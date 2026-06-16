<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VendorPayment extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'sales_id',
        'vendor_id',
        'amount',
        'currency',
        'payment_due',
        'paid_date',
        'vendor_report_id',
        'deficit_id',
        'status',
        'recorded_on',
        'last_processed',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_due' => 'date',
            'paid_date' => 'date',
            'recorded_on' => 'date',
            'last_processed' => 'date',
        ];
    }

    /**
     * @return HasOne<FranchisePayment>
     */
    public function franchisePayment(): HasOne
    {
        return $this->hasOne(FranchisePayment::class, 'sales_id', 'sales_id');
    }
}
