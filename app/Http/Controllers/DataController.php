<?php

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
}