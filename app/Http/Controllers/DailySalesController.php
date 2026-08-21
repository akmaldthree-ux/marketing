<?php
namespace App\Http\Controllers;
use App\Models\{Store, Order};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class DailySalesController extends Controller {
    public function index(Request $request) {
        [$start, $end, $dateFrom, $dateTo] = $this->dateRange($request);
        $brand   = $request->get('brand', 'all');
        $storeId = $request->get('store_id', 'all');
        $excludedStatuses = Order::excludedStatuses();

        $storeIds = Store::where('is_active',true)
            ->when($brand!=='all',fn($q)=>$q->where('brand',$brand))
            ->when($storeId!=='all',fn($q)=>$q->where('id',$storeId))
            ->pluck('id');

        $daily = Order::whereIn('store_id',$storeIds)
            ->whereNotIn('status',$excludedStatuses)
            ->whereBetween('order_date',[$start,$end])
            ->select(
                DB::raw('DATE(order_date) as day'),
                DB::raw('SUM(gmv) as gmv'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('day')->orderBy('day')->get();

        // Tambahkan AOV, delta harian, dan running total
        $runningTotal = 0;
        $dailyWithDelta = $daily->map(function($row, $i) use ($daily, &$runningTotal) {
            $prev = $i > 0 ? $daily[$i-1] : null;
            $row->aov     = $row->orders > 0 ? round($row->gmv / $row->orders) : 0;
            $row->delta   = ($prev && $prev->gmv > 0) ? round(($row->gmv - $prev->gmv) / $prev->gmv * 100, 1) : null;
            $runningTotal += $row->gmv;
            $row->running = $runningTotal;
            return $row;
        });

        $monthStart = now()->startOfMonth()->startOfDay();
        $monthEnd   = now()->endOfDay();
        $summary = [
            'today'        => Order::whereIn('store_id',$storeIds)->whereNotIn('status',$excludedStatuses)->whereDate('order_date', now()->toDateString())->sum('gmv'),
            'yesterday'    => Order::whereIn('store_id',$storeIds)->whereNotIn('status',$excludedStatuses)->whereDate('order_date', now()->subDay()->toDateString())->sum('gmv'),
            'this_month'   => Order::whereIn('store_id',$storeIds)->whereNotIn('status',$excludedStatuses)->whereBetween('order_date',[$monthStart,$monthEnd])->sum('gmv'),
            'total_range'  => $daily->sum('gmv'),
            'total_orders' => $daily->sum('orders'),
            'avg_daily'    => $daily->count() > 0 ? round($daily->avg('gmv')) : 0,
            'best_day'     => $daily->sortByDesc('gmv')->first(),
        ];

        $stores = Store::where('is_active',true)->orderBy('brand')->orderBy('name')->get();

        return view('daily-sales.index', compact('dailyWithDelta','summary','stores','brand','storeId','dateFrom','dateTo'));
    }

    private function dateRange(Request $request): array {
        $defaultFrom = now()->startOfMonth()->toDateString();
        $defaultTo   = now()->toDateString();
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
