<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'municipality',
        'state',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function groups()
    {
        return $this->hasMany(OperationalGroup::class);
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
