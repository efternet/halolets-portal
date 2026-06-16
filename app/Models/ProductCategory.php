<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<ProductList>
     */
    public function products(): HasMany
    {
        return $this->hasMany(ProductList::class, 'category_id');
    }
}
