<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Business extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * @return BelongsToMany<Customer>
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'business_customer');
    }
}
