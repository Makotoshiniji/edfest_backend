<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 1. Public Routes (ใครก็เข้าได้ ไม่ต้อง Login)
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/initial-data', [DataController::class, 'getInitialData']); // ดึงฐาน/รอบ
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
Route::get('/rounds', [DataController::class, 'getRounds']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/resend-verification-otp', [AuthController::class, 'resendVerificationOtp']);


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

// --- Admin Routes ---
Route::prefix('admin')->group(function () {
    // Public routes (ไม่ต้อง Login)
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/register', [AdminAuthController::class, 'register']); // สร้างเสร็จแล้วควรปิด Route นี้

    // Protected routes (ต้อง Login เป็น Admin เท่านั้น)
    Route::middleware(['auth:sanctum', 'ability:admin'])->group(function () {
        Route::get('/me', [AdminAuthController::class, 'me']);
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        
        Route::get('/dashboard-stats', [AdminController::class, 'stats']);
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/users/{id}', [AdminController::class, 'getUser']);
    });
});