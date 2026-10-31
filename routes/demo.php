<?php

use App\Http\Controllers\Admin\DemoResetController;
use Illuminate\Support\Facades\Route;

Route::middleware('admin')->prefix('admin/demo')->group(function () {
    Route::get('/reset', [DemoResetController::class, 'show'])->name('admin.demo.reset');
    Route::post('/reset', [DemoResetController::class, 'reset'])->name('admin.demo.reset.store');
});
