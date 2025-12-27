<?php

namespace App\Http\Requests\Ride;

use Illuminate\Foundation\Http\FormRequest;

class CalculateRideRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pickup_lat' => ['required', 'numeric', 'between:-90,90'],
            'pickup_lng' => ['required', 'numeric', 'between:-180,180'],
            'drop_lat'   => ['required', 'numeric', 'between:-90,90'],
            'drop_lng'   => ['required', 'numeric', 'between:-180,180'],
            'passengers' => ['required', 'integer', 'min:1', 'max:4'],
        ];
    }
}
