<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CognitiveSkillScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cognitive_session_id',
        'name',
        'score',
        'trend',
        'measured_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'measured_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function session()
    {
        return $this->belongsTo(CognitiveSession::class, 'cognitive_session_id');
    }
}
