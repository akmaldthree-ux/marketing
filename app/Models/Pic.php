<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pic extends Model
{
    protected $fillable = ['name', 'email', 'role', 'assigned_stores', 'is_active'];
    protected $casts = ['assigned_stores' => 'array', 'is_active' => 'boolean'];

    public function stores() { return $this->hasMany(Store::class); }
    public function user() { return $this->hasOne(User::class); }
}
