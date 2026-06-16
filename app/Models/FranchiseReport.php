<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FranchiseReport extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'filename',
    ];
}
