<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run()
    {
        Invoice::truncate();
        InvoiceDetail::truncate();

        $products = Product::all();

        $invoices = [
            [
                'user_id' => 1,
                'customer_id' => 1,
                'table_number' => 2,
                'voucher_code' => null,
                'note' => 'Cho nhiều nước',
                'total_price' => 151000,
                'discount_price' => 0,
                'final_price' => 151000,
            ],
        ];

        $invoiceDetails = [
            [
                'invoice_id' => 1,
                'product_id' => 7,
                'quantity' => 2,
                'unit_price' => 23000,
                'product_name' => 'Trà ô long',
            ],
            [
                'invoice_id' => 1,
                'product_id' => 8,
                'quantity' => 7,
                'unit_price' => 15000,
                'product_name' => 'Trà lipton',
            ],
        ];

        for ($i = 0; $i < 200; $i++) {
            // Generate random date within the past 2 weeks
            $totalPrice = rand(100000, 500000); // Adjust total price range
            $randomDay = Carbon::now()->subDays(rand(1, 28));

            $invoiceAdd = [
                'user_id' => rand(1, 10), // Adjust user ID range
                'table_number' => rand(1, 10), // Adjust table number range (optional)
                'total_price' => $totalPrice, // Adjust total price range
                'final_price' => $totalPrice,
                'status' => 'finish', // Adjust status options (e.g., 'pending', 'cancelled')
                'created_at' => $randomDay,
            ];

            // Generate random number of invoice details between 1 and 5 (adjust as needed)
            $totalPrice = 0;
            $numberOfDetails = rand(1, 5);
            for ($j = 0; $j < $numberOfDetails; $j++) {
                $randomProduct = $products->random();

                $invoiceDetails[] = [
                    'invoice_id' => $i + 1, // + 1 to account for zero-based indexing
                    'product_id' => $randomProduct->id, // Adjust product ID range
                    'quantity' => rand(1, 10), // Adjust quantity range
                    'unit_price' => $randomProduct->unit_price, // Adjust unit price range
                    'product_name' => $randomProduct->name,
                ];

                $totalPrice += $randomProduct->unit_price * $invoiceDetails[$j]['quantity'];
            }

            $invoiceAdd['total_price'] = $totalPrice;

            $invoices[] = $invoiceAdd;
        }

        foreach ($invoices as $invoice) {
            Invoice::query()->create($invoice);
        }

        foreach ($invoiceDetails as $invoiceDetail) {
            InvoiceDetail::query()->create($invoiceDetail);
        }
    }
}
