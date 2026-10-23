<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MachineryController;
use App\Http\Controllers\OfflineController;
use App\Http\Controllers\PayableController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\RecurringController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\Settings;
use App\Http\Controllers\StageController;
use App\Support\SampleData;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:20,1');
});

Route::post('/locale', LocaleController::class)->name('locale');
Route::view('/offline', 'offline')->name('offline');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('seasons', SeasonController::class)->except('destroy');
    Route::patch('/seasons/{season}/status', [SeasonController::class, 'status'])->name('seasons.status');
    Route::post('/seasons/{season}/stages', [SeasonController::class, 'addStage'])->name('seasons.stages.store');
    Route::post('/seasons/{season}/close', [SeasonController::class, 'close'])->name('seasons.close');
    Route::post('/seasons/{season}/reopen', [SeasonController::class, 'reopen'])->name('seasons.reopen');

    Route::scopeBindings()->group(function () {
        Route::get('/seasons/{season}/stages/{stage}', [StageController::class, 'show'])->name('stages.show');
        Route::patch('/seasons/{season}/stages/{stage}/status', [StageController::class, 'status'])->name('stages.status');
    });

    Route::get('/seasons/{season}/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::post('/expenses/{expense}/payments', [PayableController::class, 'payExpense'])->name('expenses.pay');

    Route::get('/payables', [PayableController::class, 'index'])->name('payables.index');
    Route::post('/payments', [PayableController::class, 'store'])->name('payments.store');
    Route::delete('/payments/{payment}', [PayableController::class, 'destroy'])->name('payments.destroy');

    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::post('/storage-units', [ProductionController::class, 'storeUnit'])->name('storage-units.store');
    Route::put('/storage-units/{unit}', [ProductionController::class, 'updateUnit'])->name('storage-units.update');
    Route::scopeBindings()->prefix('/seasons/{season}')->group(function () {
        Route::post('/harvests', [ProductionController::class, 'storeHarvest'])->name('harvests.store');
        Route::put('/harvests/{harvest}', [ProductionController::class, 'updateHarvest'])->name('harvests.update');
        Route::delete('/harvests/{harvest}', [ProductionController::class, 'destroyHarvest'])->name('harvests.destroy');
        Route::post('/batches', [ProductionController::class, 'storeBatch'])->name('batches.store');
        Route::put('/batches/{batch}', [ProductionController::class, 'updateBatch'])->name('batches.update');
        Route::delete('/batches/{batch}', [ProductionController::class, 'destroyBatch'])->name('batches.destroy');
        Route::post('/batches/{batch}/advance', [ProductionController::class, 'advanceBatch'])->name('batches.advance');
    });

    Route::prefix('machinery')->name('machinery.')->group(function () {
        Route::get('/', [MachineryController::class, 'index'])->name('index');
        Route::post('/machines', [MachineryController::class, 'storeMachine'])->name('machines.store');
        Route::put('/machines/{machine}', [MachineryController::class, 'updateMachine'])->name('machines.update');
        Route::post('/usage', [MachineryController::class, 'storeUsage'])->name('usage.store');
        Route::delete('/usage/{usage}', [MachineryController::class, 'destroyUsage'])->name('usage.destroy');
        Route::post('/shared-costs', [MachineryController::class, 'storeShared'])->name('shared.store');
        Route::get('/shared-costs/{cost}', [MachineryController::class, 'showShared'])->name('shared.show');
        Route::post('/shared-costs/{cost}/allocate', [MachineryController::class, 'allocate'])->name('shared.allocate');
        Route::post('/shared-costs/{cost}/unallocate', [MachineryController::class, 'unallocate'])->name('shared.unallocate');
        Route::delete('/shared-costs/{cost}', [MachineryController::class, 'destroyShared'])->name('shared.destroy');
    });

    Route::get('/recurring', [RecurringController::class, 'index'])->name('recurring.index');
    Route::post('/recurring', [RecurringController::class, 'store'])->name('recurring.store');
    Route::put('/recurring/{rule}', [RecurringController::class, 'update'])->name('recurring.update');
    Route::post('/recurring/{rule}/confirm', [RecurringController::class, 'confirm'])->name('recurring.confirm');
    Route::post('/recurring/{rule}/skip', [RecurringController::class, 'skip'])->name('recurring.skip');

    Route::get('/offline/token', [OfflineController::class, 'token'])->name('offline.token');
    Route::post('/offline/expenses', [OfflineController::class, 'sync'])->name('offline.sync');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/seasons/{season}', [ReportController::class, 'season'])->name('season');
        Route::get('/comparison', [ReportController::class, 'comparison'])->name('comparison');
        Route::get('/categories', [ReportController::class, 'categories'])->name('categories');
        Route::get('/payables-ageing', [ReportController::class, 'ageing'])->name('ageing');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::redirect('/', '/settings/categories')->name('index');
        Route::resource('categories', Settings\CategoryController::class)->only(['index', 'store', 'update']);
        Route::resource('vendors', Settings\VendorController::class)->only(['index', 'store', 'update']);
        Route::resource('fields', Settings\FieldController::class)->only(['index', 'store', 'update']);
        Route::resource('crops', Settings\CropController::class)->only(['index', 'store', 'update']);
        Route::resource('units', Settings\UnitController::class)->only(['index', 'store', 'update']);
        Route::resource('stages', Settings\StageTemplateController::class)->only(['index', 'store', 'update']);
        Route::resource('users', Settings\UserController::class)->only(['index', 'store', 'update'])->middleware('can:manage-users');
    });

    Route::prefix('preview')->name('preview.')->group(function () {
        Route::get('/livestock', fn () => view('preview.livestock', ['animals' => SampleData::livestock()]))->name('livestock');
        Route::get('/inventory', fn () => view('preview.inventory', ['items' => SampleData::inventory()]))->name('inventory');
        Route::get('/employees', fn () => view('preview.employees', ['employees' => SampleData::employees()]))->name('employees');
    });
});
