<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'initial_date',
        'final_date',
        'description',
        'risk_situation_id',
        'informer_id'
    ];

    /**
     * The risk situation that belong to the incident.
     */
    public function riskSituation(): BelongsTo
    {
        return $this->belongsTo(RiskSituation::class);
    }

    /**
     * The informer that belong to the incident.
     */
    public function informer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'informer_id');
    }

    /**
     * Get the reports for the incident.
     */
    public function userReports(): HasMany
    {
        return $this->hasMany(UserReport::class);
    }

    /**
     * Get meet point brigadiers for the incident.
     */
    public function brigadiers(): HasMany
    {
        return $this->hasMany(MeetPointBrigadier::class);
    }
}
