# Cocktail Improvements - Tính năng mới cho trang Cocktail

## Tổng quan
Đã cập nhật trang cocktail với các tính năng mới và giao diện được cải thiện, bao gồm:
- Hiển thị cocktail signature theo hàng dọc
- Modal chi tiết đầy đủ với ảnh, đánh giá, bình luận
- Dữ liệu động từ database
- Hỗ trợ responsive cho mobile

## Các thay đổi chính

### 1. Layout Cocktail Signature
- **Trước**: Hiển thị theo grid (hàng ngang)
- **Sau**: Hiển thị theo hàng dọc (vertical list)
- Mỗi cocktail hiển thị: ảnh, tên, mô tả, nguyên liệu, đánh giá sao, giá

### 2. Modal Chi tiết Cocktail
- **Ảnh chính**: Hiển thị ảnh lớn với navigation buttons
- **Thumbnails**: Ảnh nhỏ có thể click để chuyển đổi
- **Thông tin đầy đủ**: Tên, giá, mô tả, nguyên liệu, mức độ mạnh
- **Đánh giá**: Hiển thị sao và số lượng đánh giá
- **Bình luận**: Danh sách reviews từ khách hàng

### 3. Database Updates
- Tạo bảng `cocktail_reviews` để lưu đánh giá
- Thêm cột `images` cho `menu_items` (JSON array)
- Dữ liệu mẫu cho reviews và ảnh

## Cài đặt

### 1. Cập nhật Database
Chạy file SQL để tạo bảng và cập nhật cấu trúc:
```sql
-- Chạy file: cocktail_database_update.sql
```

### 2. Thêm ảnh
Tạo thư mục `assets/images/` và thêm ảnh cocktail:
- `cocktail-1.jpg`, `cocktail-1-2.jpg`, `cocktail-1-3.jpg`
- `cocktail-2.jpg`, `cocktail-2-2.jpg`
- `cocktail-3.jpg`

### 3. Cập nhật ảnh trong Database
```sql
UPDATE menu_items 
SET images = '["assets/images/cocktail-1.jpg", "assets/images/cocktail-1-2.jpg"]'
WHERE id = [cocktail_id];
```

## Cấu trúc dữ liệu

### Bảng cocktail_reviews
```sql
CREATE TABLE cocktail_reviews (
  id int(11) NOT NULL AUTO_INCREMENT,
  menu_item_id int(11) NOT NULL,
  user_id int(11) DEFAULT NULL,
  customer_name varchar(100) NOT NULL,
  rating int(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
  comment text DEFAULT NULL,
  is_approved tinyint(1) NOT NULL DEFAULT 1,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
);
```

### Cột images trong menu_items
- Kiểu: TEXT (JSON array)
- Ví dụ: `["image1.jpg", "image2.jpg", "image3.jpg"]`

## Tính năng JavaScript

### 1. openCocktailDetail(cocktailId)
- Mở modal chi tiết cocktail
- Hiển thị ảnh, thông tin, đánh giá, reviews

### 2. switchCocktailImage(index)
- Chuyển đổi ảnh trong modal
- Cập nhật thumbnail active state

### 3. populateCocktailImages(images)
- Hiển thị ảnh chính và thumbnails
- Tạo navigation buttons

### 4. populateCocktailRating(avgRating, reviewCount)
- Hiển thị sao đánh giá
- Hiển thị số lượng reviews

### 5. populateCocktailReviews(reviews)
- Hiển thị danh sách bình luận
- Format: tên, sao, ngày, nội dung

## Responsive Design

### Desktop
- Layout 2 cột cho modal chi tiết
- Cocktail signature hiển thị theo hàng ngang

### Mobile
- Layout 1 cột cho modal chi tiết
- Cocktail signature hiển thị theo hàng dọc
- Ảnh và buttons được tối ưu cho touch

## CSS Classes chính

### Signature Cocktails
- `.signature-cocktails-list`: Container chính
- `.signature-cocktail-item`: Mỗi item cocktail
- `.cocktail-main-info`: Thông tin chính
- `.cocktail-image`: Container ảnh
- `.cocktail-content`: Nội dung text
- `.cocktail-rating`: Phần đánh giá sao

### Modal Detail
- `.cocktail-detail-container`: Container chính
- `.cocktail-detail-images`: Phần ảnh
- `.cocktail-detail-info`: Phần thông tin
- `.main-image-container`: Ảnh chính
- `.thumbnail-images`: Ảnh nhỏ
- `.cocktail-reviews`: Phần reviews

## Troubleshooting

### 1. Lỗi Foreign Key Constraint khi chạy SQL
**Lỗi**: `#1452 - Cannot add or update a child row: a foreign key constraint fails`
**Nguyên nhân**: `user_id` trong bảng `cocktail_reviews` tham chiếu đến `users.id` không tồn tại
**Giải pháp**: 
- Sử dụng `user_id = NULL` thay vì ID cụ thể
- Hoặc chỉ sử dụng các `user_id` có sẵn trong bảng `users`
- Chạy file `cocktail_test.sql` thay vì `cocktail_database_update.sql`

### 2. Ảnh không hiển thị
- Kiểm tra đường dẫn ảnh trong database
- Đảm bảo thư mục `assets/images/` tồn tại
- Kiểm tra quyền truy cập file

### 3. Reviews không hiển thị
- Kiểm tra bảng `cocktail_reviews` đã được tạo
- Kiểm tra dữ liệu mẫu đã được insert
- Kiểm tra `is_approved = 1`

### 4. JavaScript errors
- Kiểm tra console browser
- Đảm bảo các biến global được khai báo đúng
- Kiểm tra các element IDs trong HTML

## Cách khắc phục lỗi Foreign Key

### Phương án 1: Sử dụng user_id = NULL
```sql
INSERT INTO `cocktail_reviews` (`menu_item_id`, `user_id`, `customer_name`, `rating`, `comment`, `is_approved`) VALUES
(1, NULL, 'Nguyễn Văn A', 5, 'Cocktail rất ngon, hương vị độc đáo!', 1);
```

### Phương án 2: Chỉ sử dụng user_id có sẵn
```sql
-- Kiểm tra users có sẵn trước
SELECT id, username FROM users;

-- Chỉ sử dụng các ID có sẵn
INSERT INTO `cocktail_reviews` (`menu_item_id`, `user_id`, `customer_name`, `rating`, `comment`, `is_approved`) VALUES
(1, 1, 'Nguyễn Văn A', 5, 'Cocktail rất ngon, hương vị độc đáo!', 1),
(1, 2, 'Trần Thị B', 4, 'Phong cách pha chế chuyên nghiệp', 1);
```

### Phương án 3: Chạy từng bước một
Sử dụng file `cocktail_test.sql` và chạy từng câu lệnh một để kiểm tra:
1. Tạo bảng `cocktail_reviews`
2. Thêm cột `images` cho `menu_items`
3. Insert dữ liệu mẫu
4. Cập nhật ảnh cho cocktail

## Tương lai

### Tính năng có thể thêm
- Form thêm review mới
- Filter cocktail theo rating
- Search cocktail theo tên/nguyên liệu
- Pagination cho danh sách cocktail
- Admin panel quản lý reviews

### Tối ưu hóa
- Lazy loading cho ảnh
- Caching reviews
- Infinite scroll
- Progressive Web App features
