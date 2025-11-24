<?php 
$page_title = "Liên Hệ";
include 'includes/header.php'; 
?>

<!-- Hero Section -->
<section class="hero" style="height: 60vh;">
    <div class="hero-content">
        <h1 class="hero-title">Liên Hệ</h1>
        <p class="hero-subtitle">Hãy liên hệ với chúng tôi</p>
    </div>
</section>

<!-- Contact Info Section -->
<section class="section">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info">
                <h2>Thông Tin Liên Hệ</h2>
                <div class="info-items">
                    <div class="info-item">
                        <div class="info-icon">Location</div>
                        <div class="info-content">
                            <h4>Địa Chỉ</h4>
                            <p>419 Đường ABC, Quận 1, TP.HCM<br>
                            Việt Nam</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">Phone</div>
                        <div class="info-content">
                            <h4>Điện Thoại</h4>
                            <p><a href="tel:+842812345678">+84 28 1234 5678</a><br>
                            <a href="tel:+84901234567">+84 901 234 567</a></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">Email</div>
                        <div class="info-content">
                            <h4>Email</h4>
                            <p><a href="mailto:info@419saigon.com">info@419saigon.com</a><br>
                            <a href="mailto:booking@419saigon.com">booking@419saigon.com</a></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">Time</div>
                        <div class="info-content">
                            <h4>Giờ Mở Cửa</h4>
                            <p><strong>Thứ 2 - Thứ 6:</strong> 18:00 - 02:00<br>
                            <strong>Thứ 7 - Chủ Nhật:</strong> 17:00 - 03:00</p>
                        </div>
                    </div>
                </div>
                
                <div class="social-links">
                    <h4>Theo Dõi Chúng Tôi</h4>
                    <div class="social-icons">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <span class="social-icon">FB</span>
                            <span>Facebook</span>
                        </a>
                        <a href="#" class="social-link" aria-label="Instagram">
                            <span class="social-icon">IG</span>
                            <span>Instagram</span>
                        </a>
                        <a href="#" class="social-link" aria-label="YouTube">
                            <span class="social-icon">YT</span>
                            <span>YouTube</span>
                        </a>
                        <a href="#" class="social-link" aria-label="TikTok">
                            <span class="social-icon">TT</span>
                            <span>TikTok</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="contact-form-container">
                <h2>Gửi Tin Nhắn</h2>
                <form class="contact-form" action="process_contact.php" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">Họ *</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Tên *</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Chủ đề *</label>
                        <select id="subject" name="subject" required>
                            <option value="">Chọn chủ đề</option>
                            <option value="booking">Đặt bàn</option>
                            <option value="reservation">Đặt phòng chiếu</option>
                            <option value="feedback">Phản hồi</option>
                            <option value="partnership">Hợp tác</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Nội dung tin nhắn *</label>
                        <textarea id="message" name="message" rows="5" required placeholder="Hãy chia sẻ với chúng tôi..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="newsletter" value="1">
                            <span class="checkmark"></span>
                            Tôi muốn nhận thông tin về các sự kiện và ưu đãi đặc biệt
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-full">Gửi Tin Nhắn</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="section" style="background: var(--color-dark-gray);">
    <div class="container">
        <h2 class="section-title">Vị Trí Của Chúng Tôi</h2>
        <div class="map-container">
            <div class="map-info">
                <h3>Hướng Dẫn Đường Đi</h3>
                <div class="directions">
                    <div class="direction-item">
                        <h4>🚗 Bằng Ô Tô</h4>
                        <p>Từ trung tâm Quận 1, đi theo đường ABC về hướng Đông khoảng 2km. 419 Saigon nằm bên phải đường, có bảng hiệu màu vàng nổi bật.</p>
                    </div>
                    <div class="direction-item">
                        <h4>Bằng Xe Buýt</h4>
                        <p>Tuyến xe buýt số 01, 02, 03 dừng tại trạm "ABC Station", đi bộ 100m về phía Đông.</p>
                    </div>
                    <div class="direction-item">
                        <h4>Đi Bộ</h4>
                        <p>Từ Bitexco Financial Tower, đi bộ khoảng 15 phút theo đường ABC về hướng Đông.</p>
                    </div>
                </div>
            </div>
            <div class="map-frame">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4240989444477!2d106.6983153152608!3d10.776888992319!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f46f64b933f%3A0xf8a6e5b2a5a4f1f4!2sHo%20Chi%20Minh%20City%2C%20Vietnam!5e0!3m2!1sen!2s!4v1640995200000!5m2!1sen!2s" 
                    width="100%" 
                    height="400" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Câu Hỏi Thường Gặp</h2>
        <div class="faq-container">
            <div class="faq-item">
                <div class="faq-question">
                    <h4>Có cần đặt bàn trước không?</h4>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Chúng tôi khuyến nghị đặt bàn trước, đặc biệt vào cuối tuần và các ngày lễ. Bạn có thể đặt bàn qua website, điện thoại hoặc email.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h4>Phòng chiếu riêng có thể chứa bao nhiêu người?</h4>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Mỗi phòng chiếu riêng có thể chứa tối đa 8 người. Nếu nhóm lớn hơn, chúng tôi có thể sắp xếp nhiều phòng liền kề.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h4>Có menu cho người ăn chay không?</h4>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Có, chúng tôi có nhiều lựa chọn cho người ăn chay và thuần chay. Hãy cho chúng tôi biết khi đặt bàn để chúng tôi chuẩn bị tốt nhất.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h4>Có bãi đỗ xe không?</h4>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Có, chúng tôi có bãi đỗ xe miễn phí cho khách hàng. Bãi đỗ xe nằm ngay sau tòa nhà, có bảo vệ 24/7.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h4>Có thể tổ chức sự kiện đặc biệt không?</h4>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Có, chúng tôi cung cấp dịch vụ tổ chức sự kiện như sinh nhật, kỷ niệm, họp mặt công ty. Hãy liên hệ với chúng tôi để được tư vấn chi tiết.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Contact specific styles */
.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-xxl);
}

.contact-info h2,
.contact-form-container h2 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-xl);
}

.info-items {
    margin-bottom: var(--spacing-xl);
}

.info-item {
    display: flex;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
    align-items: flex-start;
}

.info-icon {
    font-size: 1.5rem;
    min-width: 40px;
}

.info-content h4 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-xs);
}

.info-content a {
    color: var(--color-cream);
    text-decoration: none;
    transition: color var(--transition-normal);
}

.info-content a:hover {
    color: var(--color-gold);
}

.social-links h4 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-md);
}

.social-icons {
    display: flex;
    gap: var(--spacing-md);
    flex-wrap: wrap;
}

.social-link {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    background: var(--color-dark-gray);
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: 20px;
    text-decoration: none;
    color: var(--color-cream);
    transition: all var(--transition-normal);
    border: 1px solid rgba(212, 175, 55, 0.2);
}

.social-link:hover {
    background: var(--color-gold);
    color: var(--color-black);
    transform: translateY(-2px);
}

.contact-form-container {
    background: var(--color-dark-gray);
    padding: var(--spacing-xl);
    border-radius: 8px;
    border: 1px solid rgba(212, 175, 55, 0.2);
}

.contact-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: var(--spacing-xs);
    color: var(--color-cream);
    font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: var(--spacing-sm);
    border: 1px solid var(--color-gray);
    border-radius: 4px;
    background: var(--color-black);
    color: var(--color-cream);
    font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--color-gold);
    box-shadow: 0 0 5px rgba(212, 175, 55, 0.3);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    cursor: pointer;
    font-size: 0.9rem;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0;
}

.btn-full {
    width: 100%;
    padding: var(--spacing-md);
    font-size: 1.1rem;
}

.map-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-xl);
    align-items: start;
}

.map-info h3 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-lg);
}

.directions {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.direction-item h4 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-sm);
}

.map-frame {
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid rgba(212, 175, 55, 0.2);
}

.faq-container {
    max-width: 800px;
    margin: 0 auto;
}

.faq-item {
    background: var(--color-dark-gray);
    border-radius: 8px;
    margin-bottom: var(--spacing-md);
    border: 1px solid rgba(212, 175, 55, 0.2);
    overflow: hidden;
}

.faq-question {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    cursor: pointer;
    transition: background-color var(--transition-normal);
}

.faq-question:hover {
    background: rgba(212, 175, 55, 0.1);
}

.faq-question h4 {
    color: var(--color-gold);
    margin: 0;
}

.faq-toggle {
    color: var(--color-gold);
    font-size: 1.5rem;
    font-weight: bold;
    transition: transform var(--transition-normal);
}

.faq-item.active .faq-toggle {
    transform: rotate(45deg);
}

.faq-answer {
    padding: 0 var(--spacing-lg);
    max-height: 0;
    overflow: hidden;
    transition: all var(--transition-normal);
}

.faq-item.active .faq-answer {
    padding: 0 var(--spacing-lg) var(--spacing-lg);
    max-height: 200px;
}

@media (max-width: 768px) {
    .contact-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-xl);
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .map-container {
        grid-template-columns: 1fr;
        gap: var(--spacing-lg);
    }
    
    .social-icons {
        justify-content: center;
    }
}
</style>

<script>
// FAQ functionality
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', function() {
            const isActive = item.classList.contains('active');
            
            // Close all other FAQ items
            faqItems.forEach(otherItem => {
                otherItem.classList.remove('active');
            });
            
            // Toggle current item
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
