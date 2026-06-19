<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'pic_id'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isPic(): bool { return $this->role === 'pic'; }

    public function pic() {
        return $this->belongsTo(Pic::class);
    }

    public function notifications() {
        return $this->hasMany(AppNotification::class);
    }
}
