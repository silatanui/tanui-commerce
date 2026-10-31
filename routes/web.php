<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/projects', [HomeController::class, 'projects'])->name('projects');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'sendContact'])->middleware('throttle:login')->name('contact.send');

require __DIR__.'/storefront.php';
require __DIR__.'/admin.php';
require __DIR__.'/demo.php';
