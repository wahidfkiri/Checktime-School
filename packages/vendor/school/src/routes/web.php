<?php

use Illuminate\Support\Facades\Route;
use Vendor\School\Controllers\ClassController;
use Vendor\School\Controllers\VacationScheduleController;
use Vendor\School\Controllers\PenaltyRuleController;
use Vendor\School\Controllers\VacationReportController;

Route::middleware(['web', 'auth', 'role:client', 'client.active'])->group(function () {

    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', [ClassController::class, 'index'])->name('index');
        Route::get('/local', [ClassController::class, 'getLocalClasses'])->name('local');
        Route::post('/', [ClassController::class, 'store'])->name('store');
        Route::put('/{id}', [ClassController::class, 'update'])->name('update');
        Route::delete('/{id}', [ClassController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('vacation-schedules')->name('vacation-schedules.')->group(function () {
        Route::get('/', [VacationScheduleController::class, 'index'])->name('index');
        Route::get('/local', [VacationScheduleController::class, 'getLocalSchedules'])->name('local');
        Route::post('/', [VacationScheduleController::class, 'store'])->name('store');
        Route::put('/{id}', [VacationScheduleController::class, 'update'])->name('update');
        Route::delete('/{id}', [VacationScheduleController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('penalty-rules')->name('penalty-rules.')->group(function () {
        Route::get('/', [PenaltyRuleController::class, 'index'])->name('index');
        Route::post('/', [PenaltyRuleController::class, 'update'])->name('update');
    });

    Route::prefix('vacation-reports')->name('vacation-reports.')->group(function () {
        Route::get('/', [VacationReportController::class, 'index'])->name('index');
        Route::get('/presence-pdf', [VacationReportController::class, 'presencePdf'])->name('presence-pdf');
        Route::get('/payment-pdf', [VacationReportController::class, 'paymentPdf'])->name('payment-pdf');
        Route::get('/attendance-summary-pdf', [VacationReportController::class, 'attendanceSummaryPdf'])->name('attendance-summary-pdf');
    });

});
