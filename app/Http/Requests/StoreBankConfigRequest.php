<?php

namespace App\Http\Requests;

class StoreBankConfigRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'bank_id' => 'required|string',
            'bank_number' => 'required|numeric',
            'bank_account_name' => 'required|string',
            'api_key' => 'required|string',
        ];
    }
}