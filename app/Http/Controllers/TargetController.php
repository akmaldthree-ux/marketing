<?php
namespace App\Http\Controllers;
use App\Models\{Target, Store};
use Illuminate\Http\Request;
class TargetController extends Controller {
    public function index(Request $request) {
        $year   = $request->get('year', now()->year);
        $stores = Store::where('is_active', true)->orderBy('brand')->orderBy('name')->get();
        $targets = Target::with('store')->where('year', $year)->get()->groupBy('store_id');
        return view('targets.index', compact('stores', 'targets', 'year'));
    }
    public function store(Request $request) {
        $data = $request->validate([
            'store_id'   => 'required|exists:stores,id',
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer|min:2020',
            'gmv_target' => 'required|numeric|min:0',
        ]);
        Target::updateOrCreate(['store_id'=>$data['store_id'],'month'=>$data['month'],'year'=>$data['year']], ['gmv_target'=>$data['gmv_target']]);
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
