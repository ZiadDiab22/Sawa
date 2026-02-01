<?php

namespace App\Http\Requests\Ride;

use Illuminate\Foundation\Http\FormRequest;

class RideRequestDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'ride_request_id' => ['required', 'exists:ride_requests,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ride_request_id' => $this->route('ride_request_id'),
        ]);
    }

    public function messages(): array
    {
        return [
            'ride_request_id.exists' => 'invalid ride_request_id',
        ];
    }
}
