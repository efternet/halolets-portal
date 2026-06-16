<?php

use App\Http\Controllers\Api\TaskReportingController;
use App\Http\Middleware\CheckEndpointActive;
use Illuminate\Support\Facades\Route;

Route::prefix('reports')
    ->middleware(['throttle:30,1', CheckEndpointActive::class])
    ->group(function () {
        Route::get('work-tasks/resolutions', [TaskReportingController::class, 'resolutionTypeSummary']);
    });
