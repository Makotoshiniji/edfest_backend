<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Round; // ✅ อย่าลืม import Round
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    // บันทึกการลงทะเบียน (แบบทีละรายการ)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'station_id' => 'required|exists:stations,id',
            'round_id' => 'required|exists:rounds,id',
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($user, $validated) {
            
            // 1. ดึงข้อมูล Station เพื่อดูขีดจำกัดความจุ (Capacity)
            $station = \App\Models\Station::findOrFail($validated['station_id']);
            $limit = $station->capacity_limit ?? 40; // ถ้าไม่ได้ตั้งค่าให้รับได้ 40 คน

            // 2. 🔥 แก้ไขจุดนี้: นับจำนวนคนเฉพาะ "รอบนี้" และ "ฐานนี้"
            $currentCount = Registration::where('round_id', $validated['round_id'])
                                        ->where('station_id', $validated['station_id']) // สำคัญมาก! ต้องระบุฐาน
                                        ->lockForUpdate() // ล็อกเพื่อกันคนกดพร้อมกัน
                                        ->count();

            // 3. เช็คว่าเต็มหรือยัง
            if ($currentCount >= $limit) {
                return response()->json([
                    'message' => "ฐานกิจกรรม \"{$station->name}\" ในรอบนี้เต็มแล้วครับ ({$currentCount}/{$limit})"
                ], 400);
            }

            // 4. (Optional) เช็คว่า User เคยลงรอบนี้ไปแล้วหรือยัง (กันจองซ้ำรอบเวลาเดิม)
            $existing = Registration::where('user_id', $user->id)
                                    ->where('round_id', $validated['round_id'])
                                    ->exists();
            if ($existing) {
                return response()->json(['message' => 'คุณได้ลงทะเบียนในรอบเวลานี้ไปแล้ว (อาจจะเป็นฐานอื่น)'], 409);
            }

            // 5. บันทึกข้อมูล
            $registration = Registration::create([
                'user_id' => $user->id,
                'station_id' => $validated['station_id'],
                'round_id' => $validated['round_id'],
            ]);

            return response()->json([
                'message' => 'Registration successful',
                'data' => $registration
            ], 201);
        });
    }
    
    // ดึงประวัติการลงทะเบียนของ User
    public function myRegistrations(Request $request)
    {
        $user = $request->user();
        
        $registrations = Registration::where('user_id', $user->id)
                                    ->with(['station', 'round'])
                                    ->get();

        return response()->json($registrations);
    }

    // อัปเดตข้อมูลแบบ Bulk (ลบเก่า ใส่ใหม่)
    public function sync(Request $request)
    {
        $request->validate([
            'registrations' => 'present|array',
            'registrations.*.round_id' => 'required|exists:rounds,id',
            'registrations.*.station_id' => 'required|exists:stations,id',
        ]);

        $user = $request->user();
        $newRegistrations = $request->input('registrations');

        return DB::transaction(function () use ($user, $newRegistrations) {
            
            // 1. ตรวจสอบที่นั่งว่างของ "ทุกรายการ" ที่เลือกมา
            foreach ($newRegistrations as $reg) {
                // ดึงข้อมูลฐาน (Station) เพื่อดูลิมิตความจุ
                $station = \App\Models\Station::findOrFail($reg['station_id']);
                $limit = $station->capacity_limit ?? 40; // ถ้าไม่ตั้งค่า ให้ default 40

                // 🔥 แก้ตรงนี้: ต้องนับเฉพาะ Round นี้ AND Station นี้
                $currentCount = Registration::where('round_id', $reg['round_id'])
                                            ->where('station_id', $reg['station_id']) 
                                            ->where('user_id', '!=', $user->id) // ไม่นับตัวเอง (เผื่อกรณีอัปเดต)
                                            // ->whereIn('status', ['pending', 'confirmed']) // ถ้ามี status
                                            ->lockForUpdate() // ล็อกแถวเพื่อกันแย่งกันกด
                                            ->count();

                if ($currentCount >= $limit) {
                    $round = \App\Models\Round::find($reg['round_id']);
                    return response()->json([
                        'message' => "เสียใจด้วยครับ ฐาน \"{$station->name}\" ในรอบเวลา {$round->start_time} เต็มแล้ว ({$currentCount}/{$limit})"
                    ], 400);
                }
            }

            // 2. ถ้าทุกอย่างว่าง -> ลบข้อมูลเก่าทิ้ง
            Registration::where('user_id', $user->id)->delete();

            // 3. บันทึกข้อมูลใหม่
            foreach ($newRegistrations as $reg) {
                Registration::create([
                    'user_id' => $user->id,
                    'round_id' => $reg['round_id'],
                    'station_id' => $reg['station_id'],
                ]);
            }

            return response()->json(['message' => 'บันทึกข้อมูลสำเร็จ']);
        });
    }
}