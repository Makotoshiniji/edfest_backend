<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Registration;
use App\Models\Station; // ✅ เพิ่ม Import Station
use App\Models\Round;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // ✅ เพิ่ม Import DB

class AdminController extends Controller
{
    // 1. API สำหรับดึงสถิติรวม (Dashboard Stats)
    public function stats()
    {
        // 1.1 สถิติพื้นฐาน
        $totalUsers = User::count();
        $totalSelections = Registration::count();
        $fullSessions = 0; // (ถ้ามี Logic เช็คที่นั่งเต็ม ให้ใส่ตรงนี้)

        // 1.2 สถิติรายสาขา (Stations) - เรียงตามยอดจองมากไปน้อย
        $stations = Station::withCount('registrations')
            ->orderBy('registrations_count', 'desc')
            ->get();

        // 1.3 สถิติผู้สมัคร 7 วันล่าสุด (Daily Stats)
        // ใช้ DB::raw เพื่อจัดกลุ่มตามวัน (Date)
        $dailyStats = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'users' => $totalUsers,
            'selections' => $totalSelections,
            'fullSessions' => $fullSessions,
            'stations' => $stations,      // ✅ ส่งข้อมูลสาขา
            'dailyStats' => $dailyStats   // ✅ ส่งข้อมูลกราฟ
        ]);
    }

    // ... (ฟังก์ชัน getUsers, getUser เดิม คงไว้เหมือนเดิม) ...
    // 2. API สำหรับดึงรายชื่อผู้ใช้ (Users Table)
    public function getUsers(Request $request)
    {
        $limit = $request->query('limit', 10);
        $users = User::orderBy('created_at', 'desc')->paginate($limit);
        return response()->json($users);
    }

    // 3. API ดึงรายละเอียดผู้ใช้รายคน
    public function getUser($id)
    {
        $user = User::with(['registrations.round', 'registrations.station'])->find($id);
        if (!$user) return response()->json(['message' => 'ไม่พบข้อมูล'], 404);
        return response()->json($user);
    }
}