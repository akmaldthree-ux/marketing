<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = ['store_name', 'brand', 'platform', 'pic_id', 'channel_type', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function pic() { return $this->belongsTo(Pic::class); }
    public function orders() { return $this->hasMany(Order::class); }
    public function metrics() { return $this->hasMany(StoreMetric::class); }
    public function adsPerformance() { return $this->hasMany(AdsPerformance::class); }
    public function financials() { return $this->hasMany(Financial::class); }
    public function targets() { return $this->hasMany(Target::class); }
}
