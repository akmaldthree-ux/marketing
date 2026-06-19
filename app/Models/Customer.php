<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['buyer_identifier', 'platform', 'first_order_date', 'first_store_id', 'total_orders', 'last_order_date'];
    protected $casts = ['first_order_date' => 'date', 'last_order_date' => 'date'];

    public function orders() { return $this->hasMany(Order::class); }
}
