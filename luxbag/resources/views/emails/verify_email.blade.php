<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xác thực tài khoản {{ $appName }}</title>
</head>
<body style="margin:0;padding:24px 12px;background:#f4f1f8;font-family:Arial,sans-serif;color:#30283e;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr><td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:16px;">
                <tr><td style="padding:32px;">
                    <p style="margin:0 0 24px;font-size:24px;font-weight:bold;color:#8060b6;">{{ $appName }}</p>
                    <h1 style="font-size:22px;margin:0 0 20px;">Xác thực tài khoản mua hàng</h1>
                    <p style="font-size:15px;line-height:1.7;">Xin chào {{ $user->name }},</p>
                    <p style="font-size:15px;line-height:1.7;">Cảm ơn bạn đã đăng ký tại {{ $appName }}. Nhấn nút bên dưới để xác nhận địa chỉ email và kích hoạt tài khoản của bạn.</p>
                    <p style="margin:28px 0;text-align:center;">
                        <a href="{{ $verificationUrl }}" style="display:inline-block;padding:15px 24px;background:#8060b6;color:#ffffff;text-decoration:none;border-radius:8px;font-size:16px;font-weight:bold;">Xác thực tài khoản</a>
                    </p>
                    <p style="font-size:14px;line-height:1.7;">Liên kết này có hiệu lực trong <strong>{{ $expiresInMinutes }} phút</strong>. Sau khi hết hạn, bạn có thể đăng nhập và chọn gửi lại email xác thực.</p>
                    <p style="font-size:14px;line-height:1.7;">Nếu nút không hoạt động, hãy sao chép liên kết dưới đây vào trình duyệt:</p>
                    <p style="font-size:12px;line-height:1.7;overflow-wrap:anywhere;word-break:break-all;"><a href="{{ $verificationUrl }}" style="color:#8060b6;">{{ $verificationUrl }}</a></p>
                    <p style="font-size:13px;line-height:1.7;color:#71677e;">Nếu bạn không đăng ký tài khoản này, bạn có thể bỏ qua email.</p>
                    <p style="margin-top:24px;font-size:14px;">Trân trọng,<br>Đội ngũ {{ $appName }}</p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
