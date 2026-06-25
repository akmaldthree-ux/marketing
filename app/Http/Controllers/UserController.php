<?php
namespace App\Http\Controllers;
use App\Models\{User, Pic};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller {
    public function index() {
        $users = User::with('pic')->orderBy('name')->get();
        $pics  = Pic::where('is_active', true)->get();
        return view('users.index', compact('users', 'pics'));
    }
    public function create() {
        $pics = Pic::where('is_active', true)->get();
        return view('users.create', compact('pics'));
    }
    public function store(Request $request) {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:admin,pic,viewer',
            'pic_id'   => 'nullable|exists:pics,id',
        ]);
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return back()->with('success', 'User berhasil ditambahkan.');
    }
    public function edit(User $user) {
        $pics = Pic::where('is_active', true)->get();
        return view('users.edit', compact('user', 'pics'));
    }
    public function update(Request $request, User $user) {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,'.$user->id,
            'role'     => 'required|in:admin,pic,viewer',
            'pic_id'   => 'nullable|exists:pics,id',
            'password' => 'nullable|string|min:8',
        ]);
        if (empty($data['password'])) unset($data['password']);
        else $data['password'] = Hash::make($data['password']);
        $user->update($data);
        return back()->with('success', 'User berhasil diperbarui.');
    }
    public function destroy(User $user) {
        if ($user->id === auth()->id()) return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }
}
