<?php

use App\Http\Controllers\AuditPurgeController;
use App\Http\Controllers\AuditTrailController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
| Audit trail viewer. Access is enforced in the controller
| (platform admins: all schools; school admins: their own school).
*/
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localized', 'AdminAuth']], function () {
    // Retention purge: separate group so the literal paths are registered
    // before the numeric /{id} route below.
    Route::prefix('audit-trail/purge')->controller(AuditPurgeController::class)->group(function () {
        Route::get('/preview', 'preview')->name('audit.purge.preview');
        Route::get('/export', 'export')->name('audit.purge.export');
        Route::post('/', 'purge')->name('audit.purge');
    });

    Route::prefix('audit-trail')->controller(AuditTrailController::class)->group(function () {
        Route::get('/', 'index')->name('audit.index');
        Route::get('/export', 'export')->name('audit.export');
        Route::post('/verify', 'verify')->name('audit.verify');
        Route::get('/{id}', 'show')->whereNumber('id')->name('audit.show');
    });
});
