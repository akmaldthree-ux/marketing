<?php
namespace App\Http\Controllers;
use App\Models\{Customer, Order, Store};
use Illuminate\Http\Request;
use Carbon\Carbon;
class CustomerController extends Controller {
    public function index(Request $request) {
        $month   = $request->get('month', now()->format('Y-m'));
        $platform= $request->get('platform', 'all');
        [$year, $mon] = explode('-', $month);
        $start = Carbon::create($year,$mon,1)->startOfMonth();
        $end   = Carbon::create($year,$mon,1)->endOfMonth();

        $storeIds = Store::where('is_active',true)->pluck('id');
        $gmvStatuses = Order::gmvStatuses();

        $newCustomers = Order::whereIn('store_id',$storeIds)->where('is_new_customer',true)->whereIn('status',$gmvStatuses)->whereBetween('order_date',[$start,$end])->count();
        $returningCustomers = Order::whereIn('store_id',$storeIds)->where('is_new_customer',false)->whereIn('status',$gmvStatuses)->whereBetween('order_date',[$start,$end])->count();
        $totalOrders = $newCustomers + $returningCustomers;
        $repeatRate  = $totalOrders > 0 ? round($returningCustomers/$totalOrders*100,1) : 0;

        $topCustomers = Customer::withCount('orders')->when($platform!=='all',fn($q)=>$q->where('platform',$platform))->orderByDesc('total_orders')->limit(20)->get();

        return view('customers.index', compact('newCustomers','returningCustomers','repeatRate','topCustomers','month','platform'));
    }
}
