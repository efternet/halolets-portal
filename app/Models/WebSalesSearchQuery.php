<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebSalesSearchQuery extends Model
{
    public $timestamps = false;

    protected $table = 'web_sales_search_queries';

    protected $fillable = [
        'user_id',
        'user_ip',
        'product_id',
        'date_out',
        'date_in',
        'failed_date',
        'reason_for_failure',
        'record_created_date',
    ];

    protected function casts(): array
    {
        return [
            'date_out' => 'date',
            'date_in' => 'date',
            'failed_date' => 'datetime',
            'record_created_date' => 'datetime',
        ];
    }

    /**
     * @return HasMany<WebSale>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(WebSale::class, 'query_id');
    }
}
