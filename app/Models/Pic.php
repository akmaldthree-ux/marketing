<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Pic extends Model {
    protected $fillable = ['name','email','is_active'];
    public function stores() { return $this->hasMany(Store::class); }
    public function user() { return $this->hasOne(User::class); }
}
