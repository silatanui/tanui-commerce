<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\RegisterRequest;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    public function __construct(private CartService $carts) {}

    public function showLogin()
    {
        return view('storefront.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! $user->isActive() || $user->isAdmin() || ! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These demonstration credentials do not match a customer account.',
            ]);
        }

        $request->session()->regenerate();
        $this->carts->merge(current_store(), $user);

        return redirect()->intended(route('store.account', current_store()->slug));
    }

    public function showRegister()
    {
        return view('storefront.auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $user = User::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->input('phone'),
            'password' => $request->string('password')->toString(),
            'role' => 'customer',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $this->carts->merge(current_store(), $user);

        return redirect()->route('store.account', current_store()->slug);
    }

    public function logout(Request $request)
    {
        $slug = current_store()->slug;
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('store.home', $slug);
    }
}
