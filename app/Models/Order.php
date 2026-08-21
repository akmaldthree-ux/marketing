<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model {
    protected $fillable = ['order_number','store_id','customer_id','order_date','gmv','status','product_sku','product_name','qty','is_new_customer','source_platform'];
    protected $casts = ['order_date'=>'date','gmv'=>'decimal:2','is_new_customer'=>'boolean'];
    public function store() { return $this->belongsTo(Store::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public static function gmvStatuses(): array { return ['complete','shipped','processing','pending']; }
    // Status yang TIDAK dihitung sebagai GMV
    public static function excludedStatuses(): array { return ['cancelled','returned','refunded']; }
}
