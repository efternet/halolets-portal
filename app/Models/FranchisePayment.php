<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FranchisePayment extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'sales_id',
        'amount',
        'requested_on',
        'payment_date',
        'franchise_report_id',
        'recorded_on',
        'last_processed',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'requested_on' => 'date',
            'payment_date' => 'date',
            'recorded_on' => 'date',
            'last_processed' => 'date',
        ];
    }

    /**
     * @return HasOne<VendorPayment>
     */
    public function vendorPayment(): HasOne
    {
        return $this->hasOne(VendorPayment::class, 'sales_id', 'sales_id');
    }
}
