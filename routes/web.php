<?php

use App\Http\Controllers\PortalAuthCallbackController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

// Deep-link receiver: einundzwanzig://auth?token=… arrives here.
Route::get('auth', PortalAuthCallbackController::class)->name('portal.callback');
Route::view('meetups', 'meetups')->name('meetups');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
