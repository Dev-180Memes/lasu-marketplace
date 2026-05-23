<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Academic Materials',   'icon' => 'bi-book',           'sort_order' => 1],
            ['name' => 'Electronics & Gadgets','icon' => 'bi-laptop',         'sort_order' => 2],
            ['name' => 'Fashion & Clothing',   'icon' => 'bi-bag',            'sort_order' => 3],
            ['name' => 'Food & Drinks',        'icon' => 'bi-cup-straw',      'sort_order' => 4],
            ['name' => 'Accommodation',        'icon' => 'bi-house',          'sort_order' => 5],
            ['name' => 'Services',             'icon' => 'bi-tools',          'sort_order' => 6],
            ['name' => 'Sports & Fitness',     'icon' => 'bi-bicycle',        'sort_order' => 7],
            ['name' => 'Health & Beauty',      'icon' => 'bi-heart-pulse',    'sort_order' => 8],
            ['name' => 'Art & Crafts',         'icon' => 'bi-palette',        'sort_order' => 9],
            ['name' => 'Other',                'icon' => 'bi-grid',           'sort_order' => 10],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insertOrIgnore(array_merge($cat, [
                'slug'       => Str::slug($cat['name']),
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
