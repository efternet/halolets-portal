<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResolutionType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * get work tasks for the resolution type
     *
     * @return HasMany<WorkTask>
     */
    public function workTasks(): HasMany
    {
        return $this->hasMany(WorkTask::class);
    }
}
