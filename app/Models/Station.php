<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'room',
        'capacity_limit',
    ];

    // ความสัมพันธ์: Station หนึ่งอัน มีคนลงทะเบียนได้หลายคน
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
}