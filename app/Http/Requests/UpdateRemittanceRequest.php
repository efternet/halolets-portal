<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRemittanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'                    => ['required', 'integer'],
            'date_paid_by_customer' => ['nullable', 'date', 'date_format:Y-m-d'],
            'date_paid_to_customer' => ['nullable', 'date', 'date_format:Y-m-d'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required'                        => 'A remittance ID is required.',
            'id.integer'                         => 'The remittance ID must be an integer.',
            'date_paid_by_customer.date'         => 'Date paid by customer must be a valid date.',
            'date_paid_by_customer.date_format'  => 'Date paid by customer must be in YYYY-MM-DD format.',
            'date_paid_to_customer.date'         => 'Date paid to customer must be a valid date.',
            'date_paid_to_customer.date_format'  => 'Date paid to customer must be in YYYY-MM-DD format.',
        ];
    }
}
