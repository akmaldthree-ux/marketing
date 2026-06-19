<?php
namespace App\Http\Controllers;

use App\Models\{Customer, Order, Store};
use Illuminate\Http\Request;
use Carbon\Carbon;

class CustomerController extends Controller
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

        $totalOrders = Order::whereIn('store_id', $storeIds)->where('status', 'complete')->whereBetween('date', [$startDate, $endDate])->count();
        $newOrders = Order::whereIn('store_id', $storeIds)->where('status', 'complete')->where('is_new_customer', true)->whereBetween('date', [$startDate, $endDate])->count();
        $returningOrders = $totalOrders - $newOrders;
        $ror = $totalOrders > 0 ? round(($returningOrders / $totalOrders) * 100, 2) : 0;

        $storeData = Store::whereIn('id', $storeIds)->get()->map(function ($store) use ($startDate, $endDate) {
            $total = Order::where('store_id', $store->id)->where('status', 'complete')->whereBetween('date', [$startDate, $endDate])->count();
            $returning = Order::where('store_id', $store->id)->where('status', 'complete')->where('is_new_customer', false)->whereBetween('date', [$startDate, $endDate])->count();
            $ror = $total > 0 ? round(($returning / $total) * 100, 2) : 0;
            return compact('store', 'total', 'returning', 'ror');
        });

        $cohort = [];
        for ($i = 3; $i >= 0; $i--) {
            $d = Carbon::create($year, $mon, 1)->subMonths($i);
            $newInMonth = Order::whereIn('store_id', $storeIds)->where('status', 'complete')->where('is_new_customer', true)
                ->whereYear('date', $d->year)->whereMonth('date', $d->month)->count();
            $cohort[] = ['month' => $d->format('M Y'), 'new_customers' => $newInMonth];
        }

        return view('customer.index', compact('totalOrders', 'newOrders', 'returningOrders', 'ror', 'storeData', 'cohort', 'month', 'brand'));
    }
}
