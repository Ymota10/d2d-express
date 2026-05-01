<?php

use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

Route::get('/', function () {
    return view('welcome');
});

// Tracking Routes
Route::prefix('admin')->group(function () {
    Route::get('/track', [TrackingController::class, 'index'])->name('track.form');
    // Route::get('/track/{waybill}', [TrackingController::class, 'show'])->name('track.show');
    Route::get('/track/search/{waybill}', [TrackingController::class, 'search']);
});

// Return to admin after impersonation
Route::get('/return-to-admin', function () {
    if (Session::has('impersonator_id')) {
        Auth::loginUsingId(Session::pull('impersonator_id'));

        return redirect()->route('filament.admin.pages.dashboard');
    }

    abort(403);
})->name('return-to-admin');
