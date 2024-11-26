<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * The users that belong to the institution.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'institution_users')->withPivot('code', 'role_id')->withTimestamps();
    }

    /**
     * The roles that belong to the institution.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'institution_users')->withPivot('code', 'role_id')->withTimestamps();
    }

    /**
     * Get the zones for the institution.
     */
    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    /**
     * Get the meet points for the institution.
     */
    public function meetPoints(): HasMany
    {
        return $this->hasMany(MeetPoint::class);
    }

    /**
     * Get the levels for the institution.
     */
    public function levels(): HasMany
    {
        return $this->hasMany(Level::class);
    }

    /**
     * Get the risk situations for the institution.
     */
    public function riskSituations(): HasMany
    {
        return $this->hasMany(RiskSituation::class);
    }

    /**
     * Get active incidents for the institution.
     */
    public function activeIncidents()
    {
        return $this->riskSituations->map(function ($riskSituation) {
            return $riskSituation->incidents->whereNull('final_date')->load(['riskSituation', 'informer']);
        })->flatten();
    }
}
