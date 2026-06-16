<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorSale extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'sales_date',
        'date_out',
        'date_in',
        'out_from',
        'in_from',
        'place_id',
        'customer',
        'reference',
        'payment_id',
        'regulatory_code',
        'amount',
        'vat',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'sales_date' => 'date',
            'date_out' => 'date',
            'date_in' => 'date',
            'amount' => 'decimal:2',
            'vat' => 'decimal:5',
        ];
    }

    /**
     * @return BelongsTo<Place, $this>
     */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
