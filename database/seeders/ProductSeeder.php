<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        Product::truncate();

        $products = [
            [
                'name' => 'Trà đá',
                'image' => 'images/products/tra_da.jpg',
                'unit_price' => 2000,
                'type' => 'Đồ uống',
            ],
            [
                'name' => 'Trà sữa',
                'image' => 'images/products/tra_sua.jpg',
                'unit_price' => 20000,
                'type' => 'Đồ uống',
            ],
            [
                'name' => 'Cafe đen',
                'image' => 'images/products/cf_den.jpg',
                'unit_price' => 15000,
                'type' => 'Đồ uống',
            ],
            [
                'name' => 'Bạc sỉu',
                'image' => 'images/products/bac_siu.jpg',
                'unit_price' => 20000,
                'type' => 'Đồ uống',
            ],
            [
                'name' => 'Trà vải',
                'image' => 'images/products/tra_vai.jpg',
                'unit_price' => 25000,
                'type' => 'Đồ uống',
            ],
            [
                'name' => 'Sữa chua',
                'image' => 'images/products/sua_chua.jpg',
                'unit_price' => 10000,
                'type' => 'Đồ ăn',
            ],
            [
                'name' => 'Trà ô long',
                'image' => 'images/products/tra_o_long.jpg',
                'unit_price' => 23000,
                'type' => 'Đồ uống',
            ],
            [
                'name' => 'Trà lipton',
                'image' => 'images/products/tra_lipton.jpg',
                'unit_price' => 15000,
                'type' => 'Đồ uống',
            ],
            [
                'name' => 'Trà đào',
                'image' => 'images/products/tra_dao.jpg',
                'unit_price' => 25000,
                'type' => 'Đồ uống',
            ],
            [
                'name' => 'Nước khoáng',
                'image' => 'images/products/nuoc_khoang.jpg',
                'unit_price' => 10000,
                'type' => 'Đồ uống',
            ],
            [
                'name' => 'Coca',
                'image' => 'images/products/coca.jpg',
                'unit_price' => 15000,
                'type' => 'Đồ uống',
            ],
        ];

        foreach ($products as $product) {
            Product::query()->create($product);
        }
    }
}
