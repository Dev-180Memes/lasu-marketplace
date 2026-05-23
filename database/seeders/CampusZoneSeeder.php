<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampusZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            ['name' => 'Main Gate Area', 'description' => 'Near the main entrance of LASU', 'latitude' => 6.5773, 'longitude' => 3.2622, 'is_active' => true],
            ['name' => 'Faculty of Science Building', 'description' => 'Front of the Science faculty building', 'latitude' => 6.5780, 'longitude' => 3.2630, 'is_active' => true],
            ['name' => 'Faculty of Law', 'description' => 'Law faculty complex', 'latitude' => 6.5768, 'longitude' => 3.2610, 'is_active' => true],
            ['name' => 'Student Union Building (SUB)', 'description' => 'Student union building area', 'latitude' => 6.5760, 'longitude' => 3.2640, 'is_active' => true],
            ['name' => 'Library Complex', 'description' => 'Main university library', 'latitude' => 6.5775, 'longitude' => 3.2618, 'is_active' => true],
            ['name' => 'Faculty of Management Sciences', 'description' => 'Management sciences building', 'latitude' => 6.5785, 'longitude' => 3.2625, 'is_active' => true],
            ['name' => 'Engineering Complex', 'description' => 'Faculty of Engineering area', 'latitude' => 6.5770, 'longitude' => 3.2650, 'is_active' => true],
            ['name' => 'Health Sciences', 'description' => 'Faculty of Health Sciences area', 'latitude' => 6.5755, 'longitude' => 3.2615, 'is_active' => true],
            ['name' => 'Sports Complex', 'description' => 'University sports facility', 'latitude' => 6.5790, 'longitude' => 3.2660, 'is_active' => true],
            ['name' => 'Senate Building', 'description' => 'University senate building', 'latitude' => 6.5765, 'longitude' => 3.2635, 'is_active' => true],
        ];

        foreach ($zones as $zone) {
            DB::table('campus_zones')->insertOrIgnore(array_merge($zone, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
