<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetPoint extends Model
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
        'institution_id'
    ];

    /**
     * Get the institution that owns the meet point.
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Get the zones that the meet point belongs to.
     */
    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'meet_point_zones')->withTimestamps();
    }

    /**
     * Get meet point brigadiers for the incident.
     */
    public function brigadiers(): HasMany
    {
        return $this->hasMany(MeetPointBrigadier::class);
    }
}
