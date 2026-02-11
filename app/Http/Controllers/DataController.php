<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Round;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // ✅ อย่าลืม import DB

class DataController extends Controller
{
    public function getInitialData()
    {
        $stations = Station::all();
        $rounds = Round::orderBy('start_time')->get();

        // 🔥 เพิ่มส่วนนี้: นับจำนวนคนจอง แยกตาม (Round + Station)
        // เพื่อส่งไปให้หน้าเว็บคำนวณว่าเหลือที่ว่างเท่าไหร่
        $reserved_seats = DB::table('registrations')
            ->select('station_id', 'round_id', DB::raw('count(*) as count'))
            ->groupBy('station_id', 'round_id')
            ->get();

        return response()->json([
            'stations' => $stations,
            'rounds' => $rounds,
            'reserved_seats' => $reserved_seats // ✅ ส่งก้อนนี้ไปด้วย
        ]);
    }

    public function getRounds()
    {
        $rounds = Round::orderBy('start_time')->get();
        return response()->json($rounds);
    }
}