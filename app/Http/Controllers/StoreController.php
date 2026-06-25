<?php
namespace App\Http\Controllers;
use App\Models\{Store, Pic};
use Illuminate\Http\Request;
class StoreController extends Controller {
    public function index() {
        $stores = Store::with('pic')->orderBy('brand')->orderBy('name')->get();
        $pics = Pic::where('is_active', true)->get();
        return view('stores.index', compact('stores', 'pics'));
    }
    public function store(Request $request) {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'brand'        => 'required|in:DTHREE,HURIM,ASFARA',
            'platform'     => 'required|in:Shopee,TikTok Shop,Meta Ads',
            'channel_type' => 'required|in:marketplace,non_marketplace',
            'pic_id'       => 'nullable|exists:pics,id',
            'is_active'    => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Store::create($data);
        return back()->with('success', 'Toko berhasil ditambahkan.');
    }
    public function edit(Store $store) {
        $pics = Pic::where('is_active', true)->get();
        return view('stores.edit', compact('store', 'pics'));
    }
    public function update(Request $request, Store $store) {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'brand'        => 'required|in:DTHREE,HURIM,ASFARA',
            'platform'     => 'required|in:Shopee,TikTok Shop,Meta Ads',
            'channel_type' => 'required|in:marketplace,non_marketplace',
            'pic_id'       => 'nullable|exists:pics,id',
            'is_active'    => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $store->update($data);
        return back()->with('success', 'Toko berhasil diperbarui.');
    }
    public function destroy(Store $store) {
        $store->delete();
        return back()->with('success', 'Toko berhasil dihapus.');
    }
    public function create() {
        $pics = Pic::where('is_active', true)->get();
        return view('stores.create', compact('pics'));
    }
}
