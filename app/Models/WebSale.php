<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebSale extends Model
{
    public $timestamps = false;

    protected $table = 'web_sales';

    protected $fillable = [
        'query_id',
        'total_cost',
        'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'total_cost' => 'decimal:2',
            'sold_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<WebSalesSearchQuery, $this>
     */
    public function searchQuery(): BelongsTo
    {
        return $this->belongsTo(WebSalesSearchQuery::class, 'query_id');
    }
}
