<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password OTP</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;700&display=swap');
        body { font-family: 'Prompt', Arial, sans-serif; margin: 0; padding: 0; background-color: #f3f4f6; }
    </style>
</head>
<body style="background-color: #f3f4f6; margin: 0; padding: 0; font-family: 'Prompt', Arial, sans-serif;">
    
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f3f4f6; padding: 40px 0;">
        <tr>
            <td align="center">
                
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    
                    <tr>
                        <td align="center" style="background-color: #ea580c; padding: 30px;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 1px;">EdFest KKU</h1>
                            <p style="color: #ffedd5; margin: 5px 0 0 0; font-size: 14px;">Education Open House</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px 40px 30px 40px;">
                            <h2 style="color: #1f2937; margin: 0 0 20px 0; font-size: 20px; font-weight: 600;">สวัสดีครับ/ค่ะ</h2>
                            <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
                                มีการร้องขอเพื่อตั้งรหัสผ่านใหม่สำหรับบัญชีของคุณบนระบบ EdFest KKU <br>
                                โปรดใช้รหัส OTP ด้านล่างนี้เพื่อดำเนินการต่อ:
                            </p>

                            <div style="background-color: #fff7ed; border: 2px dashed #fdba74; border-radius: 12px; padding: 25px; text-align: center; margin-bottom: 25px;">
                                <span style="display: block; color: #9a3412; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">รหัส OTP ของคุณ</span>
                                <span style="display: block; color: #ea580c; font-size: 32px; font-weight: 700; letter-spacing: 8px; font-family: monospace;">{{ $otp }}</span>
                            </div>

                            <p style="color: #6b7280; font-size: 14px; margin-bottom: 0;">
                                ⚠️ รหัสนี้จะหมดอายุภายใน <strong>15 นาที</strong>
                            </p>
                            <p style="color: #6b7280; font-size: 14px; margin-top: 5px;">
                                หากคุณไม่ได้เป็นผู้ร้องขอการเปลี่ยนรหัสผ่าน โปรดเพิกเฉยต่ออีเมลฉบับนี้ บัญชีของคุณยังคงปลอดภัย
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="background-color: #f9fafb; padding: 20px; border-top: 1px solid #e5e7eb;">
                            <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                                © 2026 Faculty of Education, Khon Kaen University.<br>
                                นี่เป็นข้อความอัตโนมัติ กรุณาอย่าตอบกลับ
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>