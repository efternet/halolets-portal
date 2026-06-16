<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorDeficit extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'sales_id',
        'amount',
        'date',
        'payment_due_by',
        'status',
        'recorded_on',
        'last_status_update',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
            'payment_due_by' => 'date',
            'recorded_on' => 'date',
            'last_status_update' => 'date',
        ];
    }
}
