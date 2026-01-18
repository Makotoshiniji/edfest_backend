<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ForgotPasswordController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 1. Public Routes (ใครก็เข้าได้ ไม่ต้อง Login)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/initial-data', [DataController::class, 'getInitialData']); // ดึงฐาน/รอบ
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);

// 2. Protected Routes (ต้อง Login ก่อนถึงจะเข้าได้)
Route::group(['middleware' => ['auth:sanctum']], function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']); // เช็คข้อมูลตัวเอง

    // Registration
    Route::post('/registrations', [RegistrationController::class, 'store']); // กดจอง
    Route::get('/my-registrations', [RegistrationController::class, 'myRegistrations']); // ดูประวัติการจอง
    Route::post('/registrations/sync', [RegistrationController::class, 'sync']);

    //edit profile
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
});