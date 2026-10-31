<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['email' => ['required', 'email', 'max:160']]);

        return back()->with('status', 'This is a demonstration. Your address was not stored and no subscription was created.');
    }
}
