<?php

use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Auth\VelrixSsoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/login', '/login')->name('auth.login');

Route::prefix('oauth')->group(function () {
    Route::get('/redirect/{driver}', [OAuthController::class, 'redirect'])->name('auth.oauth.redirect');
    Route::get('/callback/{driver}', [OAuthController::class, 'callback'])->name('auth.oauth.callback')->withoutMiddleware('guest');
});

// Signed single sign-on from the Velrix app. withoutMiddleware('guest') so an
// already-authenticated session still processes the link instead of bouncing.
Route::get('/sso', VelrixSsoController::class)->name('auth.velrix-sso')->withoutMiddleware('guest');
