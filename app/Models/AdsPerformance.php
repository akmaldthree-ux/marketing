<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AdsPerformance extends Model {
    protected $fillable = ['store_id','date','platform','spend','impressions','clicks','gmv_from_ads','roas','reach','conversions','campaign_name'];
    protected $casts = ['date'=>'date'];
    public function store() { return $this->belongsTo(Store::class); }
}
