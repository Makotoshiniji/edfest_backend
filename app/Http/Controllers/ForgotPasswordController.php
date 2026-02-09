<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Mail\ResetPasswordOtp;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    // 1. ส่ง OTP ไปทางอีเมล
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // สุ่มเลข 6 หลัก
        $otp = rand(100000, 999999);

        // ลบ OTP เก่าของเมลนี้ทิ้งก่อน (ถ้ามี)
        DB::table('password_reset_otps')->where('email', $request->email)->delete();

        // บันทึก OTP ใหม่
        DB::table('password_reset_otps')->insert([
            'email' => $request->email,
            'otp' => $otp,
            'created_at' => Carbon::now()
        ]);

        // ส่งเมล (ใช้ Brevo SMTP)
        try {
            Mail::to($request->email)->send(new ResetPasswordOtp($otp));
            return response()->json(['message' => 'ส่งรหัส OTP ไปยังอีเมลเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'ไม่สามารถส่งอีเมลได้: ' . $e->getMessage()], 500);
        }
    }

    // 2. ตรวจสอบ OTP และ เปลี่ยนรหัสผ่านทันที
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric',
            'password' => 'required|min:8|confirmed'
        ]);

        // เช็ค OTP ใน Database
        $record = DB::table('password_reset_otps')
                    ->where('email', $request->email)
                    ->where('otp', $request->otp)
                    ->first();

        // ถ้าไม่เจอ หรือ OTP หมดอายุ (เกิน 15 นาที)
        if (!$record || Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            return response()->json(['message' => 'รหัส OTP ไม่ถูกต้องหรือหมดอายุ'], 400);
        }

        // อัปเดตรหัสผ่านใหม่
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // ลบ OTP ทิ้งเมื่อใช้เสร็จ
        DB::table('password_reset_otps')->where('email', $request->email)->delete();

        return response()->json(['message' => 'เปลี่ยนรหัสผ่านสำเร็จ! กรุณาเข้าสู่ระบบใหม่']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric'
        ]);

        $record = DB::table('password_reset_otps')
                    ->where('email', $request->email)
                    ->where('otp', $request->otp)
                    ->first();

        if (!$record || Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            return response()->json(['message' => 'รหัส OTP ไม่ถูกต้องหรือหมดอายุ'], 400);
        }

        return response()->json(['message' => 'OTP ถูกต้อง']);
    }
}