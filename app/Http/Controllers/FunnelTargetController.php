<?php
namespace App\Http\Controllers;
use App\Models\{FunnelTarget, Store};
use Illuminate\Http\Request;
class FunnelTargetController extends Controller {
    public function index() {
        $targets = FunnelTarget::with('store')->get();
        $stores  = Store::where('is_active', true)->get();
        return view('funnel-targets.index', compact('targets', 'stores'));
    }
    public function create() {
        $stores = Store::where('is_active', true)->get();
        return view('funnel-targets.create', compact('stores'));
    }
    public function store(Request $request) {
        $data = $request->validate([
            'store_id'      => 'required|exists:stores,id',
            'stage'         => 'required|in:views_to_visitor,atc_rate,cvr',
            'target_pct'    => 'required|numeric|min:0|max:100',
            'effective_from'=> 'required|date',
        ]);
        FunnelTarget::updateOrCreate(['store_id'=>$data['store_id'],'stage'=>$data['stage']], $data);
        return back()->with('success', 'Target funnel berhasil disimpan.');
    }
    public function edit(FunnelTarget $funnelTarget) {
        $stores = Store::where('is_active', true)->get();
        return view('funnel-targets.edit', compact('funnelTarget', 'stores'));
    }
    public function update(Request $request, FunnelTarget $funnelTarget) {
        $data = $request->validate([
            'target_pct'    => 'required|numeric|min:0|max:100',
            'effective_from'=> 'required|date',
        ]);
        $funnelTarget->update($data);
        return back()->with('success', 'Target funnel berhasil diperbarui.');
    }
    public function destroy(FunnelTarget $funnelTarget) {
        $funnelTarget->delete();
        return back()->with('success', 'Target funnel berhasil dihapus.');
    }
}
