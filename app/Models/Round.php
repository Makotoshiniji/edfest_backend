<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
    ];

    // ความสัมพันธ์: Round หนึ่งรอบ มีคนลงทะเบียนได้หลายคน
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
}