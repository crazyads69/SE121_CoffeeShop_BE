<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateVoucherRequest extends ApiFormRequest
{
    public function rules()
    {
        $voucherId = $this->voucher;
        return [
            'voucher_code' => [
                'required',
                'string',
                Rule::unique('voucher', 'voucher_code')->ignore($voucherId),
            ],
            'type' => ['required', 'string', Rule::in(['direct', 'percent'])],
            'amount' => 'required|integer',
            'quantity' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ];
    }
}