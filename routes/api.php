<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
 */

Route::group(['prefix' => 'v1', 'middleware' => 'throttle'], function () {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('signup', [AuthController::class, 'register']);

    //password reset
    Route::post('forgot-password', [AuthController::class, 'passwordReset']);
    Route::post('verify-token', [AuthController::class, 'token']);
    Route::post('password-reset', [AuthController::class, 'resetNow']);

    //webhook
    Route::post('flw-webhook', [PaymentController::class, 'flw']);
    Route::post('paystack-webhook', [PaymentController::class, 'paystack'])->middleware('paystack.ip');

    Route::group(['middleware' => 'auth:sanctum'], function () {

        //change password
        Route::post('change-password', [AuthController::class, 'password']);

        //update and view profile
        Route::apiresource('users', UserController::class)->except(['index', 'delete']);

        //terminate session
        Route::post('logout', [AuthController::class, 'logout']);

        Route::post('payments', [PaymentController::class, 'create']);
        Route::get('payments-history', [PaymentController::class, 'history']);
        Route::get('payments/{id}', [PaymentController::class, 'fetchPayment']);

        Route::group(['middleware' => ['admin']], function () {
            Route::post('make-admin', [AdminController::class, 'admin']);
            Route::post('revoke-admin', [AdminController::class, 'revoke']);
            Route::get('super-users', [AdminController::class, 'super_users']);
            Route::get('payments', [PaymentController::class, 'index']);

            Route::apiresource('appointments', AppointmentController::class)->except(['index', 'show']);
            Route::apiresource('users', UserController::class)->except(['show', 'update']);
        });

    });

});
