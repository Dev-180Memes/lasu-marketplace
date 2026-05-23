<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::firstOrCreate(
            ['email' => 'admin@lasu.edu.ng'],
            [
                'name'              => 'LASU Admin',
                'edu_email'         => 'admin@lasu.edu.ng',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'status'            => 'active',
                'faculty'           => 'Administration',
                'department'        => 'ICT',
                'phone'             => '08000000001',
            ]
        );

        // Sample Sellers
        $sellers = [
            [
                'name'       => 'Amara Books',
                'email'      => 'amara@lasu.edu.ng',
                'edu_email'  => 'amara@lasu.edu.ng',
                'faculty'    => 'Faculty of Arts',
                'department' => 'English',
                'phone'      => '08012345001',
            ],
            [
                'name'       => 'Chidi Tech Store',
                'email'      => 'chidi@lasu.edu.ng',
                'edu_email'  => 'chidi@lasu.edu.ng',
                'faculty'    => 'Faculty of Science',
                'department' => 'Computer Science',
                'phone'      => '08012345002',
            ],
            [
                'name'       => 'Fatima Fashion Hub',
                'email'      => 'fatima@lasu.edu.ng',
                'edu_email'  => 'fatima@lasu.edu.ng',
                'faculty'    => 'Faculty of Management Sciences',
                'department' => 'Business Administration',
                'phone'      => '08012345003',
            ],
        ];

        foreach ($sellers as $s) {
            User::firstOrCreate(
                ['email' => $s['email']],
                array_merge($s, [
                    'email_verified_at' => now(),
                    'password'          => Hash::make('password'),
                    'role'              => 'seller',
                    'status'            => 'active',
                ])
            );
        }

        // Sample Buyers
        $buyers = [
            [
                'name'       => 'Emeka Obi',
                'email'      => 'emeka@lasu.edu.ng',
                'edu_email'  => 'emeka@lasu.edu.ng',
                'faculty'    => 'Faculty of Law',
                'department' => 'Law',
                'phone'      => '08012345004',
            ],
            [
                'name'       => 'Ngozi Adeyemi',
                'email'      => 'ngozi@lasu.edu.ng',
                'edu_email'  => 'ngozi@lasu.edu.ng',
                'faculty'    => 'Faculty of Education',
                'department' => 'Education',
                'phone'      => '08012345005',
            ],
        ];

        foreach ($buyers as $b) {
            User::firstOrCreate(
                ['email' => $b['email']],
                array_merge($b, [
                    'email_verified_at' => now(),
                    'password'          => Hash::make('password'),
                    'role'              => 'buyer',
                    'status'            => 'active',
                ])
            );
        }
    }
}
