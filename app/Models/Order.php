<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['order_id', 'store_id', 'date', 'gmv', 'status', 'product_sku', 'product_name', 'qty', 'buyer_identifier', 'is_new_customer', 'customer_id', 'channel_type', 'source_platform'];
    protected $casts = ['is_new_customer' => 'boolean', 'date' => 'date'];

    public function store() { return $this->belongsTo(Store::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
}
