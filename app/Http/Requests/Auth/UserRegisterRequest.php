<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|email|unique:users,email',
            'phone'    => 'required|string|unique:users,phone',
            'gender' => 'required|in:male,female',

        ];
    }
}
