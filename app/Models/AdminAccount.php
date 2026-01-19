<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // เปลี่ยนจาก Model ธรรมดา
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // เพิ่ม Sanctum

class AdminAccount extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}