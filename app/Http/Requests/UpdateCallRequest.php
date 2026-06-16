<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCallRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'id'    => ['required', 'integer'],
            'stage' => ['required', 'string', 'in:open,in-progress,complete,pending,draft,archived'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
