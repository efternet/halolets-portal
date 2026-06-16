<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbTest extends Model
{
    protected $fillable = [
        'name',
        'active',
        'active_at',
        'deactivated_on',
        'user_group',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'active_at' => 'datetime',
            'deactivated_on' => 'datetime',
        ];
    }

    /**
     * @return HasMany<AbVisit>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(AbVisit::class);
    }
}
