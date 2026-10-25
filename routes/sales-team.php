<?php

use App\Http\Controllers\SalesTeamController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
| Sales Team (Admin Portal): leaders dashboard, representatives, targets,
| school transfers. AdminAuth + an Admin Rights permission on every route;
| school / teacher sessions can never satisfy `admin.can`.
*/
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localized', 'AdminAuth']], function () {
    Route::prefix('admin/sales-team')->name('sales.')->controller(SalesTeamController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('admin.can:sales_team.view_team');
        Route::get('/me', 'me')->name('me')->middleware('admin.can:sales_team.view_own,sales_team.view_team');

        Route::get('/reps', 'reps')->name('reps')->middleware('admin.can:sales_team.view_team');
        Route::post('/reps', 'storeRep')->name('reps.store')->middleware('admin.can:sales_team.manage_reps');
        Route::get('/reps/{id}', 'show')->whereNumber('id')->name('show')->middleware('admin.can:sales_team.view_team,sales_team.view_own');
        Route::put('/reps/{id}', 'updateRep')->whereNumber('id')->name('reps.update')->middleware('admin.can:sales_team.manage_reps');
        Route::post('/reps/{id}/deactivate', 'deactivateRep')->whereNumber('id')->name('reps.deactivate')->middleware('admin.can:sales_team.manage_reps');
        Route::post('/reps/{id}/reactivate', 'reactivateRep')->whereNumber('id')->name('reps.reactivate')->middleware('admin.can:sales_team.manage_reps');

        Route::get('/targets', 'targets')->name('targets')->middleware('admin.can:sales_team.view_team');
        Route::post('/targets', 'storeTarget')->name('targets.store')->middleware('admin.can:sales_team.set_targets');
        Route::put('/targets/{id}', 'updateTarget')->whereNumber('id')->name('targets.update')->middleware('admin.can:sales_team.set_targets');
        Route::delete('/targets/{id}', 'destroyTarget')->whereNumber('id')->name('targets.destroy')->middleware('admin.can:sales_team.set_targets');

        Route::get('/transfers', 'transfers')->name('transfers')->middleware('admin.can:sales_team.view_team');
        Route::get('/transfers/new', 'transferForm')->name('transfers.create')->middleware('admin.can:sales_team.transfer_schools');
        Route::post('/transfers', 'storeTransfer')->name('transfers.store')->middleware('admin.can:sales_team.transfer_schools');
        Route::get('/transfers/{id}', 'showTransfer')->whereNumber('id')->name('transfers.show')->middleware('admin.can:sales_team.view_team');

        Route::get('/unassigned', 'unassigned')->name('unassigned')->middleware('admin.can:sales_team.view_team,sales_team.assign_schools');
        Route::post('/unassigned', 'assign')->name('unassigned.assign')->middleware('admin.can:sales_team.assign_schools');

        Route::get('/export/{type}', 'export')->whereIn('type', ['leaderboard', 'portfolio', 'targets', 'transfers'])->name('export')->middleware('admin.can:sales_team.export');
    });
});
