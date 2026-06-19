<?php
namespace App\Http\Controllers;

use App\Models\{Store, Financial};
use Illuminate\Http\Request;
use Carbon\Carbon;

class PnlController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));
        $view = $request->get('view', 'store');
        $brand = $request->get('brand', 'all');

        $storeQuery = Store::query()->where('is_active', true);
        if ($brand !== 'all') $storeQuery->where('brand', $brand);
        $stores = $storeQuery->with('pic')->get();

        $pnlData = [];
        if ($view === 'store') {
            foreach ($stores as $store) {
                $fin = Financial::where('store_id', $store->id)->where('period', $period)->first();
                if ($fin) $pnlData[] = ['label' => $store->store_name, 'brand' => $store->brand, 'pic' => $store->pic?->name, 'data' => $fin];
            }
        } elseif ($view === 'brand') {
            foreach (['DTHREE', 'HURIM', 'ASFARA'] as $b) {
                if ($brand !== 'all' && $brand !== $b) continue;
                $bStoreIds = Store::where('brand', $b)->pluck('id');
                $fins = Financial::whereIn('store_id', $bStoreIds)->where('period', $period)->get();
                if ($fins->isNotEmpty()) {
                    $agg = new \stdClass;
                    foreach (['gross_gmv','net_gmv','admin_fee','promo_xtra','ongkir_fee','settlement','hpp_total','ads_spend','operational_cost','gross_profit','net_profit'] as $col) {
                        $agg->$col = $fins->sum($col);
                    }
                    $pnlData[] = ['label' => $b, 'brand' => $b, 'pic' => null, 'data' => $agg];
                }
            }
        } elseif ($view === 'pic') {
            $pics = \App\Models\Pic::where('is_active', true)->get();
            foreach ($pics as $pic) {
                $pStoreIds = Store::where('pic_id', $pic->id)->pluck('id');
                $fins = Financial::whereIn('store_id', $pStoreIds)->where('period', $period)->get();
                if ($fins->isNotEmpty()) {
                    $agg = new \stdClass;
                    foreach (['gross_gmv','net_gmv','admin_fee','promo_xtra','ongkir_fee','settlement','hpp_total','ads_spend','operational_cost','gross_profit','net_profit'] as $col) {
                        $agg->$col = $fins->sum($col);
                    }
                    $pnlData[] = ['label' => $pic->name, 'brand' => null, 'pic' => $pic->name, 'data' => $agg];
                }
            }
        }

        $totalNetProfit = collect($pnlData)->sum(fn($r) => $r['data']->net_profit ?? 0);
        $totalGrossGmv = collect($pnlData)->sum(fn($r) => $r['data']->gross_gmv ?? 0);

        return view('pnl.index', compact('pnlData', 'period', 'view', 'brand', 'totalNetProfit', 'totalGrossGmv'));
    }
}
