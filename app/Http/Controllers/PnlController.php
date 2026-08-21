<?php
namespace App\Http\Controllers;

use App\Models\{Store, Order, AdsPerformance, Financial};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PnlController extends Controller
{
    public function index(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $brand = $request->get('brand', 'all');

        $storeIds = Store::where('is_active', true)
            ->when($brand !== 'all', fn($q) => $q->where('brand', $brand))
            ->pluck('id')
            ->toArray();

        $months = ['01','02','03','04','05','06','07','08','09','10','11','12'];

        // Gross GMV dari orders
        $gmvRows = collect();
        if (!empty($storeIds)) {
            $gmvRows = DB::table('orders')
                ->whereIn('store_id', $storeIds)
                ->whereNotIn('status', Order::excludedStatuses())
                ->whereYear('order_date', $year)
                ->selectRaw("DATE_FORMAT(order_date,'%m') as mon, SUM(gmv) as total")
                ->groupByRaw("DATE_FORMAT(order_date,'%m')")
                ->pluck('total', 'mon');
        }

        // HPP/COGS: qty × hpp_per_unit via join ke tabel cogs
        $cogsRows = collect();
        if (!empty($storeIds)) {
            $hasCogs = DB::getSchemaBuilder()->hasTable('cogs');
            if ($hasCogs) {
                $cogsRows = DB::table('orders')
                    ->join('cogs', 'orders.product_sku', '=', 'cogs.product_sku')
                    ->whereIn('orders.store_id', $storeIds)
                    ->whereIn('orders.status', Order::gmvStatuses())
                    ->whereYear('orders.order_date', $year)
                    ->whereNotNull('orders.product_sku')
                    ->selectRaw("DATE_FORMAT(orders.order_date,'%m') as mon, SUM(orders.qty * cogs.hpp_per_unit) as total")
                    ->groupByRaw("DATE_FORMAT(orders.order_date,'%m')")
                    ->pluck('total', 'mon');
            }
        }

        // Ads Spend dari ads_performance
        $adsRows = collect();
        if (!empty($storeIds)) {
            $adsRows = DB::table('ads_performance')
                ->whereIn('store_id', $storeIds)
                ->whereYear('date', $year)
                ->selectRaw("DATE_FORMAT(date,'%m') as mon, SUM(spend) as total")
                ->groupByRaw("DATE_FORMAT(date,'%m')")
                ->pluck('total', 'mon');
        }

        // Biaya Ops dari financials (input manual)
        $opsRows = collect();
        if (!empty($storeIds)) {
            $opsRows = DB::table('financials')
                ->whereIn('store_id', $storeIds)
                ->where('period', 'like', $year . '-%')
                ->selectRaw("SUBSTRING(period,6,2) as mon, SUM(operational_cost) as total")
                ->groupByRaw("SUBSTRING(period,6,2)")
                ->pluck('total', 'mon');
        }

        // Rakit per bulan
        $byMonth = [];
        foreach ($months as $m) {
            $grossGmv    = (float)($gmvRows[$m]  ?? 0);
            $cogs        = (float)($cogsRows[$m] ?? 0);
            $adsSpend    = (float)($adsRows[$m]  ?? 0);
            $opsCost     = (float)($opsRows[$m]  ?? 0);
            $netGmv      = $grossGmv;
            $grossProfit = $netGmv - $cogs - $adsSpend;
            $netProfit   = $grossProfit - $opsCost;

            $byMonth[$year . '-' . $m] = compact(
                'grossGmv', 'netGmv', 'cogs', 'adsSpend', 'opsCost', 'grossProfit', 'netProfit'
            );
        }

        $stores = Store::where('is_active', true)
            ->when($brand !== 'all', fn($q) => $q->where('brand', $brand))
            ->get();

        return view('pnl.index', compact('byMonth', 'stores', 'year', 'brand'));
    }

    public function saveOps(Request $request)
    {
        $data = $request->validate([
            'year'             => 'required|integer',
            'month'            => 'required|string|size:2',
            'store_id'         => 'required|exists:stores,id',
            'operational_cost' => 'required|numeric|min:0',
        ]);

        Financial::updateOrCreate(
            ['store_id' => $data['store_id'], 'period' => $data['year'] . '-' . $data['month']],
            ['operational_cost' => $data['operational_cost']]
        );

        return back()->with('success', 'Biaya Ops berhasil disimpan.');
    }
}
