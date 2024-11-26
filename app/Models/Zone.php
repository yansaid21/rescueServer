<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
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
        'institution_id',
    ];

    /**
     * Get the institution that owns the zone.
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Get the rooms for the zone.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get the meet points for the zone.
     */
    public function meetPoints(): BelongsToMany
    {
        return $this->belongsToMany(MeetPoint::class, 'meet_point_zones')->withTimestamps();
    }

    /**
     * Get the reports for the zone.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(UserReport::class);
    }
}
