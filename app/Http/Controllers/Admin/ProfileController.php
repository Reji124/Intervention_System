<?php

// app/Http/Controllers/Admin/ProfileController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $admins     = User::where('role', 'admin')->latest()->get();
        $assistants = User::where('role', 'assistant')->latest()->get();

        return view('admin.profile.index', compact('admins', 'assistants'));
    }

    // ── Update own profile (name, email, password) ────────────────────────────
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = User::findOrFail(Auth::id());

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'current_password' => 'required',
                'password'         => 'required|min:8|confirmed',
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }

            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    // ── Add a new admin account ───────────────────────────────────────────────
    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'admin',
        ]);

        return back()->with('success', "Admin account created for {$request->name}.");
    }

    // ── Delete an admin account (cannot delete yourself) ─────────────────────
    public function destroyAdmin(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->role !== 'admin') {
            return back()->with('error', 'This action is only for admin accounts.');
        }

        $user->delete();
        return back()->with('success', 'Admin account removed.');
    }
}