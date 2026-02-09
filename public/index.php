<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

// --- เริ่มโค้ดดักจับ (Trap) ---
try {
    $logPath = __DIR__.'/../storage/logs/debug_ingress.log';
    $data = date('Y-m-d H:i:s') . " | " . ($_SERVER['REQUEST_URI']??'-') . " | " . ($_SERVER['REQUEST_METHOD']??'-') . "\n";
    file_put_contents($logPath, $data, FILE_APPEND);
} catch (\Exception $e) {}
// --- จบโค้ดดักจับ ---

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

// --- เริ่มการดัก Log ตรงนี้ ---
// ใช้ประโยคคำสั่ง PHP พื้นฐานดักไว้ก่อน เผื่อกรณี Laravel Boot ไม่ขึ้น
if (isset($_SERVER['REQUEST_URI'])) {
    // บันทึกลงไฟล์ log ของ laravel โดยตรง
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Incoming Request: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI'] . " from " . $_SERVER['REMOTE_ADDR'] . PHP_EOL;
    file_put_contents(__DIR__.'/../storage/logs/laravel.log', $logMessage, FILE_APPEND);
}

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
