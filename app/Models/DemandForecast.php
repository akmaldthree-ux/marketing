<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DemandForecast extends Model
{
    protected $fillable = ['store_id', 'product_sku', 'product_name', 'forecast_week_start', 'avg_daily_sales_4w', 'forecast_qty', 'safety_stock_qty', 'recommended_order_qty', 'trend_direction', 'trend_change_pct', 'is_limited_data', 'generated_at'];
    protected $casts = ['is_limited_data' => 'boolean', 'forecast_week_start' => 'date'];

    public function store() { return $this->belongsTo(Store::class); }
}
