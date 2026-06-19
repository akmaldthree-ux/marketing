<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AdsPerformance extends Model
{
    protected $table = 'ads_performance';
    protected $fillable = ['store_id', 'date', 'platform', 'spend', 'impressi', 'klik', 'gmv_from_ads', 'roas', 'reach', 'konversi', 'campaign_name'];
    protected $casts = ['date' => 'date'];

    public function store() { return $this->belongsTo(Store::class); }
}
