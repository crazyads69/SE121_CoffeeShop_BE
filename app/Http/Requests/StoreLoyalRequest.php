<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreLoyalRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'spending_min' => 'required|numeric',
            'type' => ['required', 'string', Rule::in(['direct', 'percent'])],
            'amount' => 'required|integer',
        ];
    }
}
