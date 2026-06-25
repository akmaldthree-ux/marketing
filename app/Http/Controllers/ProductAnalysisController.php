<?php
namespace App\Http\Controllers;

use App\Models\{Store, Order, Cog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $brand = $request->get('brand', 'all');
        [$year, $mon] = explode('-', $month);
        $start = Carbon::create($year, $mon, 1)->startOfMonth();
        $end   = Carbon::create($year, $mon, 1)->endOfMonth();
        $gmvStatuses = Order::gmvStatuses();

        $storeIds = Store::where('is_active', true)
            ->when($brand !== 'all', fn($q) => $q->where('brand', $brand))
            ->pluck('id')->toArray();

        // Top SKU by GMV dengan data HPP
        $skus = DB::table('orders')
            ->leftJoin('cogs', 'orders.product_sku', '=', 'cogs.product_sku')
            ->when(!empty($storeIds), fn($q) => $q->whereIn('orders.store_id', $storeIds))
            ->whereIn('orders.status', $gmvStatuses)
            ->whereBetween('orders.order_date', [$start, $end])
            ->whereNotNull('orders.product_sku')
            ->where('orders.product_sku', '!=', '-')
            ->selectRaw("
                orders.product_sku,
                orders.product_name,
                SUM(orders.qty) as total_qty,
                SUM(orders.gmv) as total_gmv,
                COUNT(*) as total_orders,
                AVG(orders.gmv / NULLIF(orders.qty,0)) as avg_price,
                SUM(orders.qty * COALESCE(cogs.hpp_per_unit,0)) as total_hpp,
                SUM(orders.gmv - orders.qty * COALESCE(cogs.hpp_per_unit,0)) as total_profit
            ")
            ->groupBy('orders.product_sku', 'orders.product_name')
            ->orderByDesc('total_gmv')
            ->limit(20)
            ->get()
            ->map(function ($row) {
                $margin = $row->total_gmv > 0 ? round($row->total_profit / $row->total_gmv * 100, 1) : 0;
                return (object) array_merge((array)$row, [
                    'margin_pct'  => $margin,
                    'has_hpp'     => $row->total_hpp > 0,
                ]);
            });

        // Cancel rate per SKU
        $cancelRates = DB::table('orders')
            ->when(!empty($storeIds), fn($q) => $q->whereIn('store_id', $storeIds))
            ->whereBetween('order_date', [$start, $end])
            ->whereNotNull('product_sku')
            ->selectRaw("product_sku,
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('cancelled','returned','refunded') THEN 1 ELSE 0 END) as cancelled")
            ->groupBy('product_sku')
            ->pluck(DB::raw('ROUND(cancelled/total*100,1)'), 'product_sku');

        // Trend 3 bulan terakhir per SKU top 5
        $top5 = $skus->take(5)->pluck('product_sku');
        $trend = DB::table('orders')
            ->when(!empty($storeIds), fn($q) => $q->whereIn('store_id', $storeIds))
            ->whereIn('status', $gmvStatuses)
            ->whereIn('product_sku', $top5)
            ->where('order_date', '>=', Carbon::create($year, $mon, 1)->subMonths(2)->startOfMonth())
            ->where('order_date', '<=', $end)
            ->selectRaw("product_sku, DATE_FORMAT(order_date,'%Y-%m') as period, SUM(gmv) as gmv")
            ->groupBy('product_sku', 'period')
            ->orderBy('period')
            ->get()
            ->groupBy('product_sku');

        $trendLabels = collect();
        for ($i = 2; $i >= 0; $i--) {
            $trendLabels->push(Carbon::create($year, $mon, 1)->subMonths($i)->format('Y-m'));
        }

        $allStores = Store::where('is_active', true)->get();

        return view('product-analysis.index', compact('skus', 'cancelRates', 'trend', 'trendLabels', 'allStores', 'month', 'brand'));
    }
}
