<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Models\Report;

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

    Route::get('/users/profile',[UserController::class,'getProfile']);
    Route::get('/users/my-subs',[UserController::class,'getMySubs']);
    Route::get('/users/my-supervisor',[UserController::class,'mySupervisor']);
    Route::get('/logout', [UserController::class, 'logout']);

    Route::get('/reports/supervisor',[ReportController::class,'mySubsReports']);

    Route::apiResources([
        'reports'=> ReportController::class,
    ]);
});

Route::post('/send-reset-otp',[OTPController::class,'sendResetOTP'])->middleware('throttle:3,1');
Route::post('/verify-reset-otp',[OTPController::class,'verifyResetOTP'])->middleware('throttle:3,1');
Route::post('/reset-password',[OTPController::class,'resetPassword']);

Route::get('/users/supervisors',[UserController::class,'allSupervisors']);
