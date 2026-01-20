<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerHug extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'usage_type',
        'assigned_admin_id',
        'start_date',
        'current_level',
        'main_purpose',
        'entry_trigger',
        'session_density',
        'current_phase',
        'status',
        'last_session_date',
        'next_session_date',
        'priority',
        'contract_rules',
        'ng_actions',
        'handover_memo',
        'admin_summary',
    ];

    protected $casts = [
        'start_date' => 'date',
        'last_session_date' => 'date',
        'next_session_date' => 'date',
        'ng_actions' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function levelDates(): HasMany
    {
        return $this->hasMany(CareerHugLevelDate::class);
    }

    public function contactLogs(): HasMany
    {
        return $this->hasMany(CareerHugContactLog::class)->orderBy('contact_date', 'desc');
    }

    public function levelTransitions(): HasMany
    {
        return $this->hasMany(CareerHugLevelTransition::class)->orderBy('created_at', 'desc');
    }

    public function weapons(): HasMany
    {
        return $this->hasMany(CareerHugWeapon::class);
    }
}
