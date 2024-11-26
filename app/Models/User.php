<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'id_card',
        'rhgb',
        'social_security',
        'phone_number',
        'is_active',
        'photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'password' => 'hashed',
        ];
    }

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the secondary emails for the user.
     */
    public function secondaryEmails(): HasMany
    {
        return $this->hasMany(SecondaryEmails::class);
    }

    /**
     * The institutions that belong to the user.
     */
    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class, 'institution_users')->withPivot('code', 'role_id')->withTimestamps();
    }

    /**
     * The role that belong to the user in the institution.
     */
    public function role(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'institution_users')->withPivot('code', 'institution_id')->withTimestamps();
    }

    /**
     * Get the reports that the user has made.
     */
    public function userReports(): HasMany
    {
        return $this->hasMany(UserReport::class);
    }

    /**
     * Get the reports that the user has resolved.
     */
    public function userReportResolutions(): HasMany
    {
        return $this->hasMany(UserReportResolution::class);
    }

    /**
     * Get the incidents that the user has informed.
     * This is for the admin's user.
     */
    public function informedIncidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'informer_id');
    }

    public function brigadierMeetPoints()
    {
        return $this->hasMany(MeetPointBrigadier::class, 'brigadier_id');
    }

    /**
     * Utility function that computes wheter the register has been complete or not given the institution id
     */
    public function isRegisterCompleted(int $institution_id)
    {
        return !(
            is_null($this->rhgb) || is_null($this->social_security) || is_null($this->phone_number) ||
            is_null($this->institutions()->where("id", $institution_id)->first()->pivot->code)
        );
    }
}
