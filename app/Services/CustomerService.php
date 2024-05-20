<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    public static function createCustomer(string $phoneNumber) {
        $customer = new Customer();
        $customer->phone_number = $phoneNumber;
        $customer->save();

        return $customer;
    }
    public static function getCurrentLoyal(string $customerPhoneNumber) {
        $totalSpent = self::getTotalSpent($customerPhoneNumber);

        $currentLoyal = LoyalService::getCurrentLoyal($totalSpent);

        return $currentLoyal;
    }

    public static function getTotalSpent(string $customerPhoneNumber) {
        $customer = Customer::where('phone_number', $customerPhoneNumber)->first();

        if (!$customer) {
            return 0;
        }

        $invoices = $customer->invoices;
        $totalSpent = 0;

        if (empty($invoices)) {
            return 0;
        }

        foreach ($invoices as $invoice) {
            $totalSpent += $invoice->final_price;
        }

        return $totalSpent;
    }
}
