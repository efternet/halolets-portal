<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'product_name'         => ['required', 'string', 'max:255'],
            'category_id'          => ['nullable', 'integer', 'exists:product_categories,id'],
            'brand'                => ['nullable', 'string', 'max:255'],
            'model'                => ['nullable', 'string', 'max:255'],
            'serial_number'        => ['nullable', 'string', 'max:255'],
            'asset_tag'            => ['nullable', 'string', 'max:255'],
            'batch_no'             => ['nullable', 'string', 'max:255'],
            'purchase_order'        => ['nullable', 'string', 'max:255'],
            'rental_sku'            => ['nullable', 'string', 'max:255'],
            'supplier'             => ['nullable', 'string', 'max:255'],
            'condition_grade'      => ['nullable', 'string', 'max:50'],
            'acquisition_date'     => ['nullable', 'date'],
            'warranty_expiry'      => ['nullable', 'date'],
            'notes'                => ['nullable', 'string'],
        ];
    }
}
