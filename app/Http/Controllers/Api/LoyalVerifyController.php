<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyLoyalRequest;
use App\Services\CustomerService;

class LoyalVerifyController extends Controller
{
    public function __invoke(VerifyLoyalRequest $request)
    {
        $customerPhoneNumber = $request->get('customer_phone_number');

        $loyal = CustomerService::getCurrentLoyal($customerPhoneNumber);

        $data = [
            'loyal_name' => $loyal->name,
            'loyal_type' => $loyal->type,
            'loyal_amount' => $loyal->amount,
        ];
        return response()->json($data);
    }
}
