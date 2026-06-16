<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Portal\AbTestController;
use App\Http\Controllers\Portal\BusinessController;
use App\Http\Controllers\Portal\BusinessCustomerController;
use App\Http\Controllers\Portal\Billing\Processing\BillingController;
use App\Http\Controllers\Portal\Billing\Processing\FranchisePaymentController;
use App\Http\Controllers\Portal\Billing\Processing\VendorDeficitController;
use App\Http\Controllers\Portal\CustomerController;
use App\Http\Controllers\Portal\ProductCategoryController;
use App\Http\Controllers\Portal\ProductListController;
use App\Http\Controllers\Portal\EndpointController;
use App\Http\Controllers\Portal\Operations\CallController;
use App\Http\Controllers\Portal\Operations\ResolutionTypeController;
use App\Http\Controllers\Portal\Operations\WorkTaskController;
use App\Http\Controllers\Portal\Reports\ReportsController;
use App\Http\Controllers\Portal\Reports\WebDataController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('portal')->name('portal.')->middleware('auth')->group(function () {

    Route::prefix('billing')->name('billing.')->group(function () {
        Route::prefix('processing')->name('processing.')->group(function () {

            Route::prefix('payments')->name('payments.')->group(function () {
                Route::get('/', [BillingController::class, 'index'])->name('index');
                Route::put('update', [BillingController::class, 'update'])->name('update');
            });

            Route::prefix('vendor-deficits')->name('vendor-deficits.')->group(function () {
                Route::get('/', [VendorDeficitController::class, 'index'])->name('index');
                Route::put('update', [VendorDeficitController::class, 'update'])->name('update');
            });

            Route::prefix('franchise-payments')->name('franchise-payments.')->group(function () {
                Route::get('/', [FranchisePaymentController::class, 'index'])->name('index');
                Route::put('update', [FranchisePaymentController::class, 'update'])->name('update');
            });

        });
    });

    Route::prefix('endpoints')->name('endpoints.')->group(function () {
        Route::get('/', [EndpointController::class, 'index'])->name('index');
        Route::post('toggle', [EndpointController::class, 'toggle'])->name('toggle');
    });

    Route::prefix('product-categories')->name('product-categories.')->group(function () {
        Route::get('/',        [ProductCategoryController::class, 'index'])->name('index');
        Route::post('store',   [ProductCategoryController::class, 'store'])->name('store');
        Route::delete('{id}',  [ProductCategoryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('product-list')->name('product-list.')->group(function () {
        Route::get('/',       [ProductListController::class, 'index'])->name('index');
        Route::post('store',  [ProductListController::class, 'store'])->name('store');
        Route::put('update',  [ProductListController::class, 'update'])->name('update');
    });

    Route::prefix('businesses')->name('businesses.')->group(function () {
        Route::get('/',        [BusinessController::class, 'index'])->name('index');
        Route::put('{id}',     [BusinessController::class, 'update'])->name('update');
    });

    Route::prefix('business-customers')->name('business-customers.')->group(function () {
        Route::get('/',        [BusinessCustomerController::class, 'index'])->name('index');
        Route::put('{id}',     [BusinessCustomerController::class, 'update'])->name('update');
    });

    Route::prefix('ab-tests')->name('ab-tests.')->group(function () {
        Route::get('/', [AbTestController::class, 'index'])->name('index');
    });

    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/',       [CustomerController::class, 'index'])->name('index');
        Route::put('update',  [CustomerController::class, 'update'])->name('update');
    });

    Route::prefix('operations')->name('operations.')->group(function () {

        Route::prefix('resolution-types')->name('resolution-types.')->group(function () {
            Route::get('/',         [ResolutionTypeController::class, 'index'])->name('index');
            Route::post('store',    [ResolutionTypeController::class, 'store'])->name('store');
            Route::put('update',    [ResolutionTypeController::class, 'update'])->name('update');
            Route::delete('{id}',   [ResolutionTypeController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('calls')->name('calls.')->group(function () {
            Route::get('/',        [CallController::class, 'index'])->name('index');
            Route::get('search',   [CallController::class, 'search'])->name('search');
            Route::post('store',   [CallController::class, 'store'])->name('store');
            Route::put('update',   [CallController::class, 'update'])->name('update');
        });

        Route::prefix('work-tasks')->name('work-tasks.')->group(function () {
            Route::get('/',            [WorkTaskController::class, 'index'])->name('index');
            Route::post('store',       [WorkTaskController::class, 'store'])->name('store');
            Route::put('update',       [WorkTaskController::class, 'update'])->name('update');
            Route::delete('{id}',      [WorkTaskController::class, 'destroy'])->name('destroy');
        });

    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('franchise-dispatch-reports', [ReportsController::class, 'franchiseDispatchReports'])->name('franchise-dispatch-reports');
        Route::get('franchise-reports',          [ReportsController::class, 'franchiseReports'])->name('franchise-reports');
        Route::get('vendor-reports',             [ReportsController::class, 'vendorReports'])->name('vendor-reports');
        Route::get('vendor-sales',               [ReportsController::class, 'vendorSales'])->name('vendor-sales');
        Route::get('web-sales',                  [WebDataController::class, 'webSales'])->name('web-sales');
        Route::get('web-sales-search-queries',   [WebDataController::class, 'webSalesSearchQueries'])->name('web-sales-search-queries');
        Route::get('web-search-failures',        [WebDataController::class, 'webSearchFailures'])->name('web-search-failures');
    });

});
