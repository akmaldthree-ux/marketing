<?php
namespace App\Http\Controllers;

use App\Models\{Store, Order};
use Illuminate\Http\Request;
use Carbon\Carbon;

class DemandForecastController extends Controller
{
    public function index(Request $request)
    {
        $storeId = $request->get('store_id', 'all');
        $stores  = Store::where('is_active', true)->orderBy('brand')->orderBy('name')->get();

        $storeIds = $storeId === 'all'
            ? $stores->pluck('id')
            : collect([$storeId]);

        // Top SKUs by qty in last 30 days
        $since = now()->subDays(30)->toDateString();
        $skus  = Order::whereIn('store_id', $storeIds)
            ->whereIn('status', Order::gmvStatuses())
            ->where('order_date', '>=', $since)
            ->whereNotNull('product_sku')
            ->where('product_sku', '!=', '-')
            ->selectRaw('product_sku, product_name, SUM(qty) as total_qty, AVG(qty) as avg_daily')
            ->groupBy('product_sku', 'product_name')
            ->orderByDesc('total_qty')
            ->limit(30)
            ->get()
            ->map(function ($row) {
                $avgDaily = round($row->avg_daily, 1);
                return [
                    'sku'           => $row->product_sku,
                    'name'          => $row->product_name,
                    'total_qty'     => $row->total_qty,
                    'avg_daily'     => $avgDaily,
                    'forecast_7d'   => (int) ceil($avgDaily * 7),
                    'forecast_30d'  => (int) ceil($avgDaily * 30),
                    'safety_stock'  => (int) ceil($avgDaily * 7 * 1.2),
                ];
            });

        return view('demand-forecast.index', compact('skus', 'stores', 'storeId'));
    }
}
