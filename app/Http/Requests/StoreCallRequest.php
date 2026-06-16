<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCallRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'stage' => ['required', 'string', 'in:open,in-progress,complete,pending,draft,archived'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
