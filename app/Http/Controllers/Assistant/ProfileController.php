<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        return view('assistant.profile.index');
    }

    public function update(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $request->validate(['name' => 'required|string|max:255']);

        $user->update(['name' => $request->name]);

        return back()->with('success', 'Name updated successfully.');
    }

    public function password(Request $request)
    {
        if (!$request->filled('current_password') && !$request->filled('password')) {
            return back();
        }

        $user = User::findOrFail(Auth::id());

        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }
}