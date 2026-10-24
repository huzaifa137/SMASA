<?php

use App\Http\Controllers\AdminProfileController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
| Admin portal "User Profile" (settings icon, top right).
| Self-service only — the account is always the one in session('LoggedAdmin').
*/
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localized', 'AdminAuth']], function () {
    Route::get('/admin-profile', [AdminProfileController::class, 'show'])->name('admin.profile');
    Route::post('/admin-profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
});
