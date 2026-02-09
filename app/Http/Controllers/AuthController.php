<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordOtp;

class AuthController extends Controller
{
    // 1. สมัครสมาชิก (เหมือนเดิม)
    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'title' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|string',
            'school' => 'required|string',
            'grade_level' => 'required|string',
            'is_term_accepted' => 'accepted',
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'title' => $validated['title'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'school' => $validated['school'],
            'grade_level' => $validated['grade_level'],
            'is_term_accepted' => true,
        ]);

        $otp = rand(100000, 999999);
        DB::table('email_verification_otps')->updateOrInsert(
            ['email' => $user->email],
            ['otp' => $otp, 'created_at' => now()]
        );

        try {
            Mail::to($user->email)->send(new ResetPasswordOtp($otp));
        } catch (\Exception $e) {}

        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
            'email' => $user->email
        ], 201);
    }

    // 2. ยืนยันอีเมล (✅ แก้ไข: แจก Token ทันทีเมื่อยืนยันสำเร็จ)
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string'
        ]);

        $record = DB::table('email_verification_otps')
                    ->where('email', $request->email)
                    ->where('otp', $request->otp)
                    ->first();

        if (!$record) {
            return response()->json(['message' => 'รหัส OTP ไม่ถูกต้อง'], 400);
        }

        if (now()->diffInMinutes($record->created_at) > 15) {
            return response()->json(['message' => 'รหัส OTP หมดอายุ กรุณาขอรหัสใหม่'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->email_verified_at = now();
            $user->save();
        }

        DB::table('email_verification_otps')->where('email', $request->email)->delete();

        // 🔥 สร้าง Token ส่งกลับไปให้เลย (ไม่ต้อง Login ซ้ำ)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully',
            'user' => $user,
            'access_token' => $token, // ✅ ส่ง Token กลับไป
            'token_type' => 'Bearer',
        ]);
    }

    // 3. ขอ OTP ใหม่ (เหมือนเดิม)
    public function resendVerificationOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'ไม่พบอีเมลนี้ในระบบ'], 404);
        }

        $otp = rand(100000, 999999);
        DB::table('email_verification_otps')->updateOrInsert(
            ['email' => $request->email],
            ['otp' => $otp, 'created_at' => now()]
        );

        Mail::to($request->email)->send(new ResetPasswordOtp($otp));
        return response()->json(['message' => 'ส่งรหัส OTP ใหม่เรียบร้อยแล้ว']);
    }

    // 4. เข้าสู่ระบบ (✅ แก้ไข: เปลี่ยนจาก Session เป็น Token)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // ตรวจสอบรหัสผ่าน
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'], 401);
        }

        // ตรวจสอบยืนยันตัวตน
        if (! $user->email_verified_at) {
            return response()->json([
                'message' => 'กรุณายืนยันอีเมลก่อนเข้าสู่ระบบ',
                'email_not_verified' => true
            ], 403);
        }

        // 🔥 สร้าง Token (นี่คือหัวใจสำคัญของระบบไม่มี Cookie)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $token, // ✅ ส่ง Token กลับไป
            'token_type' => 'Bearer',
        ]);
    }

    // 5. ออกจากระบบ (✅ แก้ไข: ลบ Token ทิ้ง)
    public function logout(Request $request)
    {
        // ลบ Token ที่ใช้งานอยู่ปัจจุบันทิ้ง
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    // 6. ดึงข้อมูล User (เหมือนเดิม)
    public function user(Request $request)
    {
        return $request->user();
    }

    // 7. อัปเดตโปรไฟล์ (เหมือนเดิม)
    public function updateProfile(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:50',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'school' => 'required|string|max:255',
            'grade_level' => 'required|string|max:255',
        ]);

        $user = $request->user();

        $user->update($request->all()); // เขียนย่อได้ถ้า fillable ครบ

        return response()->json([
            'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว',
            'user' => $user
        ]);   
    }
}