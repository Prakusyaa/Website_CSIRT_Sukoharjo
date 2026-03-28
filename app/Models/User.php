<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\RoleLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the role associated with the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if the user meets or exceeds a defined minimum role level.
     */
    public function hasMinLevel(RoleLevel $level): bool
    {
        if (! $this->role) {
            return false;
        }

        return $this->role->level >= $level->value;
    }

    /**
     * Check if the user has full unrestricted administrative capabilities.
     */
    public function isAdmin(): bool
    {
        return $this->hasMinLevel(RoleLevel::ADMIN);
    }

    /**
     * Check if the user is acting functionally as a CSIRT member or higher.
     */
    public function isCSIRT(): bool
    {
        return $this->hasMinLevel(RoleLevel::CSIRT);
    }

    /**
     * Check if the user is standard internal staff or tracking.
     */
    public function isStaff(): bool
    {
        return $this->hasMinLevel(RoleLevel::STAFF);
    }

    /**
     * Get the reports where the user is the reporter.
     */
    public function reportedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    /**
     * Get the reports assigned to the user.
     */
    public function assignedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'assigned_to');
    }

    /**
     * Get the reports created by the user.
     */
    public function createdReports(): HasMany
    {
        return $this->hasMany(Report::class, 'created_by');
    }

    /**
     * Get the attachments created by the user.
     */
    public function createdAttachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'createdby');
    }

    /**
     * Get the audit logs for the user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
