<?php
namespace App\Http\Controllers;

use App\Models\{Store, Order};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForecastController extends Controller
{
    public function index(Request $request)
    {
        $brand      = $request->get('brand', 'all');
        $period     = (int)$request->get('period', 3);       // bulan histori
        $targetGmv  = (float)$request->get('target_gmv', 0);
        $avgPrice   = (float)$request->get('avg_price', 299000);
        $targetMonth= $request->get('target_month', now()->addMonth()->format('Y-m'));

        $storeIds = Store::where('is_active', true)
            ->when($brand !== 'all', fn($q) => $q->where('brand', $brand))
            ->pluck('id')->toArray();

        // Rentang histori
        $histEnd   = now()->endOfMonth();
        $histStart = now()->subMonths($period)->startOfMonth();

        // Ambil data histori penjualan per SKU
        $histRows = collect();
        if (!empty($storeIds)) {
            $histRows = DB::table('orders')
                ->whereIn('store_id', $storeIds)
                ->whereIn('status', Order::gmvStatuses())
                ->whereBetween('order_date', [$histStart->toDateString(), $histEnd->toDateString()])
                ->whereNotNull('product_sku')
                ->where('product_sku', '!=', '-')
                ->leftJoin('products as p', 'orders.product_sku', '=', 'p.product_sku')
                ->selectRaw("
                    orders.product_sku,
                    COALESCE(MAX(p.canonical_name), MAX(orders.product_name)) as product_name,
                    SUM(orders.qty) as total_qty,
                    SUM(orders.gmv) as total_gmv,
                    AVG(orders.gmv / NULLIF(orders.qty,0)) as avg_unit_price
                ")
                ->groupByRaw('orders.product_sku')
                ->orderByDesc('total_qty')
                ->get();
        }

        $totalQtyHistori = $histRows->sum('total_qty');

        // Hitung % per produk dan forecast
        $forecast = collect();
        if ($totalQtyHistori > 0 && $targetGmv > 0 && $avgPrice > 0) {
            $totalUnitTarget = (int)ceil($targetGmv / $avgPrice);

            $forecast = $histRows->map(function ($row) use ($totalQtyHistori, $totalUnitTarget, $avgPrice) {
                $pct         = $row->total_qty / $totalQtyHistori * 100;
                $unitForecast= (int)ceil($totalUnitTarget * $pct / 100);
                $gmvForecast = $unitForecast * $avgPrice;

                return (object)[
                    'sku'           => $row->product_sku,
                    'name'          => $row->product_name,
                    'hist_qty'      => $row->total_qty,
                    'hist_gmv'      => $row->total_gmv,
                    'hist_avg_price'=> round($row->avg_unit_price),
                    'pct'           => round($pct, 2),
                    'unit_forecast' => $unitForecast,
                    'gmv_forecast'  => $gmvForecast,
                ];
            });
        }

        $totalUnitTarget = ($targetGmv > 0 && $avgPrice > 0)
            ? (int)ceil($targetGmv / $avgPrice)
            : 0;

        $stores = Store::where('is_active', true)->get();

        return view('forecast.index', compact(
            'forecast', 'histRows', 'brand', 'period', 'targetGmv',
            'avgPrice', 'targetMonth', 'totalUnitTarget', 'totalQtyHistori',
            'histStart', 'histEnd', 'stores'
        ));
    }

    public function export(Request $request)
    {
        $brand      = $request->get('brand', 'all');
        $period     = (int)$request->get('period', 3);
        $targetGmv  = (float)$request->get('target_gmv', 0);
        $avgPrice   = (float)$request->get('avg_price', 299000);
        $targetMonth= $request->get('target_month', now()->addMonth()->format('Y-m'));

        $storeIds = Store::where('is_active', true)
            ->when($brand !== 'all', fn($q) => $q->where('brand', $brand))
            ->pluck('id')->toArray();

        $histEnd   = now()->endOfMonth();
        $histStart = now()->subMonths($period)->startOfMonth();

        $histRows = collect();
        if (!empty($storeIds)) {
            $histRows = DB::table('orders')
                ->whereIn('store_id', $storeIds)
                ->whereIn('status', Order::gmvStatuses())
                ->whereBetween('order_date', [$histStart->toDateString(), $histEnd->toDateString()])
                ->whereNotNull('product_sku')
                ->where('product_sku', '!=', '-')
                ->leftJoin('products as p', 'orders.product_sku', '=', 'p.product_sku')
                ->selectRaw("orders.product_sku, COALESCE(MAX(p.canonical_name), MAX(orders.product_name)) as product_name, SUM(orders.qty) as total_qty, SUM(orders.gmv) as total_gmv")
                ->groupByRaw('orders.product_sku')
                ->orderByDesc('total_qty')
                ->get();
        }

        $totalQty        = $histRows->sum('total_qty');
        $totalUnitTarget = $avgPrice > 0 ? (int)ceil($targetGmv / $avgPrice) : 0;

        $rows = [[
            'SKU', 'Nama Produk',
            'Histori Qty (' . $period . ' bln)', 'Histori GMV',
            '% Share', 'Forecast Unit', 'Forecast GMV (Rp)',
        ]];

        foreach ($histRows as $row) {
            $pct          = $totalQty > 0 ? round($row->total_qty / $totalQty * 100, 2) : 0;
            $unitForecast = (int)ceil($totalUnitTarget * $pct / 100);
            $rows[]       = [
                $row->product_sku,
                $row->product_name,
                $row->total_qty,
                $row->total_gmv,
                $pct . '%',
                $unitForecast,
                $unitForecast * $avgPrice,
            ];
        }

        // Baris summary
        $rows[] = [];
        $rows[] = ['SUMMARY'];
        $rows[] = ['Target GMV', 'Rp ' . number_format($targetGmv, 0, ',', '.')];
        $rows[] = ['Average Price', 'Rp ' . number_format($avgPrice, 0, ',', '.')];
        $rows[] = ['Total Unit Target', $totalUnitTarget . ' unit'];
        $rows[] = ['Periode Histori', $period . ' bulan'];
        $rows[] = ['Brand Filter', $brand];
        $rows[] = ['Bulan Target', $targetMonth];

        $csv = "\xEF\xBB\xBF";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\r\n";
        }

        $filename = "forecast_{$targetMonth}_" . ($brand !== 'all' ? $brand . '_' : '') . "gmv" . (int)($targetGmv / 1000000) . "jt.csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
