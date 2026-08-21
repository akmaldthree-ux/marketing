<?php
namespace App\Http\Controllers;
use App\Models\{Target, Store};
use Illuminate\Http\Request;
class TargetController extends Controller {

    // Siklus tahunan: Apr (bulan ke-4) s/d Feb (bulan ke-2 tahun berikutnya)
    // Urutan kolom: Apr, Mei, Jun, Jul, Ags, Sep, Okt, Nov, Des, Jan, Feb
    public static function cycleMonths(): array {
        // [ ['month'=>4,'year_offset'=>0], ... ['month'=>2,'year_offset'=>1] ]
        return [
            ['m'=>4,'off'=>0],['m'=>5,'off'=>0],['m'=>6,'off'=>0],
            ['m'=>7,'off'=>0],['m'=>8,'off'=>0],['m'=>9,'off'=>0],
            ['m'=>10,'off'=>0],['m'=>11,'off'=>0],['m'=>12,'off'=>0],
            ['m'=>1,'off'=>1],['m'=>2,'off'=>1],
        ];
    }

    public function index(Request $request) {
        // Tentukan siklus default: jika sekarang Jan/Feb/Mar → siklus tahun lalu, sisanya siklus tahun ini
        $nowM = now()->month;
        $defaultCycle = $nowM <= 3 ? now()->year - 1 : now()->year;
        $cycle  = (int) $request->get('cycle', $defaultCycle);
        $stores = Store::where('is_active', true)->orderBy('brand')->orderBy('name')->get();

        // Ambil targets dari 2 tahun kalender yang tercakup dalam siklus ini
        $targets = Target::with('store')
            ->where(function($q) use ($cycle) {
                $q->where(fn($q2) => $q2->where('year', $cycle)->whereBetween('month', [4, 12]))
                  ->orWhere(fn($q2) => $q2->where('year', $cycle + 1)->whereBetween('month', [1, 2]));
            })
            ->get()
            ->groupBy('store_id')
            ->map(fn($rows) => $rows->keyBy(fn($r) => $r->year.'-'.str_pad($r->month,2,'0',STR_PAD_LEFT)));

        return view('targets.index', compact('stores', 'targets', 'cycle'));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'store_id'   => 'required|exists:stores,id',
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer|min:2020',
            'gmv_target' => 'required|numeric|min:0',
        ]);
        Target::updateOrCreate(
            ['store_id'=>$data['store_id'],'month'=>$data['month'],'year'=>$data['year']],
            ['gmv_target'=>$data['gmv_target']]
        );
        return back()->with('success', 'Target berhasil disimpan.');
    }

    public function update(Request $request, Target $target) {
        $data = $request->validate(['gmv_target'=>'required|numeric|min:0']);
        $target->update($data);
        return back()->with('success', 'Target berhasil diperbarui.');
    }

    public function destroy(Target $target) {
        $target->delete();
        return back()->with('success', 'Target berhasil dihapus.');
    }
}
