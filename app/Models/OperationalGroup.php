<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'security_unit_id',
        'name',
        'code',
        'shift',
        'assignment_type',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function unit()
    {
        return $this->belongsTo(SecurityUnit::class, 'security_unit_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function metrics()
    {
        return $this->hasMany(OperationalMetricSnapshot::class);
    }
}
