<?php
namespace App\Http\Controllers;
use App\Models\{Store, StoreMetric, FunnelTarget};
use Illuminate\Http\Request;
use Carbon\Carbon;
class FunnelController extends Controller {
    public function index(Request $request) {
        $month   = $request->get('month', now()->format('Y-m'));
        $storeId = $request->get('store_id', 'all');
        [$year, $mon] = explode('-', $month);
        $start = Carbon::create($year,$mon,1)->startOfMonth();
        $end   = Carbon::create($year,$mon,1)->endOfMonth();

        $storeIds = Store::where('is_active',true)->when($storeId!=='all',fn($q)=>$q->where('id',$storeId))->pluck('id');
        $metrics  = StoreMetric::whereIn('store_id',$storeIds)->whereBetween('date',[$start,$end])->get();

        $funnel = [
            'views'    => $metrics->sum('views'),
            'visitors' => $metrics->sum('visitors'),
            'atc'      => $metrics->sum('add_to_cart'),
            'checkout' => $metrics->sum('checkout'),
            'buyers'   => $metrics->sum('buyers'),
        ];
        $funnel['vtr']      = $funnel['views']>0 ? round($funnel['visitors']/$funnel['views']*100,2) : 0;
        $funnel['atc_rate'] = $funnel['visitors']>0 ? round($funnel['atc']/$funnel['visitors']*100,2) : 0;
        $funnel['cvr']      = $funnel['visitors']>0 ? round($funnel['buyers']/$funnel['visitors']*100,2) : 0;

        $stores = Store::where('is_active',true)->get();
        return view('funnel.index', compact('funnel','stores','month','storeId'));
    }
}
