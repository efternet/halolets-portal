<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCustomer extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'business_customer';

    protected $fillable = [
        'business_id',
        'customer_id',
    ];
}
