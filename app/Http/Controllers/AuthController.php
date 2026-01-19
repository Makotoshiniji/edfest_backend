<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;   // ✅ เพิ่ม
use Illuminate\Support\Facades\Mail; // ✅ เพิ่ม
use App\Mail\ResetPasswordOtp;       // ✅ เพิ่ม (ใช้ Class เดิมส่งเมล)

class AuthController extends Controller
{
    // 1. สมัครสมาชิก (แก้ไข Flow: สมัคร -> ส่ง OTP -> ยังไม่ Login)
    public function register(Request $request)
    {
        // ตรวจสอบข้อมูลที่ส่งมา
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

        // บันทึกข้อมูล User ลง Database (แต่ยังไม่มี Token)
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

        // --- เพิ่ม Logic OTP ---
        
        // 1. สร้าง OTP 6 หลัก
        $otp = rand(100000, 999999);

        // 2. บันทึกลงตาราง email_verification_otps
        DB::table('email_verification_otps')->updateOrInsert(
            ['email' => $user->email], // เงื่อนไข (ถ้ามี email นี้อยู่แล้วให้อัปเดต)
            ['otp' => $otp, 'created_at' => now()] // ข้อมูลที่จะบันทึก
        );

        // 3. ส่งอีเมล
        try {
            Mail::to($user->email)->send(new ResetPasswordOtp($otp));
        } catch (\Exception $e) {
            // กรณีส่งเมลไม่ผ่าน อาจจะ log error ไว้ แต่ยอมให้ process ผ่านไปก่อน
            // หรือจะ return error ก็ได้แล้วแต่ design
        }

        // 4. ส่ง Response กลับไป (สังเกตว่าไม่มี token)
        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
            'email' => $user->email
        ], 201);
    }

    // --- เพิ่มฟังก์ชันใหม่: ยืนยันอีเมล ---
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string'
        ]);

        // 1. ตรวจสอบว่ามี OTP นี้จริงไหม
        $record = DB::table('email_verification_otps')
                    ->where('email', $request->email)
                    ->where('otp', $request->otp)
                    ->first();

        if (!$record) {
            return response()->json(['message' => 'รหัส OTP ไม่ถูกต้อง'], 400);
        }

        // 2. ตรวจสอบว่าหมดอายุหรือยัง (ให้เวลา 15 นาที)
        if (now()->diffInMinutes($record->created_at) > 15) {
            return response()->json(['message' => 'รหัส OTP หมดอายุ กรุณาขอรหัสใหม่'], 400);
        }

        // 3. OTP ถูกต้อง -> อัปเดตสถานะ User
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->email_verified_at = now();
            $user->save();
        }

        // 4. ลบ OTP ทิ้ง
        DB::table('email_verification_otps')->where('email', $request->email)->delete();

        // 5. Login อัตโนมัติ (สร้าง Token)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully',
            'user' => $user,
            'token' => $token
        ]);
    }

    // --- เพิ่มฟังก์ชันใหม่: ขอ OTP ใหม่ ---
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

    // 2. เข้าสู่ระบบ (เหมือนเดิม)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();
        
        // (Optional check) ถ้าต้องการบังคับว่าต้อง Verify ก่อนถึงจะ Login ได้ ให้เปิดคอมเมนต์นี้
        if (!$user->email_verified_at) {
             return response()->json([
                 'message' => 'กรุณายืนยันอีเมลก่อนเข้าสู่ระบบ',
                 'email_not_verified' => true // ส่ง Flag ไปบอก Frontend เผื่อจะ Redirect ไปหน้ายืนยัน
             ], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    // 3. ออกจากระบบ (เหมือนเดิม)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    // 4. ดึงข้อมูล User ปัจจุบัน (เหมือนเดิม)
    public function user(Request $request)
    {
        return $request->user();
    }

    // 5. อัปเดตโปรไฟล์ (เหมือนเดิม)
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

        $user->update([
            'title' => $request->title,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'school' => $request->school,
            'grade_level' => $request->grade_level,
        ]);

        return response()->json([
            'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว',
            'user' => $user
        ]);
    }
}