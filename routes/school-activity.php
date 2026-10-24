<?php

use App\Http\Controllers\SchoolActivityController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
| School Activity Monitor (Admin Portal).
| AdminAuth + an Admin Rights permission on every route. School / teacher
| sessions can never satisfy `admin.can`, so this is platform-admin only.
*/
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localized', 'AdminAuth']], function () {
    Route::prefix('admin/school-activity')->controller(SchoolActivityController::class)->group(function () {
        Route::get('/', 'index')->name('school-activity.index')->middleware('admin.can:school_activity.view');
        Route::get('/export', 'export')->name('school-activity.export')->middleware('admin.can:school_activity.export');
        Route::post('/refresh', 'refresh')->name('school-activity.refresh')->middleware('admin.can:school_activity.view');
        Route::get('/{id}', 'show')->whereNumber('id')->name('school-activity.show')->middleware('admin.can:school_activity.view');
    });
});
