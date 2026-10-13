# Cập nhật xác thực email lên bloombold.shop qua GitHub và cPanel

Tên miền sử dụng: **https://bloombold.shop**.

DNS hiện trỏ `bloombold.shop` tới `103.18.6.91`; `www.bloombold.shop` là CNAME của tên miền gốc. Kiểm tra ngày 13/10/2026 cho thấy cả hai địa chỉ HTTPS đều truy cập được và chứng chỉ TLS hợp lệ. Không cần thay bản ghi DNS để đưa cập nhật này lên website.

## 1. Đưa mã từ máy tính lên GitHub

Repository của dự án: `https://github.com/ConCaLon/ecommerce-shop.git`, nhánh `main`.

Trong GitHub Desktop hoặc công cụ Git bạn đang dùng, commit và push các thay đổi trong thư mục `luxbag`, bao gồm các tệp mới trong `app/Notifications`, `resources/views/emails`, `scripts`, `docs` và `tests`.

`.env` chứa thông tin đăng nhập và đã được Git bỏ qua. Push/pull sẽ không mang cấu hình `.env` từ máy tính lên hosting. Giữ `APP_KEY`, cấu hình MySQL và thông tin gửi mail hiện có trên hosting.

## 2. Kéo mã trong Terminal của cPanel

Mở cPanel → Terminal, chuyển tới thư mục clone của repository và kiểm tra:

```bash
pwd
git status --short
git branch --show-current
```

Nếu hosting đang có thay đổi chưa commit trong các tệp được Git theo dõi, xử lý chúng trước khi kéo mã. Sau đó chạy:

```bash
git pull --ff-only origin main
```

Chuyển tới thư mục Laravel có tệp `artisan`. Với cấu trúc giống repository trên máy tính, đây là thư mục `luxbag` trong repository:

```bash
cd luxbag
php -v
composer install --no-dev --prefer-dist --optimize-autoloader
```

Nếu bạn đã đang ở thư mục có `artisan`, bỏ qua lệnh `cd luxbag`. PHP dùng trong Terminal cần từ 8.2 trở lên, tương ứng với phiên bản website trong MultiPHP Manager. Nếu hosting dùng đường dẫn PHP/Composer riêng, dùng lệnh do nhà cung cấp hosting cấp.

## 3. Cập nhật tên miền trong `.env` của hosting

Chạy thử để xem đúng các thiết lập sẽ được cập nhật, rồi áp dụng:

```bash
php scripts/configure-bloombold-domain.php --dry-run
php scripts/configure-bloombold-domain.php
```

Script chỉ cập nhật các khóa sau, giữ nguyên cấu hình MySQL, `APP_KEY`, mật khẩu SMTP và các thiết lập khác:

```dotenv
APP_URL=https://bloombold.shop
APP_ENV=production
APP_DEBUG=false
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
AUTH_VERIFICATION_EXPIRE=60
```

`SESSION_DOMAIN=null` dùng cookie theo tên miền khách đang truy cập. Nếu khách mở thư trong trình duyệt hoặc tên miền khác (`www` và không `www`), họ có thể cần đăng nhập lại; hệ thống tiếp tục mở liên kết xác thực sau khi đăng nhập.

Script không thay máy chủ gửi mail. Kiểm tra SMTP trong `.env` của hosting. Nếu dùng tài khoản Gmail đã cấu hình trong dự án, giữ thông tin đăng nhập thực tế và bảo đảm các thiết lập sau:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_TIMEOUT=15
MAIL_REQUIRE_TLS=true
```

`MAIL_USERNAME`, `MAIL_PASSWORD` và `MAIL_FROM_ADDRESS` phải có giá trị thật trên hosting. Nếu dùng mail của hosting hoặc nhà cung cấp khác, giữ host, port, scheme và thông tin đăng nhập tương ứng của nhà cung cấp đó. Kết quả kiểm tra Gmail SMTP trên máy tính không xác nhận được hosting có cho phép kết nối SMTP ra ngoài.

## 4. Làm mới cache và xác nhận cấu hình

Trong thư mục có `artisan`, chạy:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:list --path=xac-thuc
php artisan tinker --execute="dump(config('app.url'), config('app.env'), config('app.debug'), route('verification.notice'));"
```

Kết quả cuối cần có URL `https://bloombold.shop`, môi trường `production`, debug `false` và URL trang xác thực `https://bloombold.shop/xac-thuc-email`.

Bản cập nhật xác thực email không thêm migration và không cần queue worker. Nếu PHP OPcache trên hosting không kiểm tra thời gian sửa tệp, dùng chức năng reload PHP/PHP-FPM của hosting hoặc yêu cầu nhà cung cấp reload sau khi cập nhật.

## 5. Kiểm tra trên website thật

1. Mở `https://bloombold.shop/dang-ky` và đăng ký bằng email bạn kiểm soát.
2. Website phải chuyển tới trang chờ xác thực. Trước khi xác thực, khách không thể đặt hàng.
3. Kiểm tra Inbox và Spam, mở thư có tên cửa hàng Bloom & Bold và nhấn nút xác thực.
4. Liên kết phải dùng HTTPS trên `bloombold.shop` hoặc `www.bloombold.shop`. Sau khi xác thực, tài khoản được kích hoạt và có thể đặt hàng.
5. Nếu chưa nhận được thư, thử gửi lại sau một phút và kiểm tra SMTP của hosting.

Không chạy `php artisan key:generate` cho website đang hoạt động. `APP_KEY` hiện có được dùng để kiểm tra chữ ký liên kết, mã hóa cookie và các dữ liệu liên quan.

## Tài liệu chính thức

- [Laravel 12: deployment, PHP và cấu hình cache](https://laravel.com/docs/12.x/deployment).
- [Laravel 12: xác thực email và liên kết có chữ ký](https://laravel.com/docs/12.x/verification).
- [cPanel: quản lý tên miền và chuyển hướng HTTPS](https://docs.cpanel.net/cpanel/domains/domains/).
