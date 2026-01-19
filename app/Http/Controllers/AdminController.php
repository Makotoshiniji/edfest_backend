<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Registration;
use App\Models\Round;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // 1. API สำหรับดึงสถิติรวม (Dashboard Stats)
    public function stats()
    {
        // นับจำนวน User ทั้งหมด
        $totalUsers = User::count();

        // นับจำนวนการจองทั้งหมด (Selections)
        $totalSelections = Registration::count();

        // นับจำนวนรอบที่เต็มแล้ว (คำนวณจาก Round ที่มีคนจอง >= seats)
        // สมมติว่าในตาราง rounds มี column 'seats' (จำนวนที่นั่งรับได้)
        // แต่ถ้ายังไม่มี logic ตัดยอดที่นั่ง ให้ mock ค่าไปก่อน หรือนับแบบคร่าวๆ
        $fullSessions = 0; 
        
        // (Optional Logic: ถ้านับจริงต้อง Loop เช็คแต่ละรอบ)
        // $rounds = Round::withCount('registrations')->get();
        // $fullSessions = $rounds->filter(function($round) {
        //     return $round->registrations_count >= $round->seats;
        // })->count();

        return response()->json([
            'users' => $totalUsers,
            'selections' => $totalSelections,
            'fullSessions' => $fullSessions
        ]);
    }

    // 2. API สำหรับดึงรายชื่อผู้ใช้ (Users Table)
    public function getUsers(Request $request)
    {
        $limit = $request->query('limit', 10); // รับค่า limit จาก query param (default 10)

        $users = User::orderBy('created_at', 'desc')
                     ->paginate($limit);

        return response()->json($users);
    }

    // 3. API ดึงรายละเอียดผู้ใช้รายคน (User Detail)
    public function getUser($id)
    {
        // ค้นหา User พร้อมโหลดข้อมูลการลงทะเบียน (registrations)
        // และโหลดข้อมูล round, station ของการลงทะเบียนนั้นๆ มาด้วย
        $user = User::with(['registrations.round', 'registrations.station'])
                    ->find($id);

        if (!$user) {
            return response()->json(['message' => 'ไม่พบข้อมูลผู้ใช้งาน'], 404);
        }

        return response()->json($user);
    }
}