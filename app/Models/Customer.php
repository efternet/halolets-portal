<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Customer extends Model
{
    protected $fillable = [
        'first_name',
        'surname',
        'email',
        'country',
        'city',
        'terms_accepted',
        'contact_accepted',
        'last_contacted',
    ];

    protected function casts(): array
    {
        return [
            'terms_accepted' => 'boolean',
            'contact_accepted' => 'boolean',
            'last_contacted' => 'datetime',
        ];
    }

    /**
     * @return BelongsToMany<Business>
     */
    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_customer');
    }
}
