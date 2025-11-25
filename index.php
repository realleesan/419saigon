<?php 
$page_title = "Trang Chủ";
include 'includes/header.php'; 
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-frame">
        <div class="hero-content">
            <h1 class="hero-title">419 Saigon</h1>
            <p class="hero-subtitle">A Japanese Izakaya Meets Cocktail & Cinema</p>
            <div class="hero-cta">
                <button id="openExperienceModal" class="btn btn-primary"><span class="hero-arrow">↗</span>Book Your Experience</button>
            </div>
        </div>
    </div>
</section>

<!-- Experiences Modal (hidden by default) -->
<div id="experiencesModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" style="position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1980;"></div>
    <div class="modal-panel" role="dialog" aria-modal="true" style="position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:95%;max-width:1100px;background:var(--color-white);color:var(--color-black);border-radius:8px;padding:1.5rem;z-index:1990;box-shadow:0 10px 30px rgba(0,0,0,0.12);">
        <button id="closeExperiencesModal" aria-label="Close" style="position:absolute;right:12px;top:12px;background:transparent;border:1px solid var(--color-black);padding:6px 10px;border-radius:6px;cursor:pointer;">&times;</button>
        <div class="container">
            <h2 class="section-title" style="text-align:center;margin-top:0;">Trải Nghiệm Độc Đáo</h2>
            <div class="grid grid-3" style="margin-top:1.25rem;">
                <!-- Cocktail Card -->
                <div class="card" style="background:var(--color-white);">
                    <div class="card-image">
                        <img src="assets/images/cocktail-placeholder.jpg" alt="Cocktail Experience" class="lazy" data-src="assets/images/cocktail-placeholder.jpg">
                    </div>
                    <div class="card-content">
                        <h3>Cocktail</h3>
                        <p>Thưởng thức những ly cocktail độc đáo được pha chế bởi các bartender chuyên nghiệp với nguyên liệu cao cấp và công thức độc quyền.</p>
                        <div class="card-actions">
                            <a href="cocktail.php" class="btn btn-secondary">Khám Phá</a>
                            <a href="cocktail.php#menu" class="btn btn-primary">Xem Menu</a>
                        </div>
                    </div>
                </div>

                <!-- Cinema Card -->
                <div class="card" style="background:var(--color-white);">
                    <div class="card-image">
                        <img src="assets/images/cinema-placeholder.jpg" alt="Cinema Experience" class="lazy" data-src="assets/images/cinema-placeholder.jpg">
                    </div>
                    <div class="card-content">
                        <h3>Cinema</h3>
                        <p>Trải nghiệm xem phim trong phòng chiếu riêng tư với công nghệ hiện đại, âm thanh chất lượng cao và dịch vụ phục vụ tận tình.</p>
                        <div class="card-actions">
                            <a href="cinema.php" class="btn btn-secondary">Khám Phá</a>
                            <a href="cinema.php#booking" class="btn btn-primary">Đặt Phòng</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- About Preview Section -->
<section class="section" style="background: var(--color-dark-gray);">
    <div class="container">
        <div class="grid grid-2">
            <div class="about-preview-content">
                <h2>Về 419 Saigon</h2>
                <p>419 Saigon là sự kết hợp hoàn hảo giữa ẩm thực Nhật Bản truyền thống, nghệ thuật pha chế cocktail hiện đại và trải nghiệm xem phim độc đáo. Chúng tôi mang đến cho bạn một không gian giải trí đa chiều, nơi mọi giác quan đều được thỏa mãn.</p>
                <p>Từ những món ăn Izakaya được chế biến tinh tế, đến những ly cocktail độc đáo, và không gian xem phim riêng tư - mỗi trải nghiệm tại 419 Saigon đều được thiết kế để mang lại cảm giác sang trọng và đáng nhớ.</p>
                <a href="about.php" class="btn btn-primary">Tìm Hiểu Thêm</a>
            </div>
            <div class="about-preview-image">
                <img src="assets/images/about-preview.jpg" alt="419 Saigon Interior" class="lazy" data-src="assets/images/about-preview.jpg">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Tại Sao Chọn 419 Saigon?</h2>
        <div class="grid grid-4">
            <div class="feature-card">
                <h4>Cocktail Độc Đáo</h4>
                <p>Những ly cocktail được pha chế độc quyền với hương vị đặc biệt</p>
            </div>
            <div class="feature-card">
                <h4>Phòng Chiếu Riêng</h4>
                <p>Không gian xem phim riêng tư với công nghệ hiện đại</p>
            </div>
            <div class="feature-card">
                <h4>Dịch Vụ Cao Cấp</h4>
                <p>Đội ngũ nhân viên chuyên nghiệp, phục vụ tận tâm</p>
            </div>
        </div>
    </div>
</section>

<style>
/* Hero Frame - Khung nghệ thuật cho hero section */
.hero {
    padding-left: var(--spacing-md);
    padding-right: var(--spacing-md);
}

.hero-frame {
    max-width: 1200px;
    width: 100%;
    margin: 0 auto;
    padding: var(--spacing-xl);
    border: 1px solid rgba(0, 0, 0, 0.15);
    border-radius: 16px;
    background: var(--color-white);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 160px);
    z-index: 2;
}

.hero-frame .hero-content {
    width: 100%;
    max-width: 800px;
    padding: 0;
}

@media (max-width: 768px) {
    .hero-frame {
        padding: var(--spacing-md);
        min-height: calc(100vh - 140px);
    }
}

@media (max-width: 480px) {
    .hero-frame {
        padding: var(--spacing-sm);
        min-height: calc(100vh - 120px);
    }
}

/* Additional styles for homepage */
.card-image {
    height: 200px;
    overflow: hidden;
    border-radius: 8px 8px 0 0;
    margin: -1.5rem -1.5rem 1rem -1.5rem;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-normal);
}

.card:hover .card-image img {
    transform: scale(1.05);
}

.card-content {
    padding: 0;
}

.card-content h3 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-sm);
}

.card-actions {
    display: flex;
    gap: var(--spacing-sm);
    margin-top: var(--spacing-md);
}

.about-preview-content {
    padding: var(--spacing-xl);
}

.about-preview-image img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 8px;
}

.feature-card {
    text-align: center;
    padding: var(--spacing-lg);
}

.feature-icon {
    font-size: 3rem;
    margin-bottom: var(--spacing-md);
}

.feature-card h4 {
    color: var(--color-gold);
    margin-bottom: var(--spacing-sm);
}

.cta-buttons {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .about-preview-content {
        padding: var(--spacing-md);
    }
    
    .about-preview-image img {
        height: 300px;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<?php include 'includes/footer.php'; ?>

<script>
// Modal open/close handlers for Experiences modal
document.addEventListener('DOMContentLoaded', function() {
    const openBtn = document.getElementById('openExperienceModal');
    const closeBtn = document.getElementById('closeExperiencesModal');
    const modal = document.getElementById('experiencesModal');
    const backdrop = modal ? modal.querySelector('.modal-backdrop') : null;

    function openModal() {
        if (!modal) return;
        modal.style.display = 'block';
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);
    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
});
</script>
