<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MikyTerminal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'ip',
        'mac',
        'sector',
        'resolution',
        'custom_buttons',
        'cron',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'custom_buttons' => 'array',
            'cron' => 'array',
            'is_active' => 'boolean',
        ];
    }
}