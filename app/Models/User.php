<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable {
    protected $fillable = ['name','email','password','role','pic_id'];
    protected $hidden = ['password','remember_token'];
    protected function casts(): array { return ['password' => 'hashed']; }
    public function pic() { return $this->belongsTo(Pic::class); }
    public function isAdmin() { return $this->role === 'admin'; }
    public function isPic() { return $this->role === 'pic'; }
    public function isViewer() { return $this->role === 'viewer'; }
}
