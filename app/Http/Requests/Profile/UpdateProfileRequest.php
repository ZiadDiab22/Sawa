<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['sometimes', 'string', Rule::unique('users', 'phone')->ignore($userId)],
            'password' => 'sometimes|string',
            'profile_image' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            'gender' => 'sometimes|in:male,female',
        ];
    }
}
