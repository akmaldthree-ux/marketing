<?php
namespace App\Http\Controllers;

use App\Models\{Store, Order, AdsPerformance, Target, AppNotification, StoreMetric};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $brand = $request->get('brand', 'all');
        $platform = $request->get('platform', 'all');

        $storeQuery = Store::query()->where('is_active', true);
        if ($brand !== 'all') $storeQuery->where('brand', $brand);
        if ($platform !== 'all') $storeQuery->where('platform', $platform);
        $storeIds = $storeQuery->pluck('id');

        [$year, $mon] = explode('-', $month);
        $startDate = Carbon::create($year, $mon, 1)->startOfMonth();
        $endDate = Carbon::create($year, $mon, 1)->endOfMonth();
        $prevStart = $startDate->copy()->subMonth();
        $prevEnd = $prevStart->copy()->endOfMonth();

        // Current period
        $totalGmv = Order::whereIn('store_id', $storeIds)->where('status', 'complete')
            ->whereBetween('date', [$startDate, $endDate])->sum('gmv');
        $totalSpend = AdsPerformance::whereIn('store_id', $storeIds)
            ->whereBetween('date', [$startDate, $endDate])->sum('spend');
        $totalGmvFromAds = AdsPerformance::whereIn('store_id', $storeIds)
            ->whereBetween('date', [$startDate, $endDate])->sum('gmv_from_ads');
        $blendedRoas = $totalSpend > 0 ? round($totalGmvFromAds / $totalSpend, 2) : 0;

        $totalOrders = Order::whereIn('store_id', $storeIds)->whereBetween('date', [$startDate, $endDate])->count();
        $cancelOrders = Order::whereIn('store_id', $storeIds)->where('status', 'cancel')
            ->whereBetween('date', [$startDate, $endDate])->count();
        $cancelRate = $totalOrders > 0 ? round(($cancelOrders / $totalOrders) * 100, 2) : 0;

        $totalVisitors = StoreMetric::whereIn('store_id', $storeIds)->whereBetween('date', [$startDate, $endDate])->sum('visitors');
        $totalBuyers = StoreMetric::whereIn('store_id', $storeIds)->whereBetween('date', [$startDate, $endDate])->sum('buyers');
        $avgCvr = $totalVisitors > 0 ? round(($totalBuyers / $totalVisitors) * 100, 2) : 0;

        // Previous period
        $prevGmv = Order::whereIn('store_id', $storeIds)->where('status', 'complete')
            ->whereBetween('date', [$prevStart, $prevEnd])->sum('gmv');
        $gmvDelta = $prevGmv > 0 ? round((($totalGmv - $prevGmv) / $prevGmv) * 100, 1) : 0;

        // GMV Trend (daily)
        $gmvTrend = Order::whereIn('store_id', $storeIds)->where('status', 'complete')
            ->whereBetween('date', [$startDate, $endDate])
            ->select(DB::raw('date(date) as day'), DB::raw('sum(gmv) as total'))
            ->groupBy('day')->orderBy('day')->get();

        // Brand progress
        $brands = ['DTHREE', 'HURIM', 'ASFARA'];
        $brandProgress = [];
        foreach ($brands as $b) {
            $bStoreIds = Store::where('brand', $b)->where('is_active', true)->pluck('id');
            $actual = Order::whereIn('store_id', $bStoreIds)->where('status', 'complete')
                ->whereBetween('date', [$startDate, $endDate])->sum('gmv');
            $target = Target::whereIn('store_id', $bStoreIds)->where('month', $mon)->where('year', $year)->sum('gmv_target');
            $brandProgress[$b] = ['actual' => $actual, 'target' => $target, 'pct' => $target > 0 ? min(100, round(($actual / $target) * 100, 1)) : 0];
        }

        // Store leaderboard
        $leaderboard = Store::whereIn('id', $storeIds)->with('pic')->get()->map(function ($store) use ($startDate, $endDate) {
            $gmv = Order::where('store_id', $store->id)->where('status', 'complete')
                ->whereBetween('date', [$startDate, $endDate])->sum('gmv');
            return ['store' => $store, 'gmv' => $gmv];
        })->sortByDesc('gmv')->values();

        // MP vs Non-MP GMV (last 6 months)
        $mpVsNonMp = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $mpIds = Store::where('channel_type', 'mp')->pluck('id');
            $nonMpIds = Store::where('channel_type', 'non_mp')->pluck('id');
            $mpVsNonMp[] = [
                'month' => $d->format('M Y'),
                'mp' => Order::whereIn('store_id', $mpIds)->where('status', 'complete')
                    ->whereYear('date', $d->year)->whereMonth('date', $d->month)->sum('gmv'),
                'non_mp' => Order::whereIn('store_id', $nonMpIds)->where('status', 'complete')
                    ->whereYear('date', $d->year)->whereMonth('date', $d->month)->sum('gmv'),
            ];
        }

        $notifications = AppNotification::where('is_read', false)->orderByDesc('created_at')->limit(5)->get();

        return view('dashboard.index', compact(
            'totalGmv', 'totalSpend', 'blendedRoas', 'cancelRate', 'avgCvr', 'gmvDelta',
            'gmvTrend', 'brandProgress', 'leaderboard', 'mpVsNonMp', 'notifications',
            'month', 'brand', 'platform'
        ));
    }
}
