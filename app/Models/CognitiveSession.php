<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CognitiveSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'area',
        'game_key',
        'duration_minutes',
        'status',
        'score',
        'scheduled_for',
        'started_at',
        'completed_at',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'score' => 'decimal:2',
            'scheduled_for' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skillScores()
    {
        return $this->hasMany(CognitiveSkillScore::class);
    }
}
