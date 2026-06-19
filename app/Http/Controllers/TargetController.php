<?php
namespace App\Http\Controllers;

use App\Models\{Store, Target, Order};
use Illuminate\Http\Request;
use Carbon\Carbon;

class TargetController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $stores = Store::where('is_active', true)->with('pic')->get();
        $months = range(1, 12);
        $data = [];
        foreach ($stores as $store) {
            $row = ['store' => $store, 'targets' => []];
            foreach ($months as $m) {
                $target = Target::where('store_id', $store->id)->where('month', $m)->where('year', $year)->first();
                $actual = Order::where('store_id', $store->id)->where('status', 'complete')
                    ->whereYear('date', $year)->whereMonth('date', $m)->sum('gmv');
                $row['targets'][$m] = ['target' => $target?->gmv_target ?? 0, 'actual' => $actual, 'pct' => ($target?->gmv_target ?? 0) > 0 ? min(100, round(($actual / $target->gmv_target) * 100, 1)) : 0];
            }
            $data[] = $row;
        }

        // Timeline chart: Apr 2026 - Mar 2027
        $timeline = [];
        for ($i = 0; $i < 12; $i++) {
            $d = Carbon::create(2026, 4, 1)->addMonths($i);
            $t = Target::whereIn('store_id', $stores->pluck('id'))->where('month', $d->month)->where('year', $d->year)->sum('gmv_target');
            $a = Order::whereIn('store_id', $stores->pluck('id'))->where('status', 'complete')->whereYear('date', $d->year)->whereMonth('date', $d->month)->sum('gmv');
            $timeline[] = ['label' => $d->format('M Y'), 'target' => $t, 'actual' => $a];
        }

        return view('targets.index', compact('data', 'year', 'months', 'timeline', 'stores'));
    }

    public function update(Request $request)
    {
        $request->validate(['store_id' => 'required', 'month' => 'required', 'year' => 'required', 'gmv_target' => 'required|numeric']);
        Target::updateOrCreate(
            ['store_id' => $request->store_id, 'month' => $request->month, 'year' => $request->year],
            ['gmv_target' => $request->gmv_target]
        );
        return response()->json(['success' => true]);
    }
}
