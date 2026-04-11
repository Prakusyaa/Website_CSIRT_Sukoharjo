<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subject',
        'description',
        'category_id',
        'severity_id',
        'reporter_type',
        'reporter_id',
        'reporter_email',
        'assigned_to',
        'status',
        'created_by',
    ];

    protected $casts = [
        'reporter_type' => 'string',
    ];

    /**
     * Get the category associated with the report.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the severity level associated with the report.
     */
    public function severity(): BelongsTo
    {
        return $this->belongsTo(Severity::class);
    }

    /**
     * Get the user who reported the incident.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the user assigned to this report.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Alias for {@see assignee()} — used by API/resources that expect this name.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->assignee();
    }

    /**
     * Get the user who created this record in the system.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the attachments belonging to the report.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
