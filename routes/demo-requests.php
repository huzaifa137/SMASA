<?php

use App\Http\Controllers\DemoRequestController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
| Homepage "Request a Demo".
|  - POST /request-demo is PUBLIC (rate limited: 5 submissions / 10 minutes per IP).
|  - The inbox is Admin Portal only: AdminAuth + an Admin Rights permission.
*/
Route::post('/request-demo', [DemoRequestController::class, 'store'])
    ->middleware('throttle:5,10')
    ->name('demo.request');

Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localized', 'AdminAuth']], function () {
    Route::prefix('admin/demo-requests')->controller(DemoRequestController::class)->group(function () {
        Route::get('/', 'index')->name('demo-requests.index')->middleware('admin.can:demo_requests.view');
        Route::post('/bulk-delete', 'bulkDestroy')->name('demo-requests.bulk-destroy')->middleware('admin.can:demo_requests.delete');
        Route::delete('/{id}', 'destroy')->whereNumber('id')->name('demo-requests.destroy')->middleware('admin.can:demo_requests.delete');
        Route::post('/{id}', 'update')->whereNumber('id')->name('demo-requests.update')->middleware('admin.can:demo_requests.manage');
    });
});
