<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the reports associated with the category.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
