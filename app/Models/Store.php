<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Store extends Model {
    protected $fillable = ['name','brand','platform','channel_type','pic_id','is_active'];
    public function pic() { return $this->belongsTo(Pic::class); }
    public function orders() { return $this->hasMany(Order::class); }
    public function metrics() { return $this->hasMany(StoreMetric::class); }
    public function adsPerformance() { return $this->hasMany(AdsPerformance::class); }
    public function financials() { return $this->hasMany(Financial::class); }
    public function targets() { return $this->hasMany(Target::class); }
    public function cogs() { return $this->hasMany(Cog::class); }
    public function funnelTargets() { return $this->hasMany(FunnelTarget::class); }
}
