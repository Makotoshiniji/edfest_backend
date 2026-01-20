<?php

namespace App\Http\Controllers;

use App\Models\AdminAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    // Login Admin
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = AdminAccount::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'], 401);
        }

        // สร้าง Token โดยระบุว่าเป็นของ 'admin'
        $token = $admin->createToken('admin-token', ['admin'])->plainTextToken;

        return response()->json([
            'message' => 'Login สำเร็จ',
            'token' => $token,
            'admin' => $admin
        ]);
    }

    // Get Admin Profile
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
    
    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'ออกจากระบบสำเร็จ']);
    }
    
    // (Optional) ฟังก์ชันสร้าง Admin คนแรก (เอาไว้รันผ่าน Postman ทีเดียวแล้วลบออก)
    // สมัคร Admin แบบกำหนดค่าเองได้
    public function register(Request $request) {
        // 1. เพิ่ม Validation เพื่อตรวจสอบข้อมูลที่ส่งมา
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:admin_accounts,email', // เช็คซ้ำในตาราง admin_accounts
            'password' => 'required|min:6'
        ]);

        // 2. สร้าง Admin โดยใช้ข้อมูลจาก Request
        $admin = AdminAccount::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);

        return response()->json([
            'message' => 'Admin registered successfully',
            'admin' => $admin
        ], 201);
    }
}