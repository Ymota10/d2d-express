<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

Route::get('/', function () {
    return view('welcome');
});

// Route for returning to admin after impersonation
Route::get('/return-to-admin', function () {
    if (Session::has('impersonator_id')) {
        Auth::loginUsingId(Session::pull('impersonator_id'));

        return redirect()->route('filament.admin.pages.dashboard');
    }

    abort(403);
})->name('return-to-admin');
