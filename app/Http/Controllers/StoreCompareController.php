<?php
namespace App\Http\Controllers;

use App\Models\{Store, Order, AdsPerformance, Target};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StoreCompareController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $brand = $request->get('brand', 'all');
        [$year, $mon] = explode('-', $month);
        $start = Carbon::create($year, $mon, 1)->startOfMonth();
        $end   = Carbon::create($year, $mon, 1)->endOfMonth();
        $gmvStatuses = Order::gmvStatuses();

        $stores = Store::where('is_active', true)
            ->when($brand !== 'all', fn($q) => $q->where('brand', $brand))
            ->orderBy('brand')->orderBy('name')->get();

        $data = $stores->map(function ($store) use ($start, $end, $gmvStatuses, $mon, $year) {
            $gmv     = Order::where('store_id', $store->id)->whereIn('status', $gmvStatuses)->whereBetween('order_date', [$start, $end])->sum('gmv');
            $orders  = Order::where('store_id', $store->id)->whereBetween('order_date', [$start, $end])->count();
            $cancel  = Order::where('store_id', $store->id)->whereIn('status', ['cancelled','returned','refunded'])->whereBetween('order_date', [$start, $end])->count();
            $spend   = AdsPerformance::where('store_id', $store->id)->whereBetween('date', [$start, $end])->sum('spend');
            $gmvAds  = AdsPerformance::where('store_id', $store->id)->whereBetween('date', [$start, $end])->sum('gmv_from_ads');
            $target  = Target::where('store_id', $store->id)->where('month', (int)$mon)->where('year', (int)$year)->value('gmv_target') ?? 0;

            return [
                'store'       => $store,
                'gmv'         => (float)$gmv,
                'orders'      => (int)$orders,
                'cancel_rate' => $orders > 0 ? round($cancel / $orders * 100, 1) : 0,
                'roas'        => $spend > 0 ? round($gmvAds / $spend, 2) : 0,
                'ads_spend'   => (float)$spend,
                'target'      => (float)$target,
                'pct'         => $target > 0 ? min(999, round($gmv / $target * 100, 1)) : null,
                'aov'         => $orders > 0 ? round($gmv / $orders) : 0,
            ];
        })->sortByDesc('gmv')->values();

        $allStores = Store::where('is_active', true)->orderBy('brand')->orderBy('name')->get();

        return view('store-compare.index', compact('data', 'allStores', 'month', 'brand'));
    }
}
