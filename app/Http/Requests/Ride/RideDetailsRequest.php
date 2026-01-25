<?php

namespace App\Http\Requests\Ride;

use Illuminate\Foundation\Http\FormRequest;

class RideDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'ride_id' => ['required', 'exists:rides,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ride_id' => $this->route('ride_id'),
        ]);
    }

    public function messages(): array
    {
        return [
            'ride_id.exists' => 'invalid ride_id',
        ];
    }
}
