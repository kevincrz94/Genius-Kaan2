<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'badge_number',
        'rank',
        'assignment_area',
        'security_unit_id',
        'operational_group_id',
        'image',
        'age',
        'gender',
        'password',
        'status',
        'role',
        'cognifit_user_token',
        'cognifit_locale',
        'cognifit_registered_at',
        'created_at',
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
            'age' => 'integer',
            'status' => 'integer',
            'cognifit_registered_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function cognitiveSessions()
    {
        return $this->hasMany(CognitiveSession::class);
    }

    public function cognitiveSkillScores()
    {
        return $this->hasMany(CognitiveSkillScore::class);
    }

    public function securityUnit()
    {
        return $this->belongsTo(SecurityUnit::class);
    }

    public function operationalGroup()
    {
        return $this->belongsTo(OperationalGroup::class);
    }

    public function operationalMetrics()
    {
        return $this->hasMany(OperationalMetricSnapshot::class);
    }

    public function operationalAlerts()
    {
        return $this->hasMany(OperationalAlert::class);
    }
}
