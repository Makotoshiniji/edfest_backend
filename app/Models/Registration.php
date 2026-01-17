<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'station_id',
        'round_id',
    ];

    // ความสัมพันธ์: การลงทะเบียนนี้ เป็นของ User คนไหน
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ความสัมพันธ์: การลงทะเบียนนี้ เลือกฐานกิจกรรมไหน
    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    // ความสัมพันธ์: การลงทะเบียนนี้ เลือกรอบเวลาไหน
    public function round()
    {
        return $this->belongsTo(Round::class);
    }
}