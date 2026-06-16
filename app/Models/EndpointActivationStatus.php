<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EndpointActivationStatus extends Model
{
    protected $table = 'endpoint_activation_status';

    protected $fillable = [
        'name',
        'method',
        'path',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
