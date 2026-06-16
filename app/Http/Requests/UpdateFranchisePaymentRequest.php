<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFranchisePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'           => ['required', 'integer'],
            'requested_on' => ['nullable', 'date', 'date_format:Y-m-d'],
            'payment_date' => ['nullable', 'date', 'date_format:Y-m-d'],
        ];
    }
}
