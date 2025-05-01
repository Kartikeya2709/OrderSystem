<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'name' => 'Laptop',
                'price' => 999.99,
                'description' => 'High-performance laptop',
                'stock' => 10
            ],
            [
                'name' => 'Smartphone',
                'price' => 599.99,
                'description' => 'Latest smartphone model',
                'stock' => 15
            ],
            [
                'name' => 'Headphones',
                'price' => 99.99,
                'description' => 'Wireless noise-canceling headphones',
                'stock' => 20
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
