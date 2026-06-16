<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebSearchFailure extends Model
{
    public $timestamps = false;

    protected $table = 'web_search_failures';

    protected $fillable = [
        'log_timestamp',
        'original_hash',
        'decoded_id',
        'failed_on',
        'reason',
        'form_id',
    ];

    protected function casts(): array
    {
        return [
            'log_timestamp' => 'datetime',
            'failed_on' => 'datetime',
        ];
    }
}
