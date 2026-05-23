<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CampusZoneSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            StoreSeeder::class,
            ListingSeeder::class,
        ]);
    }
}
