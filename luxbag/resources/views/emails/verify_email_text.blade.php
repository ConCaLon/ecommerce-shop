Xin chào {{ $user->name }},

Cảm ơn bạn đã đăng ký tại {{ $appName }}.
Mở liên kết dưới đây để xác nhận email và kích hoạt tài khoản mua hàng:

{!! $verificationUrl !!}

Liên kết có hiệu lực trong {{ $expiresInMinutes }} phút.
Sau khi hết hạn, bạn có thể đăng nhập và chọn gửi lại email xác thực.
Nếu bạn không đăng ký tài khoản này, bạn có thể bỏ qua email.

Trân trọng,
Đội ngũ {{ $appName }}
