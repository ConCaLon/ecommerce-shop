# 📖 Hướng Dẫn Sử Dụng — LuxBag E-Commerce

> Hệ thống thương mại điện tử bán túi xách cao cấp, xây dựng trên Laravel 12 + PHP 8.2 + MySQL + Bootstrap 5.

---

## 📋 Mục Lục

1. [Yêu Cầu Hệ Thống](#1-yêu-cầu-hệ-thống)
2. [Cài Đặt & Khởi Chạy](#2-cài-đặt--khởi-chạy)
3. [Cấu Hình .env](#3-cấu-hình-env)
4. [Tài Khoản Mặc Định](#4-tài-khoản-mặc-định)
5. [Hướng Dẫn Admin](#5-hướng-dẫn-admin)
6. [Hướng Dẫn Khách Hàng](#6-hướng-dẫn-khách-hàng)
7. [Quản Lý Đơn Hàng](#7-quản-lý-đơn-hàng)
8. [Câu Hỏi Thường Gặp](#8-câu-hỏi-thường-gặp)

---

## 1. Yêu Cầu Hệ Thống

| Thành phần | Phiên bản tối thiểu |
|-----------|-------------------|
| PHP | 8.2 trở lên |
| MySQL | 8.0 trở lên |
| Composer | 2.x |
| Node.js | 18.x (nếu build assets) |
| Web Server | Apache / Nginx / Laravel Herd |

> [!NOTE]
> Dự án đang được cấu hình để chạy tại `localhost/TMDT/luxbag/public` trên môi trường local (XAMPP/Laragon).

---

## 2. Cài Đặt & Khởi Chạy

### Lần đầu cài đặt

```bash
# 1. Di chuyển vào thư mục dự án
cd d:\Antigravity\TMDT\luxbag

# 2. Cài đặt dependencies PHP
composer install

# 3. Tạo file .env từ mẫu
copy .env.example .env

# 4. Tạo app key
php artisan key:generate

# 5. Chạy migration + seed dữ liệu mẫu
php artisan migrate --seed

# 6. Tạo symbolic link cho storage
php artisan storage:link

# 7. Tối ưu cache
php artisan optimize
```

### Khởi chạy hàng ngày

```bash
# Cách 1: Dùng PHP built-in server
php artisan serve

# Cách 2: Dùng XAMPP (đặt dự án trong htdocs)
# Truy cập: http://localhost/TMDT/luxbag/public
```

### Reset dữ liệu (khi cần)

```bash
php artisan migrate:fresh --seed
```

---

## 3. Cấu Hình .env

Mở file [.env](file:///d:/Antigravity/TMDT/luxbag/.env) và chỉnh các mục quan trọng:

### Database
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=luxbag
DB_USERNAME=root
DB_PASSWORD=
```

### Email (SMTP) — để gửi email xác nhận đơn hàng
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@luxbag.vn
MAIL_FROM_NAME="LuxBag"
```

### Google OAuth (tuỳ chọn)
```env
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost/TMDT/luxbag/public/auth/google/callback
```

> [!IMPORTANT]
> Sau khi sửa `.env`, luôn chạy `php artisan optimize:clear` để xóa cache cũ.

---

## 4. Tài Khoản Mặc Định

| Loại | Email | Mật khẩu | Quyền |
|------|-------|-----------|-------|
| Super Admin | `admin@luxbag.vn` | `Admin@123456` | Toàn quyền hệ thống |
| Khách hàng | `khachhang@example.com` | `Password@123` | Mua hàng, đánh giá |
Manager	manager@luxbag.vn	Manager@123
Staff	staff@luxbag.vn	Staff@123456

> [!WARNING]
> **Bắt buộc đổi mật khẩu admin ngay sau khi triển khai lên production!**

---

## 5. Hướng Dẫn Admin

Truy cập trang quản trị tại: `/admin` hoặc `/admin/dashboard`

### 5.1 Dashboard

Trang tổng quan hiển thị:
- **Doanh thu tháng** — tổng tiền từ đơn đã giao
- **Đơn chờ xác nhận** — số đơn cần xử lý ngay
- **Tổng khách hàng** — và số mới trong ngày
- **Sản phẩm sắp hết hàng** — cảnh báo tồn kho < 10
- **Biểu đồ doanh thu 30 ngày** (bar chart)
- **Biểu đồ trạng thái đơn hàng** (doughnut chart)
- **Sản phẩm bán chạy** & **Đơn hàng gần đây**

---

### 5.2 Quản Lý Sản Phẩm

**Đường dẫn:** `/admin/san-pham`

#### Thêm sản phẩm mới
1. Nhấn **"Thêm Sản Phẩm"** (nút vàng góc trên phải)
2. Điền thông tin:
   - **Tên sản phẩm** *(bắt buộc)*
   - **SKU** *(bắt buộc, duy nhất)*
   - **Danh mục** và **Thương hiệu**
   - **Giá gốc** (đồng)
   - **Giá sale** *(để trống nếu không giảm giá)*
   - **Tồn kho** ban đầu
   - **Mô tả ngắn** và **Mô tả đầy đủ** (hỗ trợ HTML)
   - **Tags** *(phân tách bằng dấu phẩy)*
3. Upload **ảnh sản phẩm** (hỗ trợ jpg/png/webp, tối đa 5MB/ảnh)
   - Hệ thống tự chuyển sang định dạng `.webp` để tối ưu
   - Tích vào ảnh để đặt làm **ảnh chính**
4. Thêm **biến thể** (tuỳ chọn): Size, Màu sắc, Tồn kho riêng
5. Nhấn **"Lưu Sản Phẩm"**

#### Chỉnh sửa sản phẩm
- Nhấn icon ✏️ trong danh sách, hoặc trực tiếp vào trang edit
- Upload thêm ảnh hoặc xóa ảnh cũ
- Thêm/xóa biến thể

#### Bật/tắt hiển thị
- Dùng **toggle switch** trong cột "Trạng thái" — không cần vào trang edit

#### Xóa hàng loạt
- Tick chọn nhiều sản phẩm → nhấn **"Xóa đã chọn"**

---

### 5.3 Quản Lý Danh Mục

**Đường dẫn:** `/admin/danh-muc`

- Form tạo mới ở cột trái, danh sách ở cột phải
- Hỗ trợ **danh mục cha/con** (2 cấp)
- Mỗi danh mục có: Tên, Slug, Ảnh, Mô tả, Thứ tự hiển thị, Trạng thái
- **Không thể xóa** danh mục đang có sản phẩm

---

### 5.4 Quản Lý Thương Hiệu

**Đường dẫn:** `/admin/thuong-hieu`

- Form tạo mới và danh sách trên cùng trang
- Nhấn icon ✏️ để mở modal chỉnh sửa
- Hỗ trợ upload logo thương hiệu

---

### 5.5 Quản Lý Đơn Hàng (Admin)

**Đường dẫn:** `/admin/don-hang`

#### Xem danh sách đơn hàng
- Lọc theo: Trạng thái, Phương thức thanh toán, Ngày tạo
- Tìm kiếm theo: Mã đơn, Tên khách, Số điện thoại

#### Cập nhật trạng thái đơn hàng
Luồng trạng thái hợp lệ:

```
Chờ xác nhận → Đã xác nhận → Đang xử lý → Đang giao → Đã giao
                    ↓               ↓
                 Hủy đơn        Hủy đơn
                                           ↓
                                       Hoàn tiền (từ Đã giao)
```

1. Mở chi tiết đơn hàng
2. Nhấn nút trạng thái tiếp theo (VD: "Xác nhận", "Đang giao"...)
3. Nếu hủy: điền lý do hủy vào modal rồi xác nhận

#### In hóa đơn
- Nhấn **"In hóa đơn"** → trang in chuyên dụng với nút Print

#### Xuất CSV
- Tại danh sách đơn hàng → **"Xuất CSV"** để tải về file Excel

---

### 5.6 Quản Lý Mã Giảm Giá

**Đường dẫn:** `/admin/ma-giam-gia`

#### Tạo mã giảm giá
| Trường | Mô tả |
|--------|-------|
| Mã code | VD: `SUMMER2026` (tự động chuyển hoa) |
| Loại | Phần trăm (%) hoặc Số tiền cố định (đ) |
| Giá trị | Con số tương ứng loại |
| Đơn tối thiểu | Giá trị đơn hàng tối thiểu để áp dụng |
| Giảm tối đa | Giới hạn số tiền giảm (chỉ với loại %) |
| Giới hạn tổng | Tổng số lần sử dụng toàn hệ thống |
| Giới hạn/người | Mỗi user dùng tối đa bao nhiêu lần |
| Ngày bắt đầu | Khi nào mã có hiệu lực |
| Ngày hết hạn | Để trống = không giới hạn |

---

### 5.7 Flash Sale

**Đường dẫn:** `/admin/flash-sale`

1. **Tạo Flash Sale**: Tên, thời gian bắt đầu/kết thúc
2. **Thêm sản phẩm**: Nhập ID sản phẩm + % giảm giá
3. Hệ thống tự tính giá sale = Giá gốc × (1 - %giảm/100)
4. Flash Sale hiển thị countdown timer trên trang chủ khi đang active

---

### 5.8 Banner Trang Chủ

**Đường dẫn:** `/admin/banner`

- Upload ảnh banner cho slider trang chủ
- Thiết lập: Tiêu đề, Phụ đề, Link khi click, Thứ tự hiển thị
- Hỗ trợ đặt lịch hiển thị theo thời gian

---

### 5.9 Quản Lý Đánh Giá

**Đường dẫn:** `/admin/danh-gia`

- Xem tất cả đánh giá, lọc theo trạng thái
- **Duyệt** hoặc **Từ chối** đánh giá
- **Phản hồi** đánh giá của khách (hiển thị ngay dưới review)
- Xóa đánh giá vi phạm

> [!NOTE]
> Hiện tại đánh giá được **tự động duyệt** khi khách gửi. Để chuyển sang chế độ kiểm duyệt thủ công, đổi `'status' => 'approved'` thành `'pending'` trong [ReviewController.php](file:///d:/Antigravity/TMDT/luxbag/app/Http/Controllers/ReviewController.php) dòng 50.

---

### 5.10 Quản Lý Khách Hàng

**Đường dẫn:** `/admin/khach-hang`

- Tìm kiếm theo tên, email, số điện thoại
- Xem lịch sử đơn hàng và tổng chi tiêu
- **Khóa/mở khóa** tài khoản
- **Đổi vai trò**: Customer → Staff → Manager → Admin

---

### 5.11 Báo Cáo

**Đường dẫn:** `/admin/bao-cao/doanh-thu`

| Trang | Nội dung |
|-------|---------|
| Doanh thu | Biểu đồ + bảng doanh thu theo ngày/tháng/năm |
| Sản phẩm | Top sản phẩm bán chạy nhất |
| Khách hàng | Top khách hàng chi tiêu nhiều nhất |
| Xuất CSV | Tải về file Excel báo cáo doanh thu |

---

### 5.12 Cài Đặt Hệ Thống

**Đường dẫn:** `/admin/cai-dat`

| Nhóm | Cài đặt |
|------|---------|
| Thông tin cửa hàng | Tên, tagline, địa chỉ, SĐT, email |
| Mạng xã hội | Facebook, Instagram, TikTok URL |
| Vận chuyển | Ngưỡng miễn phí vận chuyển (mặc định 500.000đ) |
| Thuế | Tỷ lệ VAT (%) |
| Email thông báo | Email admin nhận đơn, bật/tắt gửi email |

---

## 6. Hướng Dẫn Khách Hàng

### 6.1 Đăng Ký / Đăng Nhập

**Tại trang `/dang-ky`:**
- Điền: Họ tên, Email, Mật khẩu (tối thiểu 8 ký tự)
- Nhấn link xác thực email được gửi về hộp thư
- Hoặc đăng nhập nhanh bằng **Google**

**Tại trang `/dang-nhap`:**
- Đăng nhập bằng Email + Mật khẩu
- Tích "Nhớ đăng nhập" để giữ phiên

**Quên mật khẩu:** `/quen-mat-khau` → nhập email → nhận link reset

---

### 6.2 Tìm Kiếm & Lọc Sản Phẩm

**Trang sản phẩm** `/san-pham`:
- **Tìm kiếm** theo từ khoá (tên, mô tả, SKU)
- **Lọc** theo:
  - Danh mục (`?danh-muc=tui-xach-nu`)
  - Thương hiệu (`?thuong-hieu=gucci`)
  - Khoảng giá (`?gia-min=500000&gia-max=2000000`)
- **Sắp xếp**: Mới nhất, Giá tăng/giảm, Bán chạy nhất, Đánh giá cao

---

### 6.3 Xem Chi Tiết Sản Phẩm

- Gallery ảnh có thể click để xem ảnh lớn
- Chọn **Size** và **Màu sắc** (nếu có biến thể)
- Xem **đánh giá** từ người mua (chỉ những người đã mua mới được đánh giá)
- Nhấn ❤️ để thêm vào **Yêu thích**
- Nhấn **"Thêm vào giỏ"** hoặc **"Mua ngay"**

---

### 6.4 Giỏ Hàng

**Trang `/gio-hang`:**
- Thay đổi số lượng trực tiếp (có kiểm tra tồn kho)
- Xóa từng sản phẩm hoặc xóa toàn bộ
- Nhập **mã giảm giá** → nhấn "Áp dụng"
- Xem tóm tắt: Tạm tính, Phí vận chuyển, Giảm giá, **Tổng cộng**
- Nhấn **"Tiến Hành Đặt Hàng"**

> [!TIP]
> Giỏ hàng **không mất** khi tắt trình duyệt (lưu theo session). Sau khi đăng nhập, giỏ hàng khách sẽ tự **merge** vào tài khoản.

---

### 6.5 Đặt Hàng (Checkout)

**Trang `/thanh-toan`:**

**Bước 1 – Thông tin giao hàng:**
- Chọn địa chỉ đã lưu hoặc nhập địa chỉ mới
- Điền: Họ tên, SĐT, Email, Tỉnh/TP, Quận/Huyện, Phường/Xã, Địa chỉ cụ thể

**Bước 2 – Phương thức vận chuyển:**
- Hệ thống hiển thị các tùy chọn với phí vận chuyển tương ứng
- Đơn từ **500.000đ** được miễn phí vận chuyển (có thể thay đổi trong Settings)

**Bước 3 – Phương thức thanh toán:**
- 💵 **COD** — Thanh toán khi nhận hàng
- 🏦 **Chuyển khoản** — Chuyển khoản ngân hàng (hệ thống hiển thị thông tin TK)
- 💳 **VNPay** / **Momo** — Thanh toán online *(cần cấu hình API key)*

**Bước 4 – Xác nhận:**
- Kiểm tra lại danh sách sản phẩm, tổng tiền
- Nhập ghi chú (nếu có)
- Nhấn **"Đặt Hàng"** → nhận mã đơn hàng

---

### 6.6 Theo Dõi Đơn Hàng

**Tại `/tai-khoan` → Tab "Đơn hàng":**

| Trạng thái | Ý nghĩa |
|-----------|---------|
| 🟡 Chờ xác nhận | Đơn vừa đặt, chờ admin xử lý |
| 🔵 Đã xác nhận | Admin đã xác nhận, đang chuẩn bị hàng |
| 🟣 Đang xử lý | Đang đóng gói hàng |
| 🚚 Đang giao | Đã giao cho đơn vị vận chuyển |
| ✅ Đã giao | Giao thành công |
| ❌ Đã hủy | Đơn đã bị hủy (có lý do) |

> Khách có thể **hủy đơn** khi trạng thái còn là "Chờ xác nhận".

---

### 6.7 Đánh Giá Sản Phẩm

**Điều kiện:** Chỉ đánh giá được sản phẩm đã có đơn hàng **"Đã giao"**

1. Vào trang chi tiết sản phẩm
2. Cuộn xuống phần **"Đánh Giá Sản Phẩm"**
3. Chọn số sao (1-5), viết tiêu đề và nội dung
4. Nhấn **"Gửi Đánh Giá"**

---

### 6.8 Quản Lý Tài Khoản

**Trang `/tai-khoan`:**

| Tính năng | Mô tả |
|-----------|-------|
| Thông tin cá nhân | Tên, SĐT, Giới tính, Ngày sinh |
| Đổi mật khẩu | Cần nhập mật khẩu hiện tại |
| Ảnh đại diện | Upload ảnh (tự crop 200×200, chuyển webp) |
| Địa chỉ giao hàng | Thêm nhiều địa chỉ, đặt mặc định |
| Danh sách yêu thích | `/yeu-thich` — xem và quản lý |
| Lịch sử đơn hàng | Xem tất cả đơn, chi tiết từng đơn |

---

## 7. Quản Lý Đơn Hàng

### Luồng xử lý đơn hàng chuẩn

```
Khách đặt hàng
      ↓
[pending] Chờ xác nhận
      ↓ Admin nhấn "Xác nhận"
[confirmed] Đã xác nhận
      ↓ Admin nhấn "Đang xử lý"
[processing] Đang xử lý / Đóng gói
      ↓ Admin nhấn "Đang giao"
[shipping] Đã bàn giao vận chuyển
      ↓ Admin nhấn "Đã giao"
[delivered] ✅ Hoàn tất
```

### Các lệnh artisan hữu ích

```bash
# Xem log ứng dụng
php artisan pail

# Xóa cache khi có lỗi lạ
php artisan optimize:clear

# Chạy seeder lại (xóa dữ liệu cũ!)
php artisan migrate:fresh --seed

# Kiểm tra trạng thái migrate
php artisan migrate:status

# Xem tất cả routes
php artisan route:list
```

---

## 8. Câu Hỏi Thường Gặp

**Q: Ảnh sản phẩm không hiển thị?**
> Chạy `php artisan storage:link` để tạo symbolic link.

**Q: Không gửi được email reset mật khẩu?**
> Kiểm tra cấu hình MAIL_* trong `.env`. Với Gmail, cần bật "App Password" (không dùng mật khẩu tài khoản thật).

**Q: Đăng nhập Google không hoạt động?**
> Cần thêm `GOOGLE_CLIENT_ID` và `GOOGLE_CLIENT_SECRET` vào `.env`. Tạo tại [Google Cloud Console](https://console.cloud.google.com/).

**Q: Trang quản trị báo lỗi 403?**
> Tài khoản đăng nhập không có role `admin` hoặc `super-admin`. Chạy:
> ```bash
> php artisan tinker --execute="App\Models\User::where('email','your@email.com')->first()->assignRole('super-admin');"
> ```

**Q: Sản phẩm không hiện trên trang chủ?**
> Kiểm tra: (1) Trạng thái sản phẩm là "Đang bán", (2) Tồn kho > 0, (3) Tick "Nổi bật" để hiện trong Featured.

**Q: Pagination không hiện?**
> Đã cấu hình Bootstrap 5 pagination trong `AppServiceProvider`. Nếu vẫn lỗi, chạy `php artisan optimize:clear`.

**Q: Muốn thêm phương thức thanh toán online (VNPay/Momo)?**
> Cần đăng ký tài khoản merchant, lấy API key, sau đó tích hợp vào `OrderController::store()`.

---

## 📞 Thông Tin Kỹ Thuật

| Mục | Chi tiết |
|-----|---------|
| Framework | Laravel 12.62.0 |
| PHP | 8.2.12 |
| Database | MySQL (InnoDB) |
| Authentication | Custom (Session) + Spatie Permission |
| Image Processing | Intervention Image (→ WebP) |
| CSS Framework | Bootstrap 5 + Custom CSS |
| Icons | Font Awesome 6 |
| Charts | Chart.js |
| Queue | Sync (đổi sang Database/Redis cho production) |
| Storage | Local (public disk) |

---

*Tài liệu cập nhật: 13/07/2026 — LuxBag v1.0*
