<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalMetricSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'security_unit_id',
        'operational_group_id',
        'category',
        'metric_name',
        'score',
        'level',
        'trend',
        'source',
        'measured_at',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'measured_at' => 'datetime',
            'raw_payload' => 'array',
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
