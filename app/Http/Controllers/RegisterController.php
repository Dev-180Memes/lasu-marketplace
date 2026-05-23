<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'edu_email'  => [
                'nullable', 'string', 'email', 'max:255', 'unique:users',
                'regex:/^[a-zA-Z0-9._%+\-]+@lasu\.edu\.ng$/',
            ],
            'role'       => ['required', 'in:buyer,seller'],
            'faculty'    => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'edu_email.regex' => 'The institutional email must be a valid @lasu.edu.ng address.',
        ]);

        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'edu_email'  => $request->edu_email,
            'role'       => $request->role,
            'faculty'    => $request->faculty,
            'department' => $request->department,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
            'status'     => 'active',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
