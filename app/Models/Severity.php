<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Severity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'level',
    ];

    /**
     * Get the reports associated with the severity.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
