<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'level',
    ];

    /**
     * Get the users associated with the role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
