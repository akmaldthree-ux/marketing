<?php
namespace App\Http\Controllers;

use App\Models\{Store, Order};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};
use Carbon\Carbon;

class ProductAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $brand = $request->get('brand', 'all');
        [$year, $mon] = explode('-', $month);
        $start = Carbon::create((int)$year, (int)$mon, 1)->startOfMonth()->toDateString();
        $end   = Carbon::create((int)$year, (int)$mon, 1)->endOfMonth()->toDateString();

        $storeIds = Store::where('is_active', true)
            ->when($brand !== 'all', fn($q) => $q->where('brand', $brand))
            ->pluck('id')->toArray();

        $gmvStatuses = Order::gmvStatuses();

        // Top SKU by GMV — pakai canonical_name dari products master jika ada
        $hasProducts = Schema::hasTable('products');
        $skuQuery = DB::table('orders as o')
            ->leftJoin('cogs as c', 'o.product_sku', '=', 'c.product_sku');
        if ($hasProducts) $skuQuery->leftJoin('products as p', 'o.product_sku', '=', 'p.product_sku');
        $nameExpr = $hasProducts
            ? "COALESCE(MAX(p.canonical_name), MAX(o.product_name))"
            : "MAX(o.product_name)";
        $skuQuery
            ->whereIn('o.status', $gmvStatuses)
            ->whereBetween('o.order_date', [$start, $end])
            ->whereNotNull('o.product_sku')
            ->where('o.product_sku', '!=', '-')
            ->selectRaw("
                o.product_sku,
                {$nameExpr} as product_name,
                SUM(o.qty) as total_qty,
                SUM(o.gmv) as total_gmv,
                COUNT(*) as total_orders,
                AVG(o.gmv / NULLIF(o.qty, 0)) as avg_price,
                SUM(o.qty * COALESCE(c.hpp_per_unit, 0)) as total_hpp,
                SUM(o.gmv - o.qty * COALESCE(c.hpp_per_unit, 0)) as total_profit,
                MAX(CASE WHEN c.hpp_per_unit IS NOT NULL THEN 1 ELSE 0 END) as has_hpp
            ")
            ->groupByRaw('o.product_sku')
            ->orderByDesc('total_gmv')
            ->limit(20);

        if (!empty($storeIds)) {
            $skuQuery->whereIn('o.store_id', $storeIds);
        }

        $skus = $skuQuery->get()->map(function ($row) {
            $row->margin_pct = $row->total_gmv > 0
                ? round($row->total_profit / $row->total_gmv * 100, 1)
                : 0;
            $row->has_hpp = (bool)$row->has_hpp;
            return $row;
        });

        // Cancel rate per SKU
        $cancelQuery = DB::table('orders')
            ->whereBetween('order_date', [$start, $end])
            ->whereNotNull('product_sku')
            ->selectRaw("
                product_sku,
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('cancelled','returned','refunded') THEN 1 ELSE 0 END) as cancelled
            ")
            ->groupByRaw('product_sku');

        if (!empty($storeIds)) {
            $cancelQuery->whereIn('store_id', $storeIds);
        }

        $cancelRates = $cancelQuery->get()->mapWithKeys(fn($r) =>
            [$r->product_sku => $r->total > 0 ? round($r->cancelled / $r->total * 100, 1) : 0]
        );

        // Tren 3 bulan terakhir untuk top 5 SKU
        $top5    = $skus->take(5)->pluck('product_sku')->toArray();
        $trend   = collect();
        $trendLabels = collect();

        if (!empty($top5)) {
            $trendStart = Carbon::create((int)$year, (int)$mon, 1)->subMonths(2)->startOfMonth()->toDateString();

            $trendQuery = DB::table('orders')
                ->whereIn('status', $gmvStatuses)
                ->whereIn('product_sku', $top5)
                ->whereBetween('order_date', [$trendStart, $end])
                ->selectRaw("product_sku, DATE_FORMAT(order_date,'%Y-%m') as period, SUM(gmv) as gmv")
                ->groupByRaw("product_sku, DATE_FORMAT(order_date,'%Y-%m')")
                ->orderBy('period');

            if (!empty($storeIds)) {
                $trendQuery->whereIn('store_id', $storeIds);
            }

            $trend = $trendQuery->get()->groupBy('product_sku');

            for ($i = 2; $i >= 0; $i--) {
                $trendLabels->push(Carbon::create((int)$year, (int)$mon, 1)->subMonths($i)->format('Y-m'));
            }
        }

        $allStores = Store::where('is_active', true)->get();

        return view('product-analysis.index', compact('skus', 'cancelRates', 'trend', 'trendLabels', 'allStores', 'month', 'brand'));
    }
}
