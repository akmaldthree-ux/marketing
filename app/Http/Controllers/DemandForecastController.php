<?php
namespace App\Http\Controllers;

use App\Models\{DemandForecast, Store, Order, StockLevel};
use Illuminate\Http\Request;
use Carbon\Carbon;

class DemandForecastController extends Controller
{
    public function index(Request $request)
    {
        $brand = $request->get('brand', 'all');
        $storeId = $request->get('store_id', 'all');

        $storeQuery = Store::query()->where('is_active', true);
        if ($brand !== 'all') $storeQuery->where('brand', $brand);
        if ($storeId !== 'all') $storeQuery->where('id', $storeId);
        $storeIds = $storeQuery->pluck('id');

        $forecasts = DemandForecast::whereIn('store_id', $storeIds)->with('store')->get();
        $allStores = Store::where('is_active', true)->get();

        return view('demand-forecast.index', compact('forecasts', 'brand', 'storeId', 'allStores'));
    }

    public function generate(Request $request)
    {
        $stores = Store::where('is_active', true)->get();
        $weekStart = Carbon::now()->addWeek()->startOfWeek()->toDateString();
        $safetyBuffer = 0.2;

        foreach ($stores as $store) {
            $skus = Order::where('store_id', $store->id)->where('status', 'complete')
                ->select('product_sku', 'product_name')->distinct()->get();
            foreach ($skus as $skuRow) {
                $fourWeeksAgo = Carbon::now()->subWeeks(4)->toDateString();
                $orders = Order::where('store_id', $store->id)->where('product_sku', $skuRow->product_sku)
                    ->where('status', 'complete')->where('date', '>=', $fourWeeksAgo)->sum('qty');
                $avgDaily = round($orders / 28, 2);
                $forecastQty = (int)ceil($avgDaily * 7);
                $safetyStock = (int)ceil($forecastQty * $safetyBuffer);

                $prevFourWeeks = Order::where('store_id', $store->id)->where('product_sku', $skuRow->product_sku)
                    ->where('status', 'complete')->whereBetween('date', [Carbon::now()->subWeeks(8)->toDateString(), $fourWeeksAgo])->sum('qty');
                $prevAvgDaily = round($prevFourWeeks / 28, 2);
                $trendChange = $prevAvgDaily > 0 ? round((($avgDaily - $prevAvgDaily) / $prevAvgDaily) * 100, 1) : 0;
                $trendDir = $trendChange > 10 ? 'up' : ($trendChange < -10 ? 'down' : 'stable');

                DemandForecast::updateOrCreate(
                    ['store_id' => $store->id, 'product_sku' => $skuRow->product_sku, 'forecast_week_start' => $weekStart],
                    ['product_name' => $skuRow->product_name, 'avg_daily_sales_4w' => $avgDaily, 'forecast_qty' => $forecastQty, 'safety_stock_qty' => $safetyStock, 'recommended_order_qty' => $forecastQty + $safetyStock, 'trend_direction' => $trendDir, 'trend_change_pct' => $trendChange, 'is_limited_data' => $orders < 14, 'generated_at' => now()]
                );
            }
        }
        return back()->with('success', 'Demand forecast berhasil di-generate!');
    }
}
