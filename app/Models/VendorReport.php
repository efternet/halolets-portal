<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorReport extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'filename',
        'reported_on',
        'report_last_requested_on',
        'report_first_processed',
        'report_status',
        'report_last_processed',
    ];

    protected function casts(): array
    {
        return [
            'reported_on' => 'date',
            'report_last_requested_on' => 'date',
            'report_first_processed' => 'date',
            'report_last_processed' => 'date',
        ];
    }
}
