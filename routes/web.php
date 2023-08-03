<?php

use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::view('/', 'index')->name('dashboard');

Route::name('schedules.')->prefix('schedules')->group(function () {
    Route::get('/', [ScheduleController::class, 'index'])
        ->name('home');

    Route::post('/', [ScheduleController::class, 'storeShipConstructionSchedule'])
        ->name('store_construction_schedule');

    Route::get('/{id}', [ScheduleController::class, 'showScheduleDataById'])
        ->name('schedule_progress');

    Route::post('/{id}/', [ScheduleController::class, 'storeCriteriaSchedule'])
        ->name('store_criteria_schedule');

    Route::post('/{id}/{criteria}', [ScheduleController::class, 'storeFinishedCriteriaSchedule'])
        ->name('store_finished_criteria_schedule');

    Route::get('/{id}/analysis', [ScheduleController::class, 'showScheduleAnalysisById'])
        ->name('schedule_analysis');
});
