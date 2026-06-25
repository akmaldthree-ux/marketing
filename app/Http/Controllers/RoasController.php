<?php
namespace App\Http\Controllers;
use App\Models\{Store, AdsPerformance};
use Illuminate\Http\Request;
use Carbon\Carbon;
class RoasController extends Controller {
    public function index(Request $request) {
        $month    = $request->get('month', now()->format('Y-m'));
        $platform = $request->get('platform', 'all');
        [$year, $mon] = explode('-', $month);
        $start = Carbon::create($year,$mon,1)->startOfMonth();
        $end   = Carbon::create($year,$mon,1)->endOfMonth();

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

        return view('roas.index', compact('byStore','totalSpend','totalGmv','blendedRoas','month','platform'));
    }
}
