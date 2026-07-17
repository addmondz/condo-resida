<?php

namespace App\Services;

use App\Enums\EntryType;
use App\Enums\UserStatus;
use App\Enums\VisitorStatus;
use App\Models\User;
use App\Models\VisitorActivityLog;
use App\Models\VisitorRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VisitorService
{
    /**
     * Create a new visitor registration.
     *
     * Validates the resident is approved, generates a QR token,
     * hashes it, stores the hash, and returns the registration with raw token.
     *
     * @return array{visitor: VisitorRegistration, qr_token: string}
     *
     * @throws ValidationException
     */
    public function createVisitor(User $resident, array $data): array
    {
        if ($resident->status !== UserStatus::Approved) {
            throw ValidationException::withMessages([
                'resident' => ['Only approved residents can register visitors.'],
            ]);
        }

        $primaryUnit = $resident->primaryUnit;

        if (! $primaryUnit) {
            throw ValidationException::withMessages([
                'unit' => ['Resident must have a unit assignment.'],
            ]);
        }

        $rawToken = Str::random(64);
        $tokenHash = hash('sha256', $rawToken);

        $visitor = VisitorRegistration::create([
            'resident_id' => $resident->id,
            'property_id' => $primaryUnit->property_id,
            'block_id' => $primaryUnit->unit->block_id,
            'unit_id' => $primaryUnit->unit_id,
            'purpose' => $data['purpose'],
            'visitor_name' => $data['visitor_name'],
            'contact_number' => $data['contact_number'],
            'vehicle_number' => $data['vehicle_number'] ?? null,
            'visit_date' => $data['visit_date'],
            'notes' => $data['notes'] ?? null,
            'entry_type' => EntryType::SingleEntry,
            'qr_token_hash' => $tokenHash,
            'status' => VisitorStatus::Active,
        ]);

        $visitor->load(['resident', 'property', 'block', 'unit']);

        return [
            'visitor' => $visitor,
            'qr_token' => $rawToken,
        ];
    }

    /**
     * Cancel a visitor registration.
     */
    public function cancelVisitor(
        VisitorRegistration $visitor,
        User $cancelledBy,
        ?string $reason = null
    ): VisitorRegistration {
        $visitor->update([
            'status' => VisitorStatus::Cancelled,
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy->id,
            'cancellation_reason' => $reason,
        ]);

        return $visitor->fresh();
    }

    /**
     * Validate a QR token and return the matching visitor registration.
     *
     * @throws ValidationException
     */
    public function validateQrToken(string $token): VisitorRegistration
    {
        $tokenHash = hash('sha256', $token);

        $visitor = VisitorRegistration::where('qr_token_hash', $tokenHash)->first();

        if (! $visitor) {
            throw ValidationException::withMessages([
                'qr_token' => ['Invalid QR code.'],
            ]);
        }

        if ($visitor->status === VisitorStatus::Cancelled) {
            throw ValidationException::withMessages([
                'qr_token' => ['This visitor registration has been cancelled.'],
            ]);
        }

        if ($visitor->status === VisitorStatus::Expired) {
            throw ValidationException::withMessages([
                'qr_token' => ['This visitor registration has expired.'],
            ]);
        }

        if ($visitor->status === VisitorStatus::CheckedOut) {
            throw ValidationException::withMessages([
                'qr_token' => ['This visitor has already checked out.'],
            ]);
        }

        if ($visitor->visit_date->lt(today()) && $visitor->status !== VisitorStatus::CheckedIn) {
            throw ValidationException::withMessages([
                'qr_token' => ['This visitor registration is for a past date.'],
            ]);
        }

        $visitor->load(['resident', 'property', 'block', 'unit']);

        return $visitor;
    }

    /**
     * Check in a visitor.
     *
     * Uses a database transaction with pessimistic locking to prevent
     * duplicate check-ins. Creates an activity log entry.
     *
     * @throws ValidationException
     */
    public function checkIn(VisitorRegistration $visitor, User $guard, ?string $notes = null): VisitorRegistration
    {
        return DB::transaction(function () use ($visitor, $guard, $notes) {
            $visitor = VisitorRegistration::lockForUpdate()->find($visitor->id);

            if (! $visitor->canCheckIn()) {
                throw ValidationException::withMessages([
                    'visitor' => ['This visitor cannot be checked in. Current status: ' . $visitor->status->label()],
                ]);
            }

            $visitor->update([
                'status' => VisitorStatus::CheckedIn,
                'checked_in_at' => now(),
                'checked_in_by' => $guard->id,
            ]);

            VisitorActivityLog::create([
                'visitor_registration_id' => $visitor->id,
                'guard_id' => $guard->id,
                'action' => 'checked_in',
                'details' => [
                    'notes' => $notes,
                    'checked_in_at' => now()->toIso8601String(),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $visitor->fresh(['resident', 'property', 'block', 'unit', 'checkedInBy']);
        });
    }

    /**
     * Check out a visitor.
     *
     * Uses a database transaction with pessimistic locking.
     * Creates an activity log entry.
     *
     * @throws ValidationException
     */
    public function checkOut(VisitorRegistration $visitor, User $guard, ?string $notes = null): VisitorRegistration
    {
        return DB::transaction(function () use ($visitor, $guard, $notes) {
            $visitor = VisitorRegistration::lockForUpdate()->find($visitor->id);

            if (! $visitor->canCheckOut()) {
                throw ValidationException::withMessages([
                    'visitor' => ['This visitor cannot be checked out. Current status: ' . $visitor->status->label()],
                ]);
            }

            $visitor->update([
                'status' => VisitorStatus::CheckedOut,
                'checked_out_at' => now(),
                'checked_out_by' => $guard->id,
            ]);

            VisitorActivityLog::create([
                'visitor_registration_id' => $visitor->id,
                'guard_id' => $guard->id,
                'action' => 'checked_out',
                'details' => [
                    'notes' => $notes,
                    'checked_out_at' => now()->toIso8601String(),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $visitor->fresh(['resident', 'property', 'block', 'unit', 'checkedOutBy']);
        });
    }

    /**
     * Mark expired visitors based on date.
     *
     * Visitors with status 'active' whose visit_date is before today are expired.
     */
    public function expireVisitors(): void
    {
        VisitorRegistration::where('status', VisitorStatus::Active)
            ->whereDate('visit_date', '<', today())
            ->update([
                'status' => VisitorStatus::Expired,
                'expired_at' => now(),
            ]);
    }
}
