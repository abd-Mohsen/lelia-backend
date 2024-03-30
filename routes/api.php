<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\UserController;

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

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('verified')->group(function () {
        //
    });
    Route::get('/send-register-otp', [OTPController::class,'sendRegisterOTP'])->middleware(['throttle:3,1']);
    Route::post('/verify-register-otp', [OTPController::class,'verifyRegisterOTP'])
                                        ->middleware(['signed','throttle:3,1'])
                                        ->name('verification.otp');
});

Route::post('/send-reset-otp',[OTPController::class,'sendResetOTP'])->middleware('throttle:3,1');
Route::post('/verify-reset-otp',[OTPController::class,'verifyResetOTP'])->middleware('throttle:3,1');
Route::post('/reset-password',[OTPController::class,'resetPassword']);
