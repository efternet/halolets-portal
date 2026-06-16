<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkTaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'id'                 => ['required', 'integer'],
            'call_id'            => ['required', 'integer', 'exists:calls,id'],
            'resolution_type_id' => ['nullable', 'integer', 'exists:resolution_types,id'],
            'work_started_at'    => ['nullable', 'date'],
            'work_completed_at'  => ['nullable', 'date', 'after_or_equal:work_started_at'],
        ];
    }
}
