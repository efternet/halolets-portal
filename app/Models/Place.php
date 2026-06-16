<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Place extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'city',
        'country',
        'currency',
        'country_iso2',
    ];
}
