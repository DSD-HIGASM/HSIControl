<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PreRegistration extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'data', 'user_id', 'is_global', 'processed_at'];
    protected $casts = ['data' => 'array', 'id' => 'string'];

    protected static function boot() {
        parent::boot();
        static::creating(fn($model) => $model->id = (string) Str::uuid());
    }
}