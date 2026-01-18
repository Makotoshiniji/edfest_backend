<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 1. สมัครสมาชิก
    public function register(Request $request)
    {
        // ตรวจสอบข้อมูลที่ส่งมา
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed', // ต้องมี password_confirmation ส่งมาด้วย
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|string',
            'school' => 'required|string',
            'grade_level' => 'required|string',
            'is_term_accepted' => 'accepted', // ต้องติ๊กถูกยอมรับเงื่อนไข
        ]);

        // บันทึกข้อมูลลง Database
        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'school' => $validated['school'],
            'grade_level' => $validated['grade_level'],
            'is_term_accepted' => true,
        ]);

        // สร้าง Token สำหรับ Login อัตโนมัติหลังสมัครเสร็จ
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // 2. เข้าสู่ระบบ
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
        
        // ลบ Token เก่าออกก่อน (Optional: เพื่อความปลอดภัย)
        $user->tokens()->delete();
        
        // สร้าง Token ใหม่
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    // 3. ออกจากระบบ
    public function logout(Request $request)
    {
        // ลบ Token ของ User ปัจจุบัน
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    // 4. ดึงข้อมูล User ปัจจุบัน (เอาไว้เช็คว่า Login อยู่ไหม)
    public function user(Request $request)
    {
        return $request->user();
    }
}