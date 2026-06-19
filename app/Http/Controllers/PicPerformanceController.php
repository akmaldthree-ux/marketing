<?php
namespace App\Http\Controllers;

use App\Models\{Pic, Store, Order, AdsPerformance, ReturnRateLog, AppNotification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PicPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $mon] = explode('-', $month);
        $startDate = Carbon::create($year, $mon, 1)->startOfMonth();
        $endDate = Carbon::create($year, $mon, 1)->endOfMonth();

        $user = Auth::user();
        $picQuery = Pic::query()->where('is_active', true);
        if ($user->isPic()) {
            $picQuery->where('id', $user->pic_id);
        }
        $pics = $picQuery->get();

        $picScores = $pics->map(function ($pic) use ($startDate, $endDate, $mon, $year) {
            $storeIds = Store::where('pic_id', $pic->id)->pluck('id');
            $totalOrders = Order::whereIn('store_id', $storeIds)->whereBetween('date', [$startDate, $endDate])->count();
            $gmv = Order::whereIn('store_id', $storeIds)->where('status', 'complete')->whereBetween('date', [$startDate, $endDate])->sum('gmv');
            $spend = AdsPerformance::whereIn('store_id', $storeIds)->whereBetween('date', [$startDate, $endDate])->sum('spend');
            $gmvFromAds = AdsPerformance::whereIn('store_id', $storeIds)->whereBetween('date', [$startDate, $endDate])->sum('gmv_from_ads');
            $roas = $spend > 0 ? round($gmvFromAds / $spend, 2) : 0;
            $returnOrders = Order::whereIn('store_id', $storeIds)->whereIn('status', ['returned', 'refunded'])->whereBetween('date', [$startDate, $endDate])->count();
            $returnRate = $totalOrders > 0 ? round(($returnOrders / $totalOrders) * 100, 2) : 0;
            $returnStatus = $returnRate > 2 ? 'breach' : ($returnRate >= 1.5 ? 'warning' : 'normal');
            $target = \App\Models\Target::whereIn('store_id', $storeIds)->where('month', $mon)->where('year', $year)->sum('gmv_target');
            $achievement = $target > 0 ? min(100, round(($gmv / $target) * 100, 1)) : 0;
            $achievementStatus = $achievement >= 85 ? 'on_track' : ($achievement >= 60 ? 'at_risk' : 'behind');

            return [
                'pic' => $pic, 'gmv' => $gmv, 'spend' => $spend, 'roas' => $roas,
                'return_rate' => $returnRate, 'return_status' => $returnStatus,
                'target' => $target, 'achievement' => $achievement, 'achievement_status' => $achievementStatus,
                'stores' => Store::where('pic_id', $pic->id)->get(),
            ];
        });

        $totalGmv = $picScores->sum('gmv');
        $hasBreach = $picScores->contains('return_status', 'breach');

        return view('pic-performance.index', compact('picScores', 'totalGmv', 'hasBreach', 'month'));
    }
}
