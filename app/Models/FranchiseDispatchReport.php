<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FranchiseDispatchReport extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'franchise_id',
        'filename',
        'currency',
        'dispatch_report_date',
    ];

    protected function casts(): array
    {
        return [
            'dispatch_report_date' => 'date',
        ];
    }
}
