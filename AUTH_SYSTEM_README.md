# Hệ Thống Đăng Nhập - 419 Saigon

## Tổng Quan

Hệ thống đăng nhập đã được tích hợp vào website 419 Saigon với các tính năng sau:

### Tính Năng Chính

1. **Đăng Nhập/Đăng Ký**
   - Trang đăng nhập: `login.php`
   - Trang đăng ký: `login.php?action=register`
   - Trang quên mật khẩu: `login.php?action=forgot`

2. **Quản Lý Tài Khoản**
   - Trang tài khoản: `account.php`
   - Xem lịch sử đặt bàn: `my-bookings.php`
   - Đăng xuất: `logout.php`

3. **Tích Hợp Với Đặt Bàn**
   - Tự động điền thông tin khi đã đăng nhập
   - Liên kết đặt bàn với tài khoản người dùng

## Cấu Trúc Database

### Bảng Users
```sql
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20),
  `role` enum('user','admin') DEFAULT 'user',
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

### Bảng Bookings (Đã cập nhật)
```sql
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11), -- Liên kết với user
  `booking_type` enum('cinema','cocktail') NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `guests` int(11) NOT NULL,
  `special_requests` text,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `total_amount` decimal(10,2),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);
```

## Các File Đã Tạo/Cập Nhật

### Files Mới
- `login.php` - Trang đăng nhập/đăng ký/quên mật khẩu
- `logout.php` - Xử lý đăng xuất
- `account.php` - Trang quản lý tài khoản
- `my-bookings.php` - Trang xem lịch sử đặt bàn

### Files Đã Cập Nhật
- `includes/header.php` - Thêm dropdown menu cho user đã đăng nhập
- `includes/config.php` - Thêm các function helper
- `assets/css/style.css` - Thêm styles cho auth pages
-- `izakaya.php` - (removed)
- `cinema.php` - Tích hợp auto-fill thông tin user
- `process_booking.php` - Xử lý user_id khi đặt bàn

## Cách Sử Dụng

### 1. Đăng Ký Tài Khoản
- Truy cập: `http://localhost/419saigon/login.php?action=register`
- Điền đầy đủ thông tin bắt buộc
- Mật khẩu tối thiểu 6 ký tự

### 2. Đăng Nhập
- Truy cập: `http://localhost/419saigon/login.php`
- Sử dụng email và mật khẩu đã đăng ký

### 3. Quản Lý Tài Khoản
- Sau khi đăng nhập, click vào nút "Tài Khoản" trên header
- Chọn "Thông Tin Cá Nhân" để cập nhật thông tin
- Chọn "Đặt Bàn Của Tôi" để xem lịch sử đặt bàn

### 4. Đặt Bàn
- Khi đã đăng nhập, thông tin sẽ được tự động điền
- Đặt bàn sẽ được liên kết với tài khoản

## Tính Năng Bảo Mật

1. **Mã Hóa Mật Khẩu**
   - Sử dụng `password_hash()` với PASSWORD_DEFAULT
   - Mật khẩu được mã hóa trước khi lưu vào database

2. **Session Management**
   - Sử dụng PHP sessions để quản lý trạng thái đăng nhập
   - Session được destroy khi đăng xuất

3. **Input Sanitization**
   - Tất cả input được sanitize trước khi xử lý
   - Sử dụng prepared statements để tránh SQL injection

4. **Validation**
   - Email validation
   - Password strength validation
   - Required fields validation

## Tài Khoản Admin Mặc Định

Database đã có sẵn tài khoản admin:
- **Email**: admin@419saigon.com
- **Password**: admin123
- **Role**: admin

## Cấu Hình

### Database Configuration
Cập nhật thông tin database trong `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', '419saigon');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Website Configuration
```php
define('SITE_NAME', '419 Saigon');
define('SITE_URL', 'http://localhost/419saigon');
define('SITE_DESCRIPTION', 'A Cocktail & Cinema experience at 419 Saigon');
```

## Tính Năng Nâng Cao (Có Thể Phát Triển Thêm)

1. **Email Verification**
   - Gửi email xác nhận khi đăng ký
   - Xác thực email trước khi kích hoạt tài khoản

2. **Password Reset**
   - Tạo bảng `password_resets` để lưu token
   - Gửi email với link đặt lại mật khẩu

3. **Social Login**
   - Tích hợp đăng nhập qua Google, Facebook

4. **User Roles & Permissions**
   - Phân quyền chi tiết cho admin
   - Quản lý booking từ admin panel

5. **Booking Management**
   - Admin có thể xem và quản lý tất cả booking
   - Gửi email thông báo trạng thái booking

## Troubleshooting

### Lỗi Thường Gặp

1. **Không thể kết nối database**
   - Kiểm tra thông tin database trong `config.php`
   - Đảm bảo MySQL service đang chạy

2. **Session không hoạt động**
   - Kiểm tra `session_start()` trong `config.php`
   - Đảm bảo PHP có quyền ghi session

3. **Form không submit được**
   - Kiểm tra action URL trong form
   - Đảm bảo method="POST"

### Debug Mode
Để bật debug mode, thêm vào `config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Liên Hệ

Nếu có vấn đề hoặc cần hỗ trợ, vui lòng liên hệ:
- Email: admin@419saigon.com
- Website: http://localhost/419saigon
