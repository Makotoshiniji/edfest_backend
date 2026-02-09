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
    
// แก้ไขฟังก์ชัน register ให้รับค่าจาก Postman
    public function register(Request $request) {
        // Validate ข้อมูล
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:admin_accounts', // ห้ามซ้ำ
            'password' => 'required|string|min:6',
        ]);

        // สร้าง Admin จากข้อมูลที่ส่งมา
        $admin = AdminAccount::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'สร้าง Admin สำเร็จ',
            'data' => $admin
        ], 201);
    }
}