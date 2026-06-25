<?php
namespace App\Http\Controllers;
use App\Models\Cog;
use Illuminate\Http\Request;
class CogController extends Controller {
    public function index() {
        $cogs = Cog::orderBy('product_sku')->get();
        return view('cogs.index', compact('cogs'));
    }
    public function store(Request $request) {
        $data = $request->validate([
            'product_sku'   => 'required|string|max:100',
            'product_name'  => 'required|string|max:255',
            'hpp_per_unit'  => 'required|numeric|min:0',
            'effective_from'=> 'required|date',
        ]);
        Cog::updateOrCreate(['product_sku' => $data['product_sku']], $data);
        return back()->with('success', 'HPP berhasil disimpan.');
    }
    public function update(Request $request, Cog $cog) {
        $data = $request->validate([
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
