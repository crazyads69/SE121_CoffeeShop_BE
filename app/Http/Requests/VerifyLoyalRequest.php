<?php

namespace App\Http\Requests;

class VerifyLoyalRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'customer_phone_number' => 'required|string',
        ];
    }
}
