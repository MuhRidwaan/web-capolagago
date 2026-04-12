<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $hasPhoneColumn = Schema::hasColumn('users', 'phone');

        $request->merge([
            'phone' => preg_replace('/\s+/', '', (string) $request->input('phone', '')),
            'email' => strtolower(trim((string) $request->input('email', ''))),
        ]);

        $rules = [
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];

        if ($hasPhoneColumn) {
            $rules['phone'] = ['required', 'string', 'regex:/^08[0-9]{8,13}$/'];
        }

        $validated = $request->validate($rules);

        $attributes = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        if ($hasPhoneColumn) {
            $attributes['phone'] = $validated['phone'];
        }

        $user = User::create($attributes);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('frontend.home'));
    }
}
