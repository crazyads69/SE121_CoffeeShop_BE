<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function getSummaryStatisticToday() {
        return response()->json([
            'total_income_today' => 10,
            'total_income_yesterday' => 1000000,
            'total_invoice_today' => 100,
            'total_invoice_yesterday' => 50,
            'total_income_pending' => 50,
            'total_customer_today' => 10,
            'total_customer_yesterday' => 5,
        ]);
    }

    public function getIncomeByTime() {
        return response()->json([
            'income_by_time' => [
                ['time' => '00:00 - 01:00', 'income' => 100000],
                ['time' => '01:00 - 02:00', 'income' => 200000],
                ['time' => '02:00 - 03:00', 'income' => 300000],
                ['time' => '03:00 - 04:00', 'income' => 400000],
                ['time' => '04:00 - 05:00', 'income' => 500000],
                ['time' => '05:00 - 06:00', 'income' => 600000],
                ['time' => '06:00 - 07:00', 'income' => 700000],
                ['time' => '07:00 - 08:00', 'income' => 800000],
                ['time' => '08:00 - 09:00', 'income' => 900000],
                ['time' => '09:00 - 10:00', 'income' => 1000000],
                ['time' => '10:00 - 11:00', 'income' => 1100000],
                ['time' => '11:00 - 12:00', 'income' => 1200000],
                ['time' => '12:00 - 13:00', 'income' => 1300000],
                ['time' => '13:00 - 14:00', 'income' => 1400000],
                ['time' => '14:00 - 15:00', 'income' => 1500000],
                ['time' => '15:00 - 16:00', 'income' => 1600000],
                ['time' => '16:00 - 17:00', 'income' => 1700000],
                ['time' => '17:00 - 18:00', 'income' => 1800000],
                ['time' => '18:00 - 19:00', 'income' => 1900000],
                ['time' => '19:00 - 20:00', 'income' => 2000000],
            ]]);
    }

    public function getTopProductByTime() {
        return response()->json([
            'top_product_by_time' => [
                ['time' => '00:00 - 01:00', 'product' => 'Coca Cola', 'quantity' => 10],
                ['time' => '01:00 - 02:00', 'product' => 'Pepsi', 'quantity' => 20],
                ['time' => '02:00 - 03:00', 'product' => 'Fanta', 'quantity' => 30],
                ['time' => '03:00 - 04:00', 'product' => 'Sprite', 'quantity' => 40],
                ['time' => '04:00 - 05:00', 'product' => '7 Up', 'quantity' => 50],
                ['time' => '05:00 - 06:00', 'product' => 'Mirinda', 'quantity' => 60],
                ['time' => '06:00 - 07:00', 'product' => 'Red Bull', 'quantity' => 70],
                ['time' => '07:00 - 08:00', 'product' => 'Monster', 'quantity' => 80],
                ['time' => '08:00 - 09:00', 'product' => 'Tiger', 'quantity' => 90],
                ['time' => '09:00 - 10:00', 'product' => 'Heineken', 'quantity' => 100],
                ['time' => '10:00 - 11:00', 'product' => 'Tiger', 'quantity' => 110],
                ['time' => '11:00 - 12:00', 'product' => 'Heineken', 'quantity' => 120],
                ['time' => '12:00 - 13:00', 'product' => 'Tiger', 'quantity' => 130],
                ['time' => '13:00 - 14:00', 'product' => 'Heineken', 'quantity' => 140],
                ['time' => '14:00 - 15:00', 'product' => 'Tiger', 'quantity' => 150],
                ['time' => '15:00 - 16:00', 'product' => 'Heineken', 'quantity' => 160]]]);
    }

    public function getTotalCustomerByTime() {
        return response()->json([
            'total_customer_by_time' => [
                ['time' => '00:00 - 01:00', 'total_customer' => 10],
                ['time' => '01:00 - 02:00', 'total_customer' => 20],
                ['time' => '02:00 - 03:00', 'total_customer' => 30],
                ['time' => '03:00 - 04:00', 'total_customer' => 40],
                ['time' => '04:00 - 05:00', 'total_customer' => 50],
                ['time' => '05:00 - 06:00', 'total_customer' => 60],
                ['time' => '06:00 - 07:00', 'total_customer' => 70],
                ['time' => '07:00 - 08:00', 'total_customer' => 80],
                ['time' => '08:00 - 09:00', 'total_customer' => 90],
                ['time' => '09:00 - 10:00', 'total_customer' => 100],
                ['time' => '10:00 - 11:00', 'total_customer' => 110],
                ['time' => '11:00 - 12:00', 'total_customer' => 120],
                ['time' => '12:00 - 13:00', 'total_customer' => 130],
                ['time' => '13:00 - 14:00', 'total_customer' => 140],
                ['time' => '14:00 - 15:00', 'total_customer' => 150],
                ['time' => '15:00 - 16:00', 'total_customer' => 160],
                ['time' => '16:00 - 17:00', 'total_customer' => 170],
                ['time' => '17:00 - 18:00', 'total_customer' => 180],
                ['time' => '18:00 - 19:00', 'total_customer' => 190],
                ['time' => '19:00 - 20:00', 'total_customer' => 200],
            ]]);
    }

}
