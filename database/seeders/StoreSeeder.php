<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Store;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            [
                'seller_email'  => 'amara@lasu.edu.ng',
                'name'          => 'Amara Books & Stationery',
                'description'   => 'Your go-to store for textbooks, past questions, and academic materials at affordable prices.',
                'status'        => 'verified',
                'location_label'=> 'Faculty of Arts corridor',
                'has_fixed_location' => true,
                'latitude'      => 6.5773,
                'longitude'     => 3.2622,
            ],
            [
                'seller_email'  => 'chidi@lasu.edu.ng',
                'name'          => 'Chidi Tech Hub',
                'description'   => 'Affordable laptops, phone accessories, and repair services for LASU students.',
                'status'        => 'verified',
                'location_label'=> 'Science Faculty Block B',
                'has_fixed_location' => true,
                'latitude'      => 6.5780,
                'longitude'     => 3.2630,
            ],
            [
                'seller_email'  => 'fatima@lasu.edu.ng',
                'name'          => "Fatima's Fashion",
                'description'   => 'Trendy, affordable fashion items including bags, clothing and accessories for female students.',
                'status'        => 'unverified',
                'location_label'=> 'Management Sciences ground floor',
                'has_fixed_location' => false,
                'latitude'      => null,
                'longitude'     => null,
            ],
        ];

        foreach ($stores as $s) {
            $user = User::where('email', $s['seller_email'])->first();
            if (!$user) continue;

            Store::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name'              => $s['name'],
                    'slug'              => Str::slug($s['name']) . '-' . $user->id,
                    'description'       => $s['description'],
                    'status'            => $s['status'],
                    'has_fixed_location'=> $s['has_fixed_location'],
                    'latitude'          => $s['latitude'],
                    'longitude'         => $s['longitude'],
                    'location_label'    => $s['location_label'],
                ]
            );
        }
    }
}
