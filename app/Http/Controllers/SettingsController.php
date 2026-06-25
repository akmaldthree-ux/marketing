<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash};
use Illuminate\Validation\Rules\Password;
class SettingsController extends Controller {
    public function index() { return view('settings.index'); }
    public function updateProfile(Request $request) {
        $user = Auth::user();
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,'.$user->id,
        ]);
        $user->update($data);
        return back()->with('success', 'Profil berhasil diperbarui.');
    }
    public function updatePassword(Request $request) {
        $request->validate([
            'current_password' => 'required|current_password',
            'password'         => ['required','confirmed', Password::min(8)],
        ]);
        Auth::user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password berhasil diubah.');
    }
}
