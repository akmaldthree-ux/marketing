<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = 'app_notifications';
    protected $fillable = ['user_id', 'title', 'message', 'type', 'link', 'is_read'];
    protected $casts = ['is_read' => 'boolean'];
}
