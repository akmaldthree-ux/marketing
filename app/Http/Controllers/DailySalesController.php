<?php
namespace App\Http\Controllers;
use App\Models\{Store, Order};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class DailySalesController extends Controller {
    public function index(Request $request) {
        $month  = $request->get('month', now()->format('Y-m'));
        $brand  = $request->get('brand', 'all');
        $storeId= $request->get('store_id', 'all');
        [$year, $mon] = explode('-', $month);
        $start  = Carbon::create($year,$mon,1)->startOfMonth();
        $end    = Carbon::create($year,$mon,1)->endOfMonth();
        $gmvStatuses = Order::gmvStatuses();

        $storeIds = Store::where('is_active',true)
            ->when($brand!=='all',fn($q)=>$q->where('brand',$brand))
            ->when($storeId!=='all',fn($q)=>$q->where('id',$storeId))
            ->pluck('id');

        $daily = Order::whereIn('store_id',$storeIds)->whereIn('status',$gmvStatuses)->whereBetween('order_date',[$start,$end])
            ->select(DB::raw('DATE(order_date) as day'),DB::raw('SUM(gmv) as gmv'),DB::raw('COUNT(*) as orders'))
            ->groupBy('day')->orderBy('day')->get();

        $summary = [
            'today'      => Order::whereIn('store_id',$storeIds)->whereIn('status',$gmvStatuses)->whereDate('order_date',today())->sum('gmv'),
            'yesterday'  => Order::whereIn('store_id',$storeIds)->whereIn('status',$gmvStatuses)->whereDate('order_date',today()->subDay())->sum('gmv'),
            'this_month' => Order::whereIn('store_id',$storeIds)->whereIn('status',$gmvStatuses)->whereBetween('order_date',[$start,$end])->sum('gmv'),
        ];

        $stores        = Store::where('is_active',true)->orderBy('brand')->orderBy('name')->get();
        $storeBreakdown= Store::whereIn('id',$storeIds)->get()->map(fn($s)=>[
            'store'  => $s,
            'gmv'    => Order::where('store_id',$s->id)->whereIn('status',$gmvStatuses)->whereBetween('order_date',[$start,$end])->sum('gmv'),
            'orders' => Order::where('store_id',$s->id)->whereIn('status',$gmvStatuses)->whereBetween('order_date',[$start,$end])->count(),
        ])->sortByDesc('gmv')->values();

        return view('daily-sales.index', compact('daily','summary','stores','storeBreakdown','month','brand','storeId'));
    }
}
