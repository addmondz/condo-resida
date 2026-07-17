<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $property = Property::create([
            'name' => 'Broadleaf Residence',
            'address' => '1, Jalan Broadleaf, 50000 Kuala Lumpur',
            'contact_email' => 'management@broadleaf.com',
            'contact_phone' => '+60312345678',
            'timezone' => 'Asia/Kuala_Lumpur',
            'status' => 'active',
        ]);

        // Block 12A with unit 184G
        $block12A = Block::create([
            'property_id' => $property->id,
            'name' => '12A',
            'status' => 'active',
        ]);

        Unit::create([
            'block_id' => $block12A->id,
            'name' => '184G',
            'floor' => 18,
            'status' => 'active',
        ]);

        // Block 8B with units 101, 202, 303
        $block8B = Block::create([
            'property_id' => $property->id,
            'name' => '8B',
            'status' => 'active',
        ]);

        Unit::create([
            'block_id' => $block8B->id,
            'name' => '101',
            'floor' => 1,
            'status' => 'active',
        ]);

        Unit::create([
            'block_id' => $block8B->id,
            'name' => '202',
            'floor' => 2,
            'status' => 'active',
        ]);

        Unit::create([
            'block_id' => $block8B->id,
            'name' => '303',
            'floor' => 3,
            'status' => 'active',
        ]);
    }
}
