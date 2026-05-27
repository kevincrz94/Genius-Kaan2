<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'security_unit_id',
        'operational_group_id',
        'category',
        'severity',
        'title',
        'description',
        'detected_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unit()
    {
        return $this->belongsTo(SecurityUnit::class, 'security_unit_id');
    }

    public function group()
    {
        return $this->belongsTo(OperationalGroup::class, 'operational_group_id');
    }
}
