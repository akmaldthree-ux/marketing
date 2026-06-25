<?php
namespace App\Http\Controllers;
use App\Models\{Cog, Store};
use Illuminate\Http\Request;
class CogController extends Controller {
    public function index() {
        $cogs   = Cog::with('store')->orderByDesc('effective_from')->get();
        $stores = Store::where('is_active', true)->get();
        return view('cogs.index', compact('cogs', 'stores'));
    }
    public function create() {
        $stores = Store::where('is_active', true)->get();
        return view('cogs.create', compact('stores'));
    }
    public function store(Request $request) {
        $data = $request->validate([
            'store_id'      => 'required|exists:stores,id',
            'product_sku'   => 'required|string|max:100',
            'product_name'  => 'required|string|max:255',
            'hpp_per_unit'  => 'required|numeric|min:0',
            'effective_from'=> 'required|date',
        ]);
        Cog::create($data);
        return back()->with('success', 'HPP berhasil ditambahkan.');
    }
    public function edit(Cog $cog) {
        $stores = Store::where('is_active', true)->get();
        return view('cogs.edit', compact('cog', 'stores'));
    }
    public function update(Request $request, Cog $cog) {
        $data = $request->validate([
            'store_id'      => 'required|exists:stores,id',
            'product_sku'   => 'required|string|max:100',
            'product_name'  => 'required|string|max:255',
            'hpp_per_unit'  => 'required|numeric|min:0',
            'effective_from'=> 'required|date',
        ]);
        $cog->update($data);
        return back()->with('success', 'HPP berhasil diperbarui.');
    }
    public function destroy(Cog $cog) {
        $cog->delete();
        return back()->with('success', 'HPP berhasil dihapus.');
    }
}
