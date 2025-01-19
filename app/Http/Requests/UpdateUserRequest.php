<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function rules()
    {
        $userId = $this->staff->id;
        return [
            'name' => 'required|string|max:55',
            'email' => 'required|email|unique:users,email,' . $userId,
            'password' => [
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->symbols()->numbers(),
            ],
        ];
    }
}
