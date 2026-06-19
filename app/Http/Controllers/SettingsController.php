<?php
namespace App\Http\Controllers;

use App\Models\{User, Store, Pic};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $users = User::with('pic')->get();
        $stores = Store::with('pic')->get();
        $pics = Pic::all();
        return view('settings.index', compact('users', 'stores', 'pics'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate(['name' => 'required', 'email' => 'required|email']);
        auth()->user()->update($request->only('name', 'email'));
        if ($request->filled('password')) {
            auth()->user()->update(['password' => Hash::make($request->password)]);
        }
        return back()->with('success', 'Profil berhasil diupdate.');
    }
}
