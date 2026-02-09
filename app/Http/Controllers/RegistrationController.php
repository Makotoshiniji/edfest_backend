<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'station_id' => 'required|exists:stations,id', // ต้องมี id ในตาราง stations จริง
            'round_id' => 'required|exists:rounds,id',     // ต้องมี id ในตาราง rounds จริง
        ]);

        // ดึง User ที่ Login อยู่ปัจจุบัน
        $user = $request->user();

        // ตรวจสอบว่าเคยลงทะเบียนฐานนี้หรือรอบนี้ไปหรือยัง (Optional)
        // ... (ใส่ Logic เพิ่มเติมได้ตรงนี้)

        $registration = Registration::create([
            'user_id' => $user->id,
            'station_id' => $validated['station_id'],
            'round_id' => $validated['round_id'],
        ]);

        return response()->json([
            'message' => 'Registration successful',
            'data' => $registration
        ], 201);
    }
    
    // ดึงประวัติการลงทะเบียนของ User
    public function myRegistrations(Request $request)
    {
        $user = $request->user();
        
        // ดึงข้อมูลพร้อมกับชื่อฐานและเวลารอบ (Eager Loading)
        $registrations = Registration::where('user_id', $user->id)
                            ->with(['station', 'round'])
                            ->get();

        return response()->json($registrations);
    }

    public function sync(Request $request)
    {
        $request->validate([
            'registrations' => 'present|array', // รับเป็น Array
            'registrations.*.round_id' => 'required|exists:rounds,id',
            'registrations.*.station_id' => 'required|exists:stations,id',
        ]);

        $user = $request->user();
        $newRegistrations = $request->input('registrations');

        // ใช้ Transaction เพื่อความปลอดภัย (ลบแล้วต้องบันทึกใหม่ได้ชัวร์ๆ)
        DB::transaction(function () use ($user, $newRegistrations) {
            // 1. ล้างข้อมูลการจองเก่าทั้งหมดของ User คนนี้ทิ้ง
            Registration::where('user_id', $user->id)->delete();

            // 2. บันทึกข้อมูลใหม่เข้าไป (ตามที่เลือกมาล่าสุด)
            foreach ($newRegistrations as $reg) {
                Registration::create([
                    'user_id' => $user->id,
                    'round_id' => $reg['round_id'],
                    'station_id' => $reg['station_id'],
                ]);
            }
        });

        return response()->json(['message' => 'บันทึกข้อมูลสำเร็จ']);
    }
}