# 419 Saigon - Hidden Bar Website

Website cho hidden bar 419 Saigon kết hợp Cocktail & Cinema.

## Tính Năng

- **Dining**: Trải nghiệm ẩm thực truyền thống
- **Cocktail**: Quầy bar với những ly cocktail độc đáo
- **Cinema**: Phòng chiếu riêng tư với công nghệ hiện đại
- **Đặt bàn trực tuyến**: Hệ thống đặt bàn và phòng chiếu
- **Responsive Design**: Tương thích mọi thiết bị
- **Dark Luxury Theme**: Thiết kế sang trọng với tông màu tối

## Yêu Cầu Hệ Thống

- PHP 7.4 trở lên
- MySQL 5.7 trở lên hoặc MariaDB 10.2 trở lên
- Apache/Nginx web server
- XAMPP, WAMP, hoặc Laragon (cho development)

## Cài Đặt

### 1. Clone Repository
```bash
git clone [repository-url]
cd 419saigon
```

### 2. Cài Đặt Database
1. Tạo database mới trong phpMyAdmin hoặc MySQL command line
2. Import file `database/419saigon.sql`
3. Hoặc chạy lệnh:
```bash
mysql -u root -p < database/419saigon.sql
```

### 3. Cấu Hình Database
Chỉnh sửa file `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', '419saigon');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Cấu Hình Web Server
- Copy toàn bộ thư mục vào `htdocs` (XAMPP) hoặc `www` (WAMP)
- Truy cập: `http://localhost/419saigon`

### 5. Tài Khoản Admin Mặc Định
- Username: `admin`
- Password: `admin123`
- Email: `admin@419saigon.com`

## 📁 Cấu Trúc Thư Mục

```
419saigon/
├── index.php                 # Trang chủ
├── (izakaya page removed)
├── cocktail.php             # Trang Cocktail
├── cinema.php               # Trang Cinema
├── about.php                # Trang Giới thiệu
├── contact.php              # Trang Liên hệ
├── login.php                # Trang Đăng nhập
├── logout.php               # Trang Đăng xuất
├── forgot_password.php      # Trang Quên mật khẩu
├── checkout.php             # Trang Thanh toán
├── payment.php              # Trang Xử lý thanh toán
├── account.php              # Trang Tài khoản
├── search.php               # Trang Tìm kiếm
├── includes/
│   ├── header.php           # Header chung
│   ├── footer.php           # Footer chung
│   └── config.php           # Cấu hình database
├── assets/
│   ├── css/
│   │   └── style.css        # CSS chính
│   ├── js/
│   │   └── main.js          # JavaScript chính
│   └── images/              # Thư mục hình ảnh
├── database/
│   └── 419saigon.sql        # File SQL database
└── admin/                   # Thư mục Admin Panel
    ├── dashboard.php
    ├── (izakaya admin page removed)
    ├── cocktail.php
    ├── cinema.php
    ├── orders.php
    ├── contact.php
    ├── about.php
    ├── tags.php
    ├── settings.php
    ├── users.php
    └── assets/
        ├── css/
        └── js/
```

## Thiết Kế

### Color Palette
- **Black**: `#0a0a0a` - Màu nền chính
- **Dark Gray**: `#1a1a1a` - Màu nền phụ
- **Wood Brown**: `#8B4513` - Màu gỗ
- **Gold**: `#d4af37` - Màu chủ đạo
- **Cream**: `#f5f5f5` - Màu chữ

### Typography
- **Primary Font**: Inter (Sans-serif)
- **Heading Font**: Playfair Display (Serif)

### Features
- Smooth scrolling
- Parallax effects
- Hover animations
- Responsive design
- Dark luxury theme

## 🗄️ Database Schema

### Bảng Chính
- `users` - Quản lý người dùng
- `categories` - Danh mục món ăn/đồ uống/phim
- `menu_items` - Món ăn và đồ uống
- `movies` - Danh sách phim
- `cinema_rooms` - Phòng chiếu
- `bookings` - Đặt bàn/đặt phòng
- `orders` - Đơn hàng
- `contact_messages` - Tin nhắn liên hệ

### Quan Hệ
- Một booking có thể có nhiều order
- Một order có thể có nhiều order_items
- Một category có thể có nhiều menu_items

## Tính Năng Sắp Tới

- [ ] Hệ thống đăng nhập/đăng ký
- [ ] Admin panel hoàn chỉnh
- [ ] Hệ thống thanh toán online
- [ ] Email notifications
- [ ] Booking management
- [ ] Menu management
- [ ] User reviews
- [ ] Loyalty program

## 🔧 Development

### Thêm Trang Mới
1. Tạo file PHP mới trong thư mục gốc
2. Include header và footer
3. Thêm link vào navigation menu
4. Tạo CSS riêng nếu cần

### Thêm Tính Năng JavaScript
1. Thêm code vào `assets/js/main.js`
2. Hoặc tạo file JS riêng và include

### Customize CSS
1. Chỉnh sửa `assets/css/style.css`
2. Sử dụng CSS variables để thay đổi màu sắc
3. Responsive design với media queries

## 📱 Responsive Breakpoints

- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

## 🐛 Troubleshooting

### Lỗi Database Connection
- Kiểm tra thông tin database trong `config.php`
- Đảm bảo MySQL service đang chạy
- Kiểm tra quyền truy cập database

### Lỗi 404
- Kiểm tra cấu hình Apache/Nginx
- Đảm bảo file `.htaccess` tồn tại
- Kiểm tra đường dẫn file

### Lỗi CSS/JS không load
- Kiểm tra đường dẫn trong header.php
- Đảm bảo file tồn tại trong thư mục assets
- Kiểm tra quyền đọc file

## 📞 Hỗ Trợ

- Email: info@419saigon.com
- Phone: +84 28 1234 5678
- Website: https://419saigon.com

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Credits

- Design: 419 Saigon Team
- Development: [Your Name]
- Icons: Emoji Unicode
- Fonts: Google Fonts (Inter, Playfair Display)

---

**419 Saigon** - Cocktail & Cinema experiences in Saigon
