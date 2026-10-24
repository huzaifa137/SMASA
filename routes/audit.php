<?php

use App\Http\Controllers\AuditTrailController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
| Audit trail viewer. Access is enforced in the controller
| (platform admins: all schools; school admins: their own school).
*/
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localized', 'AdminAuth']], function () {
    Route::prefix('audit-trail')->controller(AuditTrailController::class)->group(function () {
        Route::get('/', 'index')->name('audit.index');
        Route::get('/export', 'export')->name('audit.export');
        Route::post('/verify', 'verify')->name('audit.verify');
        Route::get('/{id}', 'show')->whereNumber('id')->name('audit.show');
    });
});
