<?php

// namespace App\Http\Controllers;

// use App\Models\Station;
// use App\Models\Round;
// use Illuminate\Http\Request;

// class DataController extends Controller
// {
//     public function getInitialData()
//     {
//         $stations = Station::all();
//         $rounds = Round::all();

//         return response()->json([
//             'stations' => $stations,
//             'rounds' => $rounds
//         ]);
//     }

//     public function getRounds()
//     {
//         // ดึงรอบทั้งหมด เรียงตามเวลาเริ่ม
//         return response()->json(Round::orderBy('start_time')->get());
//     }
// }


namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Round;
use Illuminate\Http\Request;

class DataController extends Controller
{
    public function getInitialData()
    {
        $stations = Station::all();
        $rounds = Round::all();

        return response()->json([
            'stations' => $stations,
            'rounds' => $rounds
        ]);
    }

    // 🔥 เพิ่มฟังก์ชันนี้ต่อท้ายครับ
    public function getRounds()
    {
        // ดึงข้อมูลรอบทั้งหมด เรียงตามเวลา
        $rounds = Round::orderBy('start_time')->get();
        return response()->json($rounds);
    }
}