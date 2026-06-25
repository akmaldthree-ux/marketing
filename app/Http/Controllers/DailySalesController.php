<?php
namespace App\Http\Controllers;

use App\Models\{Store, Order};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailySalesController extends Controller
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
        $storeIds = $storeQuery->pluck('id');

        $gmvStatuses = ['complete', 'shipped', 'processing', 'pending'];

        $dailySales = Order::whereIn('store_id', $storeIds)->whereIn('status', $gmvStatuses)
            ->whereBetween('date', [$startDate, $endDate])
            ->select(DB::raw('date(date) as day'), DB::raw('sum(gmv) as total'), DB::raw('count(*) as orders'))
            ->groupBy('day')->orderBy('day')->get();

        $today = Order::whereIn('store_id', $storeIds)->whereIn('status', $gmvStatuses)
            ->whereDate('date', today())->sum('gmv');
        $yesterday = Order::whereIn('store_id', $storeIds)->whereIn('status', $gmvStatuses)
            ->whereDate('date', today()->subDay())->sum('gmv');
        $thisWeek = Order::whereIn('store_id', $storeIds)->whereIn('status', $gmvStatuses)
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->sum('gmv');
        $lastWeek = Order::whereIn('store_id', $storeIds)->whereIn('status', $gmvStatuses)
            ->whereBetween('date', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->sum('gmv');

        $storeBreakdown = Store::whereIn('id', $storeIds)->get()->map(function ($store) use ($startDate, $endDate, $gmvStatuses) {
            return [
                'store' => $store,
                'gmv' => Order::where('store_id', $store->id)->whereIn('status', $gmvStatuses)->whereBetween('date', [$startDate, $endDate])->sum('gmv'),
                'orders' => Order::where('store_id', $store->id)->whereIn('status', $gmvStatuses)->whereBetween('date', [$startDate, $endDate])->count(),
            ];
        })->sortByDesc('gmv')->values();

        return view('daily-sales.index', compact('dailySales', 'today', 'yesterday', 'thisWeek', 'lastWeek', 'storeBreakdown', 'month', 'brand'));
    }
}
