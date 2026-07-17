<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class FacilityService
{
    /**
     * Get available time slots for a facility on a given date.
     *
     * Calculates all slots based on opening/closing time and slot duration,
     * then removes booked and blocked slots.
     *
     * @return array<int, array{start_time: string, end_time: string, available: bool}>
     */
    public function getAvailableSlots(Facility $facility, string $date): array
    {
        $openingTime = Carbon::parse($facility->opening_time);
        $closingTime = Carbon::parse($facility->closing_time);
        $slotDuration = $facility->slot_duration;

        $slots = [];
        $current = $openingTime->copy();

        while ($current->copy()->addMinutes($slotDuration)->lte($closingTime)) {
            $slotStart = $current->format('H:i');
            $slotEnd = $current->copy()->addMinutes($slotDuration)->format('H:i');

            $slots[] = [
                'start_time' => $slotStart,
                'end_time' => $slotEnd,
                'available' => true,
            ];

            $current->addMinutes($slotDuration);
        }

        // Get existing bookings for the date (pending or approved)
        $bookedSlots = FacilityBooking::where('facility_id', $facility->id)
            ->whereDate('booking_date', $date)
            ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
            ->get(['start_time', 'end_time']);

        // Get blocked slots for the date
        $blockedSlots = $facility->blockedSlots()
            ->whereDate('blocked_date', $date)
            ->get(['start_time', 'end_time']);

        foreach ($slots as &$slot) {
            // Check against booked slots
            foreach ($bookedSlots as $booked) {
                $bookedStart = Carbon::parse($booked->start_time)->format('H:i');
                $bookedEnd = Carbon::parse($booked->end_time)->format('H:i');

                if ($slot['start_time'] < $bookedEnd && $slot['end_time'] > $bookedStart) {
                    $slot['available'] = false;
                    break;
                }
            }

            // Check against blocked slots
            if ($slot['available']) {
                foreach ($blockedSlots as $blocked) {
                    $blockedStart = Carbon::parse($blocked->start_time)->format('H:i');
                    $blockedEnd = Carbon::parse($blocked->end_time)->format('H:i');

                    if ($slot['start_time'] < $blockedEnd && $slot['end_time'] > $blockedStart) {
                        $slot['available'] = false;
                        break;
                    }
                }
            }
        }

        return $slots;
    }

    /**
     * Create a new facility booking.
     *
     * Checks for time conflicts and prevents double booking.
     *
     * @throws ValidationException
     */
    public function createBooking(User $resident, array $data): FacilityBooking
    {
        $facility = Facility::where('uuid', $data['facility_uuid'])->firstOrFail();

        if (! $facility->is_active || $facility->is_under_maintenance) {
            throw ValidationException::withMessages([
                'facility' => ['This facility is currently unavailable.'],
            ]);
        }

        // Check advance booking days
        $bookingDate = Carbon::parse($data['booking_date']);
        $maxDate = now()->addDays($facility->advance_booking_days);

        if ($bookingDate->gt($maxDate)) {
            throw ValidationException::withMessages([
                'booking_date' => ["Bookings can only be made up to {$facility->advance_booking_days} days in advance."],
            ]);
        }

        // Check for conflicting bookings
        $conflict = FacilityBooking::where('facility_id', $facility->id)
            ->whereDate('booking_date', $data['booking_date'])
            ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
            ->where(function ($query) use ($data) {
                $query->where('start_time', '<', $data['end_time'])
                    ->where('end_time', '>', $data['start_time']);
            })
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'time' => ['This time slot is already booked.'],
            ]);
        }

        // Check max bookings per resident
        $activeBookings = FacilityBooking::where('facility_id', $facility->id)
            ->where('resident_id', $resident->id)
            ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
            ->whereDate('booking_date', '>=', today())
            ->count();

        if ($activeBookings >= $facility->max_bookings_per_resident) {
            throw ValidationException::withMessages([
                'booking' => ["You have reached the maximum of {$facility->max_bookings_per_resident} active bookings for this facility."],
            ]);
        }

        // Check blocked slots
        $blocked = $facility->blockedSlots()
            ->whereDate('blocked_date', $data['booking_date'])
            ->where(function ($query) use ($data) {
                $query->where('start_time', '<', $data['end_time'])
                    ->where('end_time', '>', $data['start_time']);
            })
            ->exists();

        if ($blocked) {
            throw ValidationException::withMessages([
                'time' => ['This time slot is blocked and unavailable.'],
            ]);
        }

        $booking = FacilityBooking::create([
            'facility_id' => $facility->id,
            'resident_id' => $resident->id,
            'booking_date' => $data['booking_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'status' => BookingStatus::Pending,
            'notes' => $data['notes'] ?? null,
        ]);

        return $booking->load(['facility', 'resident']);
    }

    /**
     * Approve a facility booking.
     */
    public function approveBooking(FacilityBooking $booking, User $admin): FacilityBooking
    {
        $booking->update([
            'status' => BookingStatus::Approved,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        return $booking->fresh(['facility', 'resident', 'approvedBy']);
    }

    /**
     * Reject a facility booking.
     */
    public function rejectBooking(FacilityBooking $booking, User $admin, string $reason): FacilityBooking
    {
        $booking->update([
            'status' => BookingStatus::Rejected,
            'rejection_reason' => $reason,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        return $booking->fresh(['facility', 'resident']);
    }

    /**
     * Cancel a facility booking.
     */
    public function cancelBooking(FacilityBooking $booking, User $cancelledBy): FacilityBooking
    {
        $booking->update([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy->id,
        ]);

        return $booking->fresh(['facility', 'resident']);
    }
}
