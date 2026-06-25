<?php
namespace App\Http\Controllers;
use App\Models\{Pic, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class PicController extends Controller {
    public function index() {
        $pics = Pic::with(['user','stores'])->get();
        return view('pics.index', compact('pics'));
    }
    public function create() { return view('pics.create'); }
    public function store(Request $request) {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:pics,email',
            'password' => 'required|string|min:8',
        ]);
        $pic = Pic::create(['name'=>$data['name'],'email'=>$data['email']]);
        User::create(['name'=>$pic->name,'email'=>$pic->email,'password'=>Hash::make($data['password']),'role'=>'pic','pic_id'=>$pic->id]);
        return back()->with('success', 'PIC berhasil ditambahkan.');
    }
    public function edit(Pic $pic) { return view('pics.edit', compact('pic')); }
    public function update(Request $request, Pic $pic) {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:pics,email,'.$pic->id,
            'is_active' => 'boolean',
            'password'  => 'nullable|string|min:8',
        ]);
        $pic->update(['name'=>$data['name'],'email'=>$data['email'],'is_active'=>$request->boolean('is_active',true)]);
        if ($pic->user) {
            $userUpdate = ['name'=>$data['name'],'email'=>$data['email']];
            if (!empty($data['password'])) $userUpdate['password'] = Hash::make($data['password']);
            $pic->user->update($userUpdate);
        }
        return back()->with('success', 'PIC berhasil diperbarui.');
    }
    public function destroy(Pic $pic) {
        $pic->delete();
        return back()->with('success', 'PIC berhasil dihapus.');
    }
}
