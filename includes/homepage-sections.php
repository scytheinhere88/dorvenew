<?php
/**
 * HOMEPAGE LUXURY SECTIONS
 * Include this file in index.php for upgraded sections
 */

// This file contains 3 luxury sections:
// 1. Hero Slider (from banners table)
// 2. Category Marquee (auto-scrolling)
// 3. Enhanced Featured Products
?>

<!-- ========== HERO SLIDER SECTION ========== -->
<?php if (!empty($slider_banners)): ?>
<section class="hero-slider-container">
    <?php foreach ($slider_banners as $index => $banner): ?>
        <div class="hero-slide <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>">
            <img src="<?= htmlspecialchars($banner['image_url']) ?>" 
                 alt="<?= htmlspecialchars($banner['title'] ?? 'Banner') ?>" 
                 class="hero-slide-image">
            <div class="hero-slide-overlay"></div>
            <div class="hero-slide-content">
                <?php if (!empty($banner['title'])): ?>
                    <h1 class="hero-slide-title"><?= htmlspecialchars($banner['title']) ?></h1>
                <?php endif; ?>
                <?php if (!empty($banner['subtitle'])): ?>
                    <p class="hero-slide-subtitle"><?= htmlspecialchars($banner['subtitle']) ?></p>
                <?php endif; ?>
                <?php if (!empty($banner['cta_text']) && !empty($banner['link_url'])): ?>
                    <a href="<?= htmlspecialchars($banner['link_url']) ?>" class="hero-slide-cta">
                        <?= htmlspecialchars($banner['cta_text']) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <!-- Slider Navigation Dots -->
    <?php if (count($slider_banners) > 1): ?>
        <div class="slider-nav">
            <?php foreach ($slider_banners as $index => $banner): ?>
                <span class="slider-dot <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>" onclick="goToSlide(<?= $index ?>)"></span>
            <?php endforeach; ?>
        </div>
        
        <!-- Slider Arrows -->
        <div class="slider-arrows">
            <div class="slider-arrow" onclick="prevSlide()">❮</div>
            <div class="slider-arrow" onclick="nextSlide()">❯</div>
        </div>
    <?php endif; ?>
</section>

<script>
let currentSlide = 0;
const totalSlides = <?= count($slider_banners) ?>;
const slides = document.querySelectorAll('.hero-slide');
const dots = document.querySelectorAll('.slider-dot');
let sliderInterval;

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    slides[index].classList.add('active');
    dots[index].classList.add('active');
    currentSlide = index;
}

function nextSlide() {
    let next = (currentSlide + 1) % totalSlides;
    showSlide(next);
    resetInterval();
}

function prevSlide() {
    let prev = (currentSlide - 1 + totalSlides) % totalSlides;
    showSlide(prev);
    resetInterval();
}

function goToSlide(index) {
    showSlide(index);
    resetInterval();
}

function resetInterval() {
    clearInterval(sliderInterval);
    sliderInterval = setInterval(nextSlide, 5000);
}

// Auto-play slider every 5 seconds
if (totalSlides > 1) {
    sliderInterval = setInterval(nextSlide, 5000);
}
</script>
<?php endif; ?>

<!-- CATEGORY MARQUEE REMOVED - Already in main index.php at "Jelajahi Koleksi Kami" section -->

<!-- ========== FEATURED PRODUCTS SECTION ========== -->
<?php if (!empty($featured_products)): ?>
<section class="featured-section" style="background: linear-gradient(135deg, #FAFAFA 0%, #FFFFFF 100%);">
    <div class="container">
        <div class="section-header">
            <div class="section-pretitle">Pilihan Spesial</div>
            <h2 class="section-title">Produk Unggulan</h2>
            <p class="section-description">Koleksi terbaik yang dipilih khusus untuk Anda</p>
        </div>

        <div class="products-grid">
            <?php foreach ($featured_products as $product): ?>
                <a href="/pages/product-detail.php?slug=<?= $product['slug'] ?>" class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <img src="https://images.pexels.com/photos/1926769/pexels-photo-1926769.jpeg?auto=compress&cs=tinysrgb&w=400" 
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php endif; ?>
                        
                        <?php if ($product['is_new'] == 1): ?>
                            <span class="product-badge" style="background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);">✨ NEW</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <?php if (!empty($product['category_name'])): ?>
                            <div class="product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                        <?php endif; ?>
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="product-price">
                            <?php if (!empty($product['discount_price']) && $product['discount_price'] < $product['price']): ?>
                                <span class="product-price-discount"><?= formatPrice($product['price']) ?></span>
                                <?= formatPrice($product['discount_price']) ?>
                            <?php else: ?>
                                <?= formatPrice($product['price']) ?>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($product['stock'])): ?>
                            <?php if ($product['stock'] > 0): ?>
                                <div class="product-stock in-stock">✓ In Stock</div>
                            <?php else: ?>
                                <div class="product-stock out-stock">⚠️ Out of Stock</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <a href="/pages/all-products.php" class="view-all-btn">
            Lihat Semua Produk
        </a>
    </div>
</section>
<?php endif; ?>

<!-- ========== NEW ARRIVALS SECTION ========== -->
<?php if (!empty($new_arrivals)): ?>
<section class="featured-section" style="background: var(--white);">
    <div class="container">
        <div class="section-header">
            <div class="section-pretitle">Koleksi Terbaru</div>
            <h2 class="section-title">New Arrivals</h2>
            <p class="section-description">Produk terbaru yang baru saja tiba</p>
        </div>

        <div class="products-grid">
            <?php foreach ($new_arrivals as $product): ?>
                <a href="/pages/product-detail.php?slug=<?= $product['slug'] ?>" class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <img src="https://images.pexels.com/photos/1926769/pexels-photo-1926769.jpeg?auto=compress&cs=tinysrgb&w=400" 
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php endif; ?>
                        <span class="product-badge" style="background: #10B981;">🌟 NEW ARRIVAL</span>
                    </div>
                    <div class="product-info">
                        <?php if (!empty($product['category_name'])): ?>
                            <div class="product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                        <?php endif; ?>
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="product-price">
                            <?php if (!empty($product['discount_price']) && $product['discount_price'] < $product['price']): ?>
                                <span class="product-price-discount"><?= formatPrice($product['price']) ?></span>
                                <?= formatPrice($product['discount_price']) ?>
                            <?php else: ?>
                                <?= formatPrice($product['price']) ?>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($product['stock'])): ?>
                            <?php if ($product['stock'] > 0): ?>
                                <div class="product-stock in-stock">✓ In Stock</div>
                            <?php else: ?>
                                <div class="product-stock out-stock">⚠️ Out of Stock</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <a href="/pages/all-products.php?filter=new" class="view-all-btn">
            Lihat Semua Produk Baru
        </a>
    </div>
</section>
<?php endif; ?>
