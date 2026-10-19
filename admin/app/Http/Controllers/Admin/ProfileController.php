<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('admin.profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|max:120',
            'email' => ['required', 'email', 'max:200', Rule::unique('users')->ignore($user->id)],
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:8|confirmed',
        ]);
        if (! empty($data['password'])) {
            if (! Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Your current password is not correct.']);
            }
            $user->password = $data['password'];
        }
        $user->fill(['name' => $data['name'], 'email' => $data['email']])->save();
        ActivityLog::record('updated', 'Profile', $user->name);

        return back()->with('status', 'Profile updated.');
    }
}
