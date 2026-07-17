<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $property = Property::where('name', 'Broadleaf Residence')->firstOrFail();
        $resident = User::where('email', 'resident@example.com')->firstOrFail();
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        // 1. Swimming Pool
        $pool = Facility::create([
            'property_id' => $property->id,
            'name' => 'Swimming Pool',
            'description' => 'Olympic-sized swimming pool with separate children\'s pool area.',
            'rules' => 'No diving. Children under 12 must be accompanied by an adult. Proper swimwear required.',
            'capacity' => 30,
            'opening_time' => '07:00',
            'closing_time' => '22:00',
            'slot_duration' => 60,
            'max_bookings_per_resident' => 2,
            'advance_booking_days' => 14,
            'cancellation_hours' => 24,
            'is_active' => true,
            'is_under_maintenance' => false,
            'status' => 'active',
        ]);

        // 2. Function Room
        $functionRoom = Facility::create([
            'property_id' => $property->id,
            'name' => 'Function Room',
            'description' => 'Multi-purpose function room suitable for events and gatherings.',
            'rules' => 'Maximum capacity must not be exceeded. Noise must be kept to acceptable levels after 10 PM. Room must be cleaned after use.',
            'capacity' => 50,
            'opening_time' => '09:00',
            'closing_time' => '21:00',
            'slot_duration' => 120,
            'max_bookings_per_resident' => 1,
            'advance_booking_days' => 30,
            'cancellation_hours' => 48,
            'is_active' => true,
            'is_under_maintenance' => false,
            'status' => 'active',
        ]);

        // Sample bookings

        // 1. Approved booking for tomorrow
        FacilityBooking::create([
            'facility_id' => $pool->id,
            'resident_id' => $resident->id,
            'booking_date' => now()->addDay(),
            'start_time' => '08:00',
            'end_time' => '09:00',
            'status' => BookingStatus::Approved,
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'notes' => 'Morning swim session.',
        ]);

        // 2. Pending booking
        FacilityBooking::create([
            'facility_id' => $functionRoom->id,
            'resident_id' => $resident->id,
            'booking_date' => now()->addDays(7),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => BookingStatus::Pending,
            'notes' => 'Birthday party for 20 guests.',
        ]);

        // 3. Completed booking (past date)
        FacilityBooking::create([
            'facility_id' => $pool->id,
            'resident_id' => $resident->id,
            'booking_date' => now()->subDays(3),
            'start_time' => '17:00',
            'end_time' => '18:00',
            'status' => BookingStatus::Completed,
            'approved_by' => $admin->id,
            'approved_at' => now()->subDays(5),
            'notes' => 'Evening swim.',
        ]);

        // 4. Rejected booking with reason
        FacilityBooking::create([
            'facility_id' => $functionRoom->id,
            'resident_id' => $resident->id,
            'booking_date' => now()->addDays(14),
            'start_time' => '14:00',
            'end_time' => '16:00',
            'status' => BookingStatus::Rejected,
            'rejection_reason' => 'Facility is reserved for scheduled maintenance on that date.',
        ]);

        // 5. Cancelled booking
        FacilityBooking::create([
            'facility_id' => $pool->id,
            'resident_id' => $resident->id,
            'booking_date' => now()->addDays(2),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'cancelled_by' => $resident->id,
        ]);
    }
}
