<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetPointBrigadier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'meet_point_id',
        'brigadier_id',
        'incident_id'
    ];

    /**
     * Get the meet point that the brigadier is assigned to.
     */
    public function meetPoint(): BelongsTo
    {
        return $this->belongsTo(MeetPoint::class);
    }

    /**
     * Get the brigadier that is assigned to the meet point.
     */
    public function brigadier(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the incident that the brigadier is assigned to.
     */
    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }
}
