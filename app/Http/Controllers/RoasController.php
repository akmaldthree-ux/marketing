<?php
namespace App\Http\Controllers;
use App\Models\{Store, AdsPerformance};
use Illuminate\Http\Request;
use Carbon\Carbon;
class RoasController extends Controller {
    public function index(Request $request) {
        [$start, $end, $dateFrom, $dateTo] = $this->dateRange($request);
        $platform = $request->get('platform', 'all');

        $query = AdsPerformance::with('store')->whereBetween('date',[$start,$end]);
        if ($platform !== 'all') $query->where('platform', $platform);

        $records = $query->get();
        $byStore = $records->groupBy('store_id')->map(function($rows) {
            $spend = $rows->sum('spend');
            $gmv   = $rows->sum('gmv_from_ads');
            return [
                'store'       => $rows->first()->store,
                'spend'       => $spend,
                'gmv'         => $gmv,
                'impressions' => $rows->sum('impressions'),
                'clicks'      => $rows->sum('clicks'),
                'conversions' => $rows->sum('conversions'),
                'roas'        => $spend > 0 ? round($gmv/$spend,2) : 0,
            ];
        })->sortByDesc('roas')->values();

        $totalSpend = $records->sum('spend');
        $totalGmv   = $records->sum('gmv_from_ads');
        $blendedRoas= $totalSpend > 0 ? round($totalGmv/$totalSpend,2) : 0;

        return view('roas.index', compact('byStore','totalSpend','totalGmv','blendedRoas','platform','dateFrom','dateTo'));
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
