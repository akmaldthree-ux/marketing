<?php
namespace App\Http\Controllers;

use App\Models\{Store, AdsPerformance, Target};
use Illuminate\Http\Request;
use Carbon\Carbon;

class RoasController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $brand = $request->get('brand', 'all');
        [$year, $mon] = explode('-', $month);
        $startDate = Carbon::create($year, $mon, 1)->startOfMonth();
        $endDate = Carbon::create($year, $mon, 1)->endOfMonth();

        $storeQuery = Store::query()->where('is_active', true);
        if ($brand !== 'all') $storeQuery->where('brand', $brand);
        $stores = $storeQuery->get();

        $roasData = $stores->map(function ($store) use ($startDate, $endDate) {
            $ads = AdsPerformance::where('store_id', $store->id)->whereBetween('date', [$startDate, $endDate])->get();
            $spend = $ads->sum('spend');
            $gmvFromAds = $ads->sum('gmv_from_ads');
            $roas = $spend > 0 ? round($gmvFromAds / $spend, 2) : 0;
            $daily = $ads->groupBy(fn($a) => $a->date->format('Y-m-d'))->map(function ($group) {
                $s = $group->sum('spend');
                $g = $group->sum('gmv_from_ads');
                return ['spend' => $s, 'gmv' => $g, 'roas' => $s > 0 ? round($g / $s, 2) : 0];
            });
            return compact('store', 'spend', 'gmvFromAds', 'roas', 'daily');
        });

        $platformBreakdown = [];
        foreach (['Shopee', 'TikTok Shop', 'Meta Ads'] as $plat) {
            $platStoreIds = Store::where('platform', $plat)->pluck('id');
            $spend = AdsPerformance::whereIn('store_id', $platStoreIds)->whereBetween('date', [$startDate, $endDate])->sum('spend');
            $gmv = AdsPerformance::whereIn('store_id', $platStoreIds)->whereBetween('date', [$startDate, $endDate])->sum('gmv_from_ads');
            $platformBreakdown[$plat] = ['spend' => $spend, 'gmv' => $gmv, 'roas' => $spend > 0 ? round($gmv / $spend, 2) : 0];
        }

        return view('roas.index', compact('roasData', 'platformBreakdown', 'month', 'brand'));
    }
}
