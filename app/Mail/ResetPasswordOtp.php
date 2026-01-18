<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordOtp extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->subject('รหัส OTP สำหรับตั้งรหัสผ่านใหม่ - EdFest KKU')
                    ->view('emails.otp'); // <-- เรียกใช้ไฟล์ view ที่สร้างไว้
    }
}