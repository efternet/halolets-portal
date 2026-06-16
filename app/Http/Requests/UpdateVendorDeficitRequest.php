<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorDeficitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'             => ['required', 'integer'],
            'date'           => ['nullable', 'date', 'date_format:Y-m-d'],
            'payment_due_by' => ['nullable', 'date', 'date_format:Y-m-d'],
            'status'         => ['required', 'string', 'in:PAID,PENDING,RESOLVED'],
        ];
    }
}
