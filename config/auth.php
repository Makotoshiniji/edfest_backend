<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | เราเพิ่ม 'api' (สำหรับนักเรียนใช้ Sanctum)
    | และ 'admin-api' (สำหรับแอดมินใช้ Sanctum แยกต่างหาก)
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // ✅ เพิ่ม Guard สำหรับ User (นักเรียน) ผ่าน API
        'api' => [
            'driver' => 'sanctum',
            'provider' => 'users',
            'hash' => false,
        ],

        // ✅ เพิ่ม Guard สำหรับ Admin ผ่าน API
        'admin-api' => [
            'driver' => 'sanctum',
            'provider' => 'admins',
            'hash' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | กำหนดว่า Guard แต่ละตัวจะไปดึงข้อมูล User จากตารางไหน/Model ไหน
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // ✅ เพิ่ม Provider สำหรับ Admin (ชี้ไปที่ Model AdminAccount)
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\AdminAccount::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
        
        // (Optional) เผื่อทำระบบลืมรหัสผ่านให้แอดมินด้วย
        'admins' => [
            'provider' => 'admins',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => 10800,

];