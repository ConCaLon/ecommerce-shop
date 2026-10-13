# Xác thực email đăng ký tài khoản

Luồng sử dụng cơ chế xác thực email của Laravel 12:
https://laravel.com/docs/12.x/verification

1. Khách đăng ký tài khoản. Tài khoản được lưu với `status=unverified` và `email_verified_at=null`.
2. Hệ thống gửi email tiếng Việt qua SMTP và chuyển khách tới `/xac-thuc-email`.
3. Khách nhấn liên kết có chữ ký trong email. Liên kết có hiệu lực 60 phút theo mặc định.
4. Laravel kiểm tra chữ ký, thời hạn, tài khoản đang đăng nhập và địa chỉ email. Sau đó tài khoản chuyển sang `active` và lưu thời điểm xác thực.
5. Tài khoản chưa xác thực có thể xem sản phẩm và giỏ hàng, nhưng phải xác thực trước khi đặt hàng, truy cập hồ sơ hoặc trang quản trị.

Khi khách mở email trong trình duyệt khác, hệ thống yêu cầu đăng nhập bằng tài khoản vừa đăng ký rồi tiếp tục mở liên kết xác thực. Nếu liên kết hết hạn, trang xác thực cho phép gửi lại. Mỗi lần gửi cách nhau ít nhất 60 giây; thời hạn của liên kết mới được tính từ lần gửi mới.

Nếu SMTP gặp lỗi, tài khoản vẫn được giữ ở trạng thái chờ xác thực. Trang xác thực hiển thị thông báo lỗi và cho phép thử gửi lại sau một phút. Log chỉ ghi ID tài khoản và loại lỗi, không ghi mật khẩu SMTP hay nội dung lỗi chứa thông tin đăng nhập.

Tên người gửi, tiêu đề và nội dung email lấy tên cửa hàng từ mục cấu hình `site_name` trên website; nếu chưa có thì dùng `APP_NAME`. Địa chỉ gửi vẫn lấy từ `MAIL_FROM_ADDRESS` trong `.env`.

## Cấu hình `.env`

Cấu hình Gmail SMTP tương thích với phiên bản Laravel/Symfony Mailer đang cài:

```dotenv
APP_URL=http://127.0.0.1:8000
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-account@gmail.com
MAIL_PASSWORD="your-google-app-password"
MAIL_FROM_ADDRESS=your-account@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
MAIL_TIMEOUT=15
MAIL_REQUIRE_TLS=true
AUTH_VERIFICATION_EXPIRE=60
```

Các giá trị tài khoản và mật khẩu ở trên chỉ là ví dụ. Giữ thông tin gửi mail trong `.env`, không đưa vào Git. Với cổng 587, `MAIL_SCHEME=smtp` cùng `MAIL_REQUIRE_TLS=true` yêu cầu nâng kết nối lên TLS bằng STARTTLS. `MAIL_ENCRYPTION` không được cấu hình mail hiện tại sử dụng.

`APP_URL` phải trùng URL truy cập website. Khi đưa lên máy chủ, đổi thành URL HTTPS thực tế trước khi gửi email cho khách. Liên kết localhost chỉ dùng được trên máy đang chạy website.

Website hiện dùng `https://bloombold.shop` trên hosting cPanel. Xem [quy trình triển khai qua GitHub và Terminal](deploy-cpanel-bloombold.md) để cập nhật `.env` trên hosting sau khi kéo mã. Bản `.env` trên máy tính đã đổi `APP_URL` sang tên miền này; các thiết lập phát triển trên máy tính vẫn được giữ. Script cấu hình production chỉ chạy trên hosting khi triển khai.

Sau khi thay đổi cấu hình:

```powershell
php artisan config:clear
php artisan route:clear
```

Email xác thực được gửi đồng bộ nên không cần chạy queue worker cho chức năng này. Không cần migration mới vì bảng `users` đã có `email_verified_at` và `status`.

## Kiểm tra

```powershell
php vendor/bin/phpunit --filter 'AuthTest|EmailVerificationTest'
```

Kiểm thử dùng SQLite trong bộ nhớ và mail transport `array`, không gửi email cho khách hoặc thay đổi dữ liệu MySQL thật. Bộ kiểm thử bao gồm đăng ký, nội dung HTML/text của email, liên kết hợp lệ, hết hạn, bị sửa, sai tài khoản, tài khoản bị khóa, gửi lại, giới hạn gửi và lỗi SMTP.

Để kiểm tra hộp thư thật, chạy website tại URL trong `APP_URL`, đăng ký bằng một địa chỉ email bạn kiểm soát, mở thư xác thực rồi kiểm tra việc đặt hàng. Các kiểm thử tự động và đăng nhập SMTP không xác nhận được thư đã xuất hiện trong Inbox hay Spam.

Tài khoản đã được tự kích hoạt bởi mã đăng ký cũ giữ nguyên trạng thái. Liên kết dạng token cũ `/xac-thuc-email/{token}` được thay bằng liên kết có chữ ký; tài khoản chưa xác thực có thể đăng nhập và gửi lại email để nhận liên kết mới.
