<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Customer extends Model {
    protected $fillable = ['username','platform','first_order_date','last_order_date','total_orders','first_store_id'];
    protected $casts = ['first_order_date'=>'date','last_order_date'=>'date'];
    public function orders() { return $this->hasMany(Order::class); }
    public function firstStore() { return $this->belongsTo(Store::class,'first_store_id'); }
}
