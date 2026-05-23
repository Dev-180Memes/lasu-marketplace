<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Store;
use App\Models\Listing;
use App\Models\Category;
use App\Models\CampusZone;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        $zone = CampusZone::first();

        $data = [
            [
                'seller_email' => 'amara@lasu.edu.ng',
                'listings' => [
                    [
                        'title'       => '300 Level Law Past Questions Bundle',
                        'description' => 'Complete set of law past questions from 2015–2023. Very useful for exam preparation.',
                        'price'       => 2500,
                        'category'    => 'Academic Materials',
                        'condition'   => 'new',
                        'stock'       => 20,
                    ],
                    [
                        'title'       => 'Engineering Mathematics Textbook (Stroud)',
                        'description' => 'Stroud Engineering Mathematics 7th Edition. Barely used, in excellent condition.',
                        'price'       => 8000,
                        'category'    => 'Academic Materials',
                        'condition'   => 'fairly_used',
                        'stock'       => 3,
                    ],
                ],
            ],
            [
                'seller_email' => 'chidi@lasu.edu.ng',
                'listings' => [
                    [
                        'title'       => 'USB-C Phone Charger (Fast Charge 65W)',
                        'description' => '65W USB-C fast charger compatible with most Android phones. Quality guaranteed.',
                        'price'       => 4500,
                        'category'    => 'Electronics & Gadgets',
                        'condition'   => 'new',
                        'stock'       => 15,
                    ],
                    [
                        'title'       => 'Laptop Screen Repair Service',
                        'description' => 'Professional laptop screen replacement. Bring your laptop, get it fixed same day.',
                        'price'       => 15000,
                        'category'    => 'Services',
                        'condition'   => 'new',
                        'stock'       => 99,
                    ],
                ],
            ],
            [
                'seller_email' => 'fatima@lasu.edu.ng',
                'listings' => [
                    [
                        'title'       => 'Ladies Ankara Handbag',
                        'description' => 'Handmade Ankara fabric handbag, large size. Multiple colour options available.',
                        'price'       => 3500,
                        'category'    => 'Fashion & Clothing',
                        'condition'   => 'new',
                        'stock'       => 10,
                    ],
                ],
            ],
        ];

        foreach ($data as $sellerData) {
            $user  = User::where('email', $sellerData['seller_email'])->first();
            $store = Store::where('user_id', $user?->id)->first();
            if (!$user || !$store) continue;

            foreach ($sellerData['listings'] as $l) {
                $category = Category::where('name', $l['category'])->first();
                if (!$category) continue;

                Listing::firstOrCreate(
                    ['title' => $l['title'], 'store_id' => $store->id],
                    [
                        'user_id'        => $user->id,
                        'store_id'       => $store->id,
                        'category_id'    => $category->id,
                        'campus_zone_id' => $zone?->id,
                        'description'    => $l['description'],
                        'price'          => $l['price'],
                        'is_negotiable'  => false,
                        'is_preorder'    => false,
                        'item_condition' => $l['condition'],
                        'stock_quantity' => $l['stock'],
                        'availability'   => 'available',
                        'published_at'   => now(),
                    ]
                );
            }
        }
    }
}
