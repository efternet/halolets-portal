<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'id'               => ['required', 'integer'],
            'first_name'       => ['required', 'string', 'max:255'],
            'surname'          => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255'],
            'country'          => ['nullable', 'string', 'max:255'],
            'city'             => ['nullable', 'string', 'max:255'],
            'contact_accepted' => ['nullable', 'boolean'],
            'last_contacted'   => ['nullable', 'date'],
        ];
    }
}
