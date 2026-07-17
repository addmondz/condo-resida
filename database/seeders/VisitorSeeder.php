<?php

namespace Database\Seeders;

use App\Enums\EntryType;
use App\Enums\VisitorPurpose;
use App\Enums\VisitorStatus;
use App\Models\Block;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Models\VisitorActivityLog;
use App\Models\VisitorRegistration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VisitorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resident = User::where('email', 'resident@example.com')->firstOrFail();
        $guard = User::where('email', 'guard@example.com')->firstOrFail();
        $property = Property::where('name', 'Broadleaf Residence')->firstOrFail();
        $block = Block::where('name', '12A')->where('property_id', $property->id)->firstOrFail();
        $unit = Unit::where('name', '184G')->where('block_id', $block->id)->firstOrFail();

        // 1. Active visitor - future visit date
        VisitorRegistration::create([
            'resident_id' => $resident->id,
            'property_id' => $property->id,
            'block_id' => $block->id,
            'unit_id' => $unit->id,
            'visitor_name' => 'Alice Johnson',
            'contact_number' => '+60121234567',
            'vehicle_number' => 'WKL 1234',
            'purpose' => VisitorPurpose::Visitor,
            'entry_type' => EntryType::SingleEntry,
            'visit_date' => now()->addDays(3),
            'notes' => 'Coming for dinner.',
            'status' => VisitorStatus::Active,
            'qr_token_hash' => hash('sha256', Str::random(64)),
        ]);

        // 2. Checked-in visitor - today
        $checkedInVisitor = VisitorRegistration::create([
            'resident_id' => $resident->id,
            'property_id' => $property->id,
            'block_id' => $block->id,
            'unit_id' => $unit->id,
            'visitor_name' => 'Bob Smith',
            'contact_number' => '+60129876543',
            'purpose' => VisitorPurpose::Family,
            'entry_type' => EntryType::SingleEntry,
            'visit_date' => now()->toDateString(),
            'status' => VisitorStatus::CheckedIn,
            'qr_token_hash' => hash('sha256', Str::random(64)),
            'checked_in_at' => now()->subHours(2),
            'checked_in_by' => $guard->id,
        ]);

        VisitorActivityLog::create([
            'visitor_registration_id' => $checkedInVisitor->id,
            'guard_id' => $guard->id,
            'action' => 'checked_in',
            'details' => [
                'visitor_name' => 'Bob Smith',
                'checked_in_at' => now()->subHours(2)->toIso8601String(),
            ],
        ]);

        // 3. Checked-out visitor - today
        $checkedOutVisitor = VisitorRegistration::create([
            'resident_id' => $resident->id,
            'property_id' => $property->id,
            'block_id' => $block->id,
            'unit_id' => $unit->id,
            'visitor_name' => 'Charlie Brown',
            'contact_number' => '+60125551234',
            'purpose' => VisitorPurpose::Delivery,
            'entry_type' => EntryType::SingleEntry,
            'visit_date' => now()->toDateString(),
            'status' => VisitorStatus::CheckedOut,
            'qr_token_hash' => hash('sha256', Str::random(64)),
            'checked_in_at' => now()->subHours(5),
            'checked_in_by' => $guard->id,
            'checked_out_at' => now()->subHours(3),
            'checked_out_by' => $guard->id,
        ]);

        VisitorActivityLog::create([
            'visitor_registration_id' => $checkedOutVisitor->id,
            'guard_id' => $guard->id,
            'action' => 'checked_in',
            'details' => [
                'visitor_name' => 'Charlie Brown',
                'checked_in_at' => now()->subHours(5)->toIso8601String(),
            ],
        ]);

        VisitorActivityLog::create([
            'visitor_registration_id' => $checkedOutVisitor->id,
            'guard_id' => $guard->id,
            'action' => 'checked_out',
            'details' => [
                'visitor_name' => 'Charlie Brown',
                'checked_out_at' => now()->subHours(3)->toIso8601String(),
            ],
        ]);

        // 4. Expired visitor - past date
        VisitorRegistration::create([
            'resident_id' => $resident->id,
            'property_id' => $property->id,
            'block_id' => $block->id,
            'unit_id' => $unit->id,
            'visitor_name' => 'Diana Prince',
            'contact_number' => '+60127778888',
            'purpose' => VisitorPurpose::Contractor,
            'entry_type' => EntryType::SingleEntry,
            'visit_date' => now()->subDays(7),
            'status' => VisitorStatus::Expired,
            'qr_token_hash' => hash('sha256', Str::random(64)),
            'expired_at' => now()->subDays(6),
        ]);

        // 5. Cancelled visitor
        VisitorRegistration::create([
            'resident_id' => $resident->id,
            'property_id' => $property->id,
            'block_id' => $block->id,
            'unit_id' => $unit->id,
            'visitor_name' => 'Eve Williams',
            'contact_number' => '+60123334444',
            'purpose' => VisitorPurpose::ServiceProvider,
            'entry_type' => EntryType::SingleEntry,
            'visit_date' => now()->addDays(1),
            'status' => VisitorStatus::Cancelled,
            'qr_token_hash' => hash('sha256', Str::random(64)),
            'cancelled_at' => now(),
            'cancelled_by' => $resident->id,
            'cancellation_reason' => 'Service provider rescheduled to next week.',
        ]);
    }
}
