<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DemoResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoResetController extends Controller
{
    public function show()
    {
        return view('admin.reset');
    }

    public function reset(Request $request, DemoResetService $reset)
    {
        $request->validate([
            'confirmation' => ['required', 'in:'.config('demo.reset_phrase')],
        ]);

        $reset->reset();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'Demonstration data was restored. Sign in again with the original administrator account.');
    }
}
