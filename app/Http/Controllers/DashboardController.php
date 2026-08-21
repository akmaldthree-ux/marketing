<?php
namespace App\Http\Controllers;
use App\Models\{Store, Order, AdsPerformance, Target, StoreMetric};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Carbon\Carbon;
class DashboardController extends Controller {
    public function index(Request $request) {
        [$start, $end, $dateFrom, $dateTo] = $this->dateRange($request);
        $brand = $request->get('brand', 'all');

        // "previous period" same duration length
        $days      = $start->diffInDays($end) + 1;
        $prevEnd   = $start->copy()->subDay();
        $prevStart = $prevEnd->copy()->subDays($days - 1);

        $storeIds    = $this->storeIds($brand);
        $gmvStatuses = Order::gmvStatuses();
        $excludedStatuses = Order::excludedStatuses();

        $totalGmv   = Order::whereIn('store_id',$storeIds)->whereNotIn('status',$excludedStatuses)->whereBetween('order_date',[$start,$end])->sum('gmv');
        $prevGmv    = Order::whereIn('store_id',$storeIds)->whereNotIn('status',$excludedStatuses)->whereBetween('order_date',[$prevStart,$prevEnd])->sum('gmv');
        $gmvDelta   = $prevGmv > 0 ? round((($totalGmv-$prevGmv)/$prevGmv)*100,1) : null;

        $totalOrders  = Order::whereIn('store_id',$storeIds)->whereBetween('order_date',[$start,$end])->count();
        $cancelOrders = Order::whereIn('store_id',$storeIds)->whereIn('status',['cancelled','returned','refunded'])->whereBetween('order_date',[$start,$end])->count();
        $cancelRate   = $totalOrders > 0 ? round($cancelOrders/$totalOrders*100,2) : 0;

        $totalSpend      = AdsPerformance::whereIn('store_id',$storeIds)->whereBetween('date',[$start,$end])->sum('spend');
        $totalGmvFromAds = AdsPerformance::whereIn('store_id',$storeIds)->whereBetween('date',[$start,$end])->sum('gmv_from_ads');
        $blendedRoas     = $totalSpend > 0 ? round($totalGmvFromAds/$totalSpend,2) : 0;

        $totalVisitors = StoreMetric::whereIn('store_id',$storeIds)->whereBetween('date',[$start,$end])->sum('visitors');
        $totalBuyers   = StoreMetric::whereIn('store_id',$storeIds)->whereBetween('date',[$start,$end])->sum('buyers');
        $avgCvr        = $totalVisitors > 0 ? round($totalBuyers/$totalVisitors*100,2) : 0;

        $gmvTrend = Order::whereIn('store_id',$storeIds)->whereNotIn('status',$excludedStatuses)
            ->whereBetween('order_date',[$start,$end])
            ->select(DB::raw('DATE(order_date) as day'), DB::raw('SUM(gmv) as total'))
            ->groupBy('day')->orderBy('day')->get();

        $brandProgress = [];
        foreach (['DTHREE','HURIM','ASFARA'] as $b) {
            $bIds   = Store::where('brand',$b)->where('is_active',true)->pluck('id');
            $actual = Order::whereIn('store_id',$bIds)->whereNotIn('status',$excludedStatuses)->whereBetween('order_date',[$start,$end])->sum('gmv');
            // Target: sum across months spanned by range
            $target = 0;
            $cur = $start->copy()->startOfMonth();
            while ($cur->lte($end)) {
                $target += Target::whereIn('store_id',$bIds)->where('month',$cur->month)->where('year',$cur->year)->sum('gmv_target');
                $cur->addMonth();
            }
            $brandProgress[$b] = ['actual'=>$actual,'target'=>$target,'pct'=>$target>0?min(100,round($actual/$target*100,1)):0];
        }

        $leaderboard = Store::whereIn('id',$storeIds)->with('pic')->get()->map(function($s) use($start,$end,$gmvStatuses){
            return ['store'=>$s,'gmv'=>Order::where('store_id',$s->id)->whereNotIn('status',$excludedStatuses)->whereBetween('order_date',[$start,$end])->sum('gmv')];
        })->sortByDesc('gmv')->values();

        $trendMonths = [];
        for ($i=5;$i>=0;$i--) {
            $d = now()->subMonths($i);
            $mpIds    = Store::where('channel_type','marketplace')->pluck('id');
            $nonMpIds = Store::where('channel_type','non_marketplace')->pluck('id');
            $trendMonths[] = [
                'label'  => $d->format('M Y'),
                'mp'     => Order::whereIn('store_id',$mpIds)->whereNotIn('status',$excludedStatuses)->whereYear('order_date',$d->year)->whereMonth('order_date',$d->month)->sum('gmv'),
                'non_mp' => Order::whereIn('store_id',$nonMpIds)->whereNotIn('status',$excludedStatuses)->whereYear('order_date',$d->year)->whereMonth('order_date',$d->month)->sum('gmv'),
            ];
        }

        // Legacy compat vars for view
        $year = $start->year; $mon = $start->month;
        return view('dashboard.index', compact('totalGmv','prevGmv','gmvDelta','totalOrders','cancelRate','blendedRoas','avgCvr','gmvTrend','brandProgress','leaderboard','trendMonths','brand','year','mon','dateFrom','dateTo'));
    }

    private function storeIds(string $brand) {
        $q = Store::where('is_active', true);
        if ($brand !== 'all') $q->where('brand', $brand);
        return $q->pluck('id');
    }

    private function dateRange(Request $request): array {
        $defaultFrom = now()->startOfMonth()->toDateString();
        $defaultTo   = now()->toDateString();
        // Backward compat: if old ?month= param used
        if ($request->has('month') && !$request->has('date_from')) {
            [$y,$m] = explode('-', $request->get('month'));
            $defaultFrom = Carbon::create($y,$m,1)->startOfMonth()->toDateString();
            $defaultTo   = Carbon::create($y,$m,1)->endOfMonth()->toDateString();
        }
        $dateFrom = $request->get('date_from', $defaultFrom);
        $dateTo   = $request->get('date_to',   $defaultTo);
        $start    = Carbon::parse($dateFrom)->startOfDay();
        $end      = Carbon::parse($dateTo)->endOfDay();
        if ($start->gt($end)) [$start, $end] = [$end, $start];
        return [$start, $end, $start->toDateString(), $end->toDateString()];
    }
}
