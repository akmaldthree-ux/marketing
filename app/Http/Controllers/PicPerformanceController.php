<?php
namespace App\Http\Controllers;

use App\Models\{Pic, Store, Order, AdsPerformance};
use Illuminate\Http\Request;
use Carbon\Carbon;

class PicPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $mon] = explode('-', $month);
        $start = Carbon::create($year, $mon, 1)->startOfMonth();
        $end   = Carbon::create($year, $mon, 1)->endOfMonth();
        $gmvStatuses = Order::gmvStatuses();
        $excludedStatuses = Order::excludedStatuses();

        $pics = Pic::where('is_active', true)->with('stores')->get()->map(function ($pic) use ($start, $end, $gmvStatuses) {
            $storeIds = $pic->stores->pluck('id');

            $gmv    = Order::whereIn('store_id', $storeIds)->whereNotIn('status', $excludedStatuses)->whereBetween('order_date', [$start, $end])->sum('gmv');
            $orders = Order::whereIn('store_id', $storeIds)->whereBetween('order_date', [$start, $end])->count();
            $cancel = Order::whereIn('store_id', $storeIds)->whereIn('status', ['cancelled','returned','refunded'])->whereBetween('order_date', [$start, $end])->count();
            $spend  = AdsPerformance::whereIn('store_id', $storeIds)->whereBetween('date', [$start, $end])->sum('spend');
            $gmvAds = AdsPerformance::whereIn('store_id', $storeIds)->whereBetween('date', [$start, $end])->sum('gmv_from_ads');

            return [
                'pic'         => $pic,
                'store_count' => $pic->stores->count(),
                'gmv'         => $gmv,
                'orders'      => $orders,
                'cancel_rate' => $orders > 0 ? round($cancel / $orders * 100, 2) : 0,
                'roas'        => $spend > 0 ? round($gmvAds / $spend, 2) : 0,
                'ads_spend'   => $spend,
            ];
        })->sortByDesc('gmv')->values();

        return view('pic-performance.index', compact('pics', 'month'));
    }
}
