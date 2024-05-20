<?php

namespace App\Services;

use App\Models\Loyal;

class LoyalService
{
    public static function applyLoyal(float $totalPrice, Loyal $loyal)
    {
        if ($loyal->type == 'direct') {
            $discountPrice = $loyal->amount;

            $finalPrice = $totalPrice - $discountPrice;

            return [$discountPrice, $finalPrice];
        }

        $discountPrice = $totalPrice * $loyal->amount / 100;

        $finalPrice = $totalPrice - $discountPrice;

        return [$discountPrice, $finalPrice];
    }

    public static function getCurrentLoyal(float $totalSpent) {
        $loyals = Loyal::all();
        $currentLoyal = new Loyal();

        foreach ($loyals as $loyal) {
            if ($totalSpent >= $loyal->spending_min && $totalSpent <= $loyal->spending_max) {
                $currentLoyal = $loyal;
            }
        }

        return $currentLoyal;
    }
}
