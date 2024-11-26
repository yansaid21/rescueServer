<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserReportResolution extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_report_id';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'user_report_id',
        'state',
        'description',
    ];

    /**
     * Get the user that owns the resolution.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the report that owns the resolution.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(UserReport::class, 'user_report_id');
    }
}
