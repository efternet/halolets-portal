<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbStage extends Model
{
    protected $fillable = [
        'name',
        'business_type',
    ];

    /**
     * @return HasMany<AbVisit>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(AbVisit::class, 'stage');
    }
}
