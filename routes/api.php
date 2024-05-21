<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('/v1')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::middleware(['checkAdmin'])->group(function () {
            Route::post('/products', [\App\Http\Controllers\Api\ProductController::class, 'store']);
            Route::put('/products/{product}', [\App\Http\Controllers\Api\ProductController::class, 'update']);
            Route::delete('/products/bulk-delete', [\App\Http\Controllers\Api\ProductController::class, 'destroyMultiple']);
            Route::delete('/products/{product}', [\App\Http\Controllers\Api\ProductController::class, 'destroy']);
            Route::get('/products/{product}', [\App\Http\Controllers\Api\ProductController::class, 'show']);

            Route::get('/loyals', [\App\Http\Controllers\Api\LoyalController::class, 'index']);
            Route::post('/loyals', [\App\Http\Controllers\Api\LoyalController::class, 'store']);
            Route::put('/loyals/{loyal}', [\App\Http\Controllers\Api\LoyalController::class, 'update']);
            Route::delete('/loyals/{loyal}', [\App\Http\Controllers\Api\LoyalController::class, 'destroy']);
            Route::get('/loyals/{loyal}', [\App\Http\Controllers\Api\LoyalController::class, 'show']);
            Route::delete('/loyals/bulk-delete', [\App\Http\Controllers\Api\LoyalController::class, 'destroyMultiple']);

            Route::get('/bank-config', [\App\Http\Controllers\Api\BankConfigController::class, 'getBankConfig']);
            Route::post('/bank-config', [\App\Http\Controllers\Api\BankConfigController::class, 'storeBankConfig']);
            Route::get('/bank-config-test', [\App\Http\Controllers\Api\BankConfigController::class, 'testBankConfig']);

            Route::get('/staffs', [\App\Http\Controllers\Api\StaffController::class, 'index']);
            Route::post('/staffs', [\App\Http\Controllers\Api\StaffController::class, 'store']);
            Route::put('/staffs/{staff}', [\App\Http\Controllers\Api\StaffController::class, 'update']);
            Route::delete('/staffs/bulk-delete', [\App\Http\Controllers\Api\StaffController::class, 'destroyMultiple']);
            Route::delete('/staffs/{staff}', [\App\Http\Controllers\Api\StaffController::class, 'destroy']);
            Route::get('/staffs/{staff}', [\App\Http\Controllers\Api\StaffController::class, 'show']);

            Route::get('/invoices', [\App\Http\Controllers\Api\InvoiceController::class, 'index']);
            Route::put('/invoices/{invoice}', [\App\Http\Controllers\Api\InvoiceController::class, 'update']);
            Route::delete('/invoices/bulk-delete', [\App\Http\Controllers\Api\InvoiceController::class, 'destroyMultiple']);
            Route::delete('/invoices/{invoice}', [\App\Http\Controllers\Api\InvoiceController::class, 'destroy']);
            Route::get('/invoices/{invoice}', [\App\Http\Controllers\Api\InvoiceController::class, 'show']);

            Route::get('/vouchers', [\App\Http\Controllers\Api\VoucherController::class, 'index']);
            Route::post('/vouchers', [\App\Http\Controllers\Api\VoucherController::class, 'store']);
            Route::put('/vouchers/{voucher}', [\App\Http\Controllers\Api\VoucherController::class, 'update']);
            Route::delete('/vouchers/bulk-delete', [\App\Http\Controllers\Api\VoucherController::class, 'destroyMultiple']);
            Route::delete('/vouchers/{voucher}', [\App\Http\Controllers\Api\VoucherController::class, 'destroy']);
            Route::get('/vouchers/{voucher}', [\App\Http\Controllers\Api\VoucherController::class, 'show']);

            Route::get('/customers', [\App\Http\Controllers\Api\CustomerController::class, 'index']);
            Route::put('/customers/{customer}', [\App\Http\Controllers\Api\CustomerController::class, 'update']);
            Route::delete('/customers/bulk-delete', [\App\Http\Controllers\Api\CustomerController::class, 'destroyMultiple']);
            Route::delete('/customers/{customer}', [\App\Http\Controllers\Api\CustomerController::class, 'destroy']);
            Route::get('/customers/{customer}', [\App\Http\Controllers\Api\CustomerController::class, 'show']);

            Route::get('/dashboard/summary-statistic-today', [\App\Http\Controllers\Api\DashboardController::class, 'getSummaryStatisticToday']);
            Route::get('/dashboard/income-by-time', [\App\Http\Controllers\Api\DashboardController::class, 'getIncomeByTime']);
            Route::get('/dashboard/top-product-by-time', [\App\Http\Controllers\Api\DashboardController::class, 'getTopProductByTime']);
            Route::get('/dashboard/total-customer-by-time', [\App\Http\Controllers\Api\DashboardController::class, 'getTotalCustomerByTime']);
        });

        Route::post('/vouchers-verify', [\App\Http\Controllers\Api\VoucherVerifyController::class, '__invoke']);
        Route::post('/loyals-verify', [\App\Http\Controllers\Api\LoyalVerifyController::class, '__invoke']);
        Route::post('/invoices/get-qr', [\App\Http\Controllers\Api\InvoiceController::class, 'getQR']);
        Route::post('/invoices/check-bank', [\App\Http\Controllers\Api\InvoiceController::class, 'checkBank']);
        Route::post('/invoices', [\App\Http\Controllers\Api\InvoiceController::class, 'store']);
        Route::post('/invoices/get-total-price', [\App\Http\Controllers\Api\InvoiceController::class, 'getTotalCart']);
        Route::post('/customers', [\App\Http\Controllers\Api\CustomerController::class, 'store']);
        Route::get('/products', [\App\Http\Controllers\Api\ProductController::class, 'index']);
        Route::get('/invoices-pending', [\App\Http\Controllers\Api\InvoiceController::class, 'getPending']);
        Route::post('/invoices-finish/{invoice}', [\App\Http\Controllers\Api\InvoiceStatusController::class, 'finish']);
        Route::post('/invoices-undo/{invoice}', [\App\Http\Controllers\Api\InvoiceStatusController::class, 'undo']);

        Route::get('/profile', [UserController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});
