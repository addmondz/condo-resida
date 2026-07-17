<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['group' => 'visitor', 'key' => 'qr_validity_days', 'value' => '1', 'type' => 'integer'],
            ['group' => 'visitor', 'key' => 'single_entry_only', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'visitor', 'key' => 'advance_booking_days', 'value' => '30', 'type' => 'integer'],
            ['group' => 'facility', 'key' => 'default_slot_duration', 'value' => '60', 'type' => 'integer'],
            ['group' => 'facility', 'key' => 'require_approval', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'system', 'key' => 'date_format', 'value' => 'd M Y', 'type' => 'string'],
            ['group' => 'system', 'key' => 'time_format', 'value' => 'h:i A', 'type' => 'string'],
            ['group' => 'system', 'key' => 'timezone', 'value' => 'Asia/Kuala_Lumpur', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']],
            );
        }
    }
}
