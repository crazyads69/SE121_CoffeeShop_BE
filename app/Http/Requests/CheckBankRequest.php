<?php

namespace App\Http\Requests;

class CheckBankRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'random_code' => 'required|string',
            'amount' => 'required|numeric',
        ];
    }
}
