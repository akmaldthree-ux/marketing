<?php
namespace App\Http\Controllers;
use App\Models\{Store, StoreMetric, FunnelTarget};
use Illuminate\Http\Request;
use Carbon\Carbon;
class FunnelController extends Controller {
    public function index(Request $request) {
        [$start, $end, $dateFrom, $dateTo] = $this->dateRange($request);
        $storeId = $request->get('store_id', 'all');

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
        return view('funnel.index', compact('funnel','stores','storeId','dateFrom','dateTo'));
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
