<?php

namespace App\Http\Requests;

class DashboardRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'start_date' => 'numeric|required',
            'end_date' => 'numeric|required',
        ];
    }
}
