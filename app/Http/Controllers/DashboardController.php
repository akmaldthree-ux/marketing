<?php
namespace App\Http\Controllers;
use App\Models\{Store, Order, AdsPerformance, Target, StoreMetric};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Carbon\Carbon;
class DashboardController extends Controller {
    public function index(Request $request) {
        $month    = $request->get('month', now()->format('Y-m'));
        $brand    = $request->get('brand', 'all');
        [$year, $mon] = explode('-', $month);
        $start    = Carbon::create($year, $mon, 1)->startOfMonth();
        $end      = Carbon::create($year, $mon, 1)->endOfMonth();
        $prevStart= $start->copy()->subMonth()->startOfMonth();
        $prevEnd  = $start->copy()->subMonth()->endOfMonth();

        $storeIds = $this->storeIds($brand);
        $gmvStatuses = Order::gmvStatuses();

        $totalGmv   = Order::whereIn('store_id',$storeIds)->whereIn('status',$gmvStatuses)->whereBetween('order_date',[$start,$end])->sum('gmv');
        $prevGmv    = Order::whereIn('store_id',$storeIds)->whereIn('status',$gmvStatuses)->whereBetween('order_date',[$prevStart,$prevEnd])->sum('gmv');
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

        $gmvTrend = Order::whereIn('store_id',$storeIds)->whereIn('status',$gmvStatuses)
            ->whereBetween('order_date',[$start,$end])
            ->select(DB::raw('DATE(order_date) as day'), DB::raw('SUM(gmv) as total'))
            ->groupBy('day')->orderBy('day')->get();

        $brandProgress = [];
        foreach (['DTHREE','HURIM','ASFARA'] as $b) {
            $bIds    = Store::where('brand',$b)->where('is_active',true)->pluck('id');
            $actual  = Order::whereIn('store_id',$bIds)->whereIn('status',$gmvStatuses)->whereBetween('order_date',[$start,$end])->sum('gmv');
            $target  = Target::whereIn('store_id',$bIds)->where('month',$mon)->where('year',$year)->sum('gmv_target');
            $brandProgress[$b] = ['actual'=>$actual,'target'=>$target,'pct'=>$target>0?min(100,round($actual/$target*100,1)):0];
        }

        $leaderboard = Store::whereIn('id',$storeIds)->with('pic')->get()->map(function($s) use($start,$end,$gmvStatuses){
            return ['store'=>$s,'gmv'=>Order::where('store_id',$s->id)->whereIn('status',$gmvStatuses)->whereBetween('order_date',[$start,$end])->sum('gmv')];
        })->sortByDesc('gmv')->values();

        $trendMonths = [];
        for ($i=5;$i>=0;$i--) {
            $d = now()->subMonths($i);
            $mpIds    = Store::where('channel_type','marketplace')->pluck('id');
            $nonMpIds = Store::where('channel_type','non_marketplace')->pluck('id');
            $trendMonths[] = [
                'label'  => $d->format('M Y'),
                'mp'     => Order::whereIn('store_id',$mpIds)->whereIn('status',$gmvStatuses)->whereYear('order_date',$d->year)->whereMonth('order_date',$d->month)->sum('gmv'),
                'non_mp' => Order::whereIn('store_id',$nonMpIds)->whereIn('status',$gmvStatuses)->whereYear('order_date',$d->year)->whereMonth('order_date',$d->month)->sum('gmv'),
            ];
        }

        return view('dashboard.index', compact('totalGmv','prevGmv','gmvDelta','totalOrders','cancelRate','blendedRoas','avgCvr','gmvTrend','brandProgress','leaderboard','trendMonths','month','brand','year','mon'));
    }

    private function storeIds(string $brand) {
        $q = Store::where('is_active', true);
        if ($brand !== 'all') $q->where('brand', $brand);
        return $q->pluck('id');
    }
}
