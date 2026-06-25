<?php
namespace App\Http\Controllers;
use App\Models\{Store, Financial};
use Illuminate\Http\Request;
class PnlController extends Controller {
    public function index(Request $request) {
        $year  = $request->get('year', now()->year);
        $brand = $request->get('brand', 'all');
        $storeIds = Store::where('is_active',true)->when($brand!=='all',fn($q)=>$q->where('brand',$brand))->pluck('id');

        $financials = Financial::with('store')->whereIn('store_id',$storeIds)
            ->where('period','like',$year.'-%')->orderBy('period')->get();

        $byMonth = $financials->groupBy(fn($f)=>substr($f->period,0,7))->map(function($rows){
            return [
                'gross_gmv'        => $rows->sum('gross_gmv'),
                'net_gmv'          => $rows->sum('net_gmv'),
                'cogs'             => $rows->sum('cogs'),
                'ads_spend'        => $rows->sum('ads_spend'),
                'operational_cost' => $rows->sum('operational_cost'),
                'gross_profit'     => $rows->sum('gross_profit'),
                'net_profit'       => $rows->sum('net_profit'),
            ];
        });

        $stores = Store::where('is_active',true)->get();
        return view('pnl.index', compact('byMonth','financials','stores','year','brand'));
    }
}
