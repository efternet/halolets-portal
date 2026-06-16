<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductList extends Model
{
    public $incrementing = false;

    protected $table = 'product_list';

    protected $fillable = [
        'id',
        'category_id',
        'product_name',
        'brand',
        'model',
        'serial_number',
        'asset_tag',
        'batch_no',
        'purchase_order',
        'rental_sku',
        'supplier',
        'condition_grade',
        'acquisition_date',
        'warranty_expiry',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'warranty_expiry' => 'date',
        ];
    }

    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
}
