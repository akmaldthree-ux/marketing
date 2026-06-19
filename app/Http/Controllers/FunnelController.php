<?php
namespace App\Http\Controllers;

use App\Models\{Store, StoreMetric, FunnelTarget, Order};
use Illuminate\Http\Request;
use Carbon\Carbon;

class FunnelController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $storeId = $request->get('store_id', 'all');
        $brand = $request->get('brand', 'all');
        [$year, $mon] = explode('-', $month);
        $startDate = Carbon::create($year, $mon, 1)->startOfMonth();
        $endDate = Carbon::create($year, $mon, 1)->endOfMonth();

        $storeQuery = Store::query()->where('is_active', true);
        if ($brand !== 'all') $storeQuery->where('brand', $brand);
        if ($storeId !== 'all') $storeQuery->where('id', $storeId);
        $stores = $storeQuery->get();

        $funnelData = $stores->map(function ($store) use ($startDate, $endDate, $mon, $year) {
            $metrics = StoreMetric::where('store_id', $store->id)->whereBetween('date', [$startDate, $endDate])->get();
            $views = $metrics->sum('views');
            $visitors = $metrics->sum('visitors');
            $atc = $metrics->sum('add_to_cart');
            $checkout = $metrics->sum('checkout');
            $buyers = $metrics->sum('buyers');

            $vtvRate = $views > 0 ? round(($visitors / $views) * 100, 2) : 0;
            $atcRate = $visitors > 0 ? round(($atc / $visitors) * 100, 2) : 0;
            $cvr = $visitors > 0 ? round(($buyers / $visitors) * 100, 2) : 0;
            $checkoutRate = $atc > 0 ? round(($checkout / $atc) * 100, 2) : 0;

            $targets = FunnelTarget::where('store_id', $store->id)->get()->keyBy('stage');
            $t_vtv = $targets->get('views_to_visitor') ? $targets->get('views_to_visitor')->target_pct : 10;
            $t_atc = $targets->get('atc_rate') ? $targets->get('atc_rate')->target_pct : 15;
            $t_cvr = $targets->get('cvr') ? $targets->get('cvr')->target_pct : 2;

            // AOV
            $gmv = Order::where('store_id', $store->id)->where('status', 'complete')->whereBetween('date', [$startDate, $endDate])->sum('gmv');
            $orders = Order::where('store_id', $store->id)->where('status', 'complete')->whereBetween('date', [$startDate, $endDate])->count();
            $aov = $orders > 0 ? $gmv / $orders : 0;

            // Potential Loss
            $vtvLoss = $vtvRate < $t_vtv ? (($t_vtv / 100 - $vtvRate / 100) * $views * $aov) : 0;
            $cvrLoss = $cvr < $t_cvr ? (($t_cvr / 100 - $cvr / 100) * $visitors * $aov) : 0;
            $atcLoss = $atcRate < $t_atc ? (($t_atc / 100 - $atcRate / 100) * $visitors * $aov) : 0;

            return compact('store', 'views', 'visitors', 'atc', 'checkout', 'buyers',
                'vtvRate', 'atcRate', 'cvr', 'checkoutRate',
                't_vtv', 't_atc', 't_cvr', 'aov', 'vtvLoss', 'cvrLoss', 'atcLoss');
        });

        $totalPotentialLoss = $funnelData->sum('vtvLoss') + $funnelData->sum('cvrLoss') + $funnelData->sum('atcLoss');
        $allStores = Store::where('is_active', true)->get();

        return view('funnel.index', compact('funnelData', 'totalPotentialLoss', 'month', 'storeId', 'brand', 'allStores'));
    }
}
