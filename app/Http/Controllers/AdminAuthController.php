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
    public function register(Request $request) {
        $admin = AdminAccount::create([
            'name' => 'Super Admin',
            'email' => 'admin@edfest.com',
            'password' => Hash::make('password1234')
        ]);
        return response()->json($admin);
    }
}