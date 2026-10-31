<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('pages.home', [
            'stores' => Store::query()->where('status', 'active')->with('settings')->orderBy('id')->get(),
        ]);
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function projects(): View
    {
        return view('pages.projects', [
            'stores' => Store::query()->where('status', 'active')->with('settings')->orderBy('id')->get(),
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function sendContact(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        return back()->with('status', 'This is a demonstration. Your message was not stored and no email was sent.');
    }
}
