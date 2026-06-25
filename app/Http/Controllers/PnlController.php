<?php
namespace App\Http\Controllers;

use App\Models\{Store, Order, AdsPerformance, Cog, Financial};
use Illuminate\Http\Request;

class PnlController extends Controller
{
    public function index(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $brand = $request->get('brand', 'all');

        $storeIds = Store::where('is_active', true)
            ->when($brand !== 'all', fn($q) => $q->where('brand', $brand))
            ->pluck('id');

        $months = ['01','02','03','04','05','06','07','08','09','10','11','12'];

        // Gross GMV dari orders
        $gmvRows = Order::whereIn('store_id', $storeIds)
            ->whereIn('status', Order::gmvStatuses())
            ->whereYear('order_date', $year)
            ->selectRaw("DATE_FORMAT(order_date,'%m') as mon, SUM(gmv) as total")
            ->groupBy('mon')
            ->pluck('total', 'mon');

        // HPP/COGS: qty × hpp_per_unit via join ke tabel cogs berdasarkan product_sku
        $cogsRows = Order::whereIn('store_id', $storeIds)
            ->whereIn('status', Order::gmvStatuses())
            ->whereYear('order_date', $year)
            ->whereNotNull('product_sku')
            ->join('cogs', 'orders.product_sku', '=', 'cogs.product_sku')
            ->selectRaw("DATE_FORMAT(orders.order_date,'%m') as mon, SUM(orders.qty * cogs.hpp_per_unit) as total")
            ->groupBy('mon')
            ->pluck('total', 'mon');

        // Ads Spend dari ads_performance
        $adsRows = AdsPerformance::whereIn('store_id', $storeIds)
            ->whereYear('date', $year)
            ->selectRaw("DATE_FORMAT(date,'%m') as mon, SUM(spend) as total")
            ->groupBy('mon')
            ->pluck('total', 'mon');

        // Biaya Ops dari financials (input manual per toko per bulan)
        $opsRows = Financial::whereIn('store_id', $storeIds)
            ->where('period', 'like', $year . '-%')
            ->selectRaw("SUBSTRING(period,6,2) as mon, SUM(operational_cost) as total")
            ->groupBy('mon')
            ->pluck('total', 'mon');

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

            $byMonth[$year . '-' . $m] = [
                'gross_gmv'        => $grossGmv,
                'net_gmv'          => $netGmv,
                'cogs'             => $cogs,
                'ads_spend'        => $adsSpend,
                'operational_cost' => $opsCost,
                'gross_profit'     => $grossProfit,
                'net_profit'       => $netProfit,
            ];
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
