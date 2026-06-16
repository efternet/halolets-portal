<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'           => ['required', 'integer'],
            'paid_date'    => ['nullable', 'date', 'date_format:Y-m-d'],
            'payment_date' => ['nullable', 'date', 'date_format:Y-m-d'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required'              => 'A payment ID is required.',
            'id.integer'               => 'The payment ID must be an integer.',
            'paid_date.date'           => 'Vendor paid date must be a valid date.',
            'paid_date.date_format'    => 'Vendor paid date must be in YYYY-MM-DD format.',
            'payment_date.date'        => 'Location payment date must be a valid date.',
            'payment_date.date_format' => 'Location payment date must be in YYYY-MM-DD format.',
        ];
    }
}
