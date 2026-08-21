<?php
namespace App\Http\Controllers;
use App\Models\{Customer, Order, Store};
use Illuminate\Http\Request;
use Carbon\Carbon;

class CustomerController extends Controller {
    public function index(Request $request) {
        [$start, $end, $dateFrom, $dateTo] = $this->dateRange($request);
        $platform = $request->get('platform', 'all');

        $storeIds    = Store::where('is_active', true)->pluck('id')->toArray();
        $gmvStatuses = Order::gmvStatuses();

        $newCustomers       = Order::whereIn('store_id', $storeIds)->where('is_new_customer', true)->whereIn('status', $gmvStatuses)->whereBetween('order_date', [$start, $end])->count();
        $returningCustomers = Order::whereIn('store_id', $storeIds)->where('is_new_customer', false)->whereIn('status', $gmvStatuses)->whereBetween('order_date', [$start, $end])->count();
        $totalOrders = $newCustomers + $returningCustomers;
        $repeatRate  = $totalOrders > 0 ? round($returningCustomers / $totalOrders * 100, 1) : 0;

        // Tren 6 bulan terakhir (fixed lookback, not tied to selected range)
        $trendMonths = [];
        for ($i = 5; $i >= 0; $i--) {
            $d    = now()->subMonths($i);
            $s    = $d->copy()->startOfMonth();
            $e    = $d->copy()->endOfMonth();
            $newC = Order::whereIn('store_id', $storeIds)->where('is_new_customer', true)->whereIn('status', $gmvStatuses)->whereBetween('order_date', [$s, $e])->count();
            $retC = Order::whereIn('store_id', $storeIds)->where('is_new_customer', false)->whereIn('status', $gmvStatuses)->whereBetween('order_date', [$s, $e])->count();
            $tot  = $newC + $retC;
            $trendMonths[] = [
                'label'         => $d->format('M Y'),
                'new_customers' => $newC,
                'returning'     => $retC,
                'repeat_rate'   => $tot > 0 ? round($retC / $tot * 100, 1) : 0,
            ];
        }

        $topCustomers = Customer::withCount('orders')
            ->when($platform !== 'all', fn($q) => $q->where('platform', $platform))
            ->orderByDesc('total_orders')->limit(20)->get();

        return view('customers.index', compact('newCustomers', 'returningCustomers', 'repeatRate', 'topCustomers', 'platform', 'trendMonths', 'dateFrom', 'dateTo'));
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
