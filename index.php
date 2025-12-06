<?php
require_once __DIR__ . '/config.php';

// Get banners for homepage slider (8-10 banners)
try {
    $stmt = $pdo->query("SELECT * FROM banners WHERE banner_type = 'slider' AND is_active = 1 ORDER BY display_order ASC, created_at DESC LIMIT 10");
    $slider_banners = $stmt->fetchAll();
} catch (PDOException $e) {
    $slider_banners = [];
}

// Get popup banner
try {
    $stmt = $pdo->query("SELECT * FROM banners WHERE banner_type = 'popup' AND is_active = 1 ORDER BY display_order ASC, created_at DESC LIMIT 1");
    $popup_banner = $stmt->fetch();
} catch (PDOException $e) {
    $popup_banner = null;
}

// Get featured products
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name
                      FROM products p
                      LEFT JOIN categories c ON p.category_id = c.id
                      WHERE p.is_featured = 1 AND p.is_active = 1
                      ORDER BY p.created_at DESC
                      LIMIT 8");
$stmt->execute();
$featured_products = $stmt->fetchAll();

// Get new arrivals
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name
                      FROM products p
                      LEFT JOIN categories c ON p.category_id = c.id
                      WHERE p.is_new = 1 AND p.is_active = 1
                      ORDER BY p.created_at DESC
                      LIMIT 8");
$stmt->execute();
$new_arrivals = $stmt->fetchAll();

// Get categories
try {
    $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 12");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    try {
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY sequence ASC LIMIT 12");
        $categories = $stmt->fetchAll();
    } catch (PDOException $e2) {
        $categories = [];
    }
}

$page_title = 'Dorve.id | Fashion Pria & Wanita Indonesia – Toko Baju Online Terpercaya';
$page_description = 'Belanja fashion online di Dorve.id - pusat baju pria, wanita & couple terlengkap. Koleksi dress, kemeja, kaos, hoodie kekinian dengan harga terjangkau. Gratis ongkir, COD tersedia, pengiriman cepat ke seluruh Indonesia.';
$page_keywords = 'dorve, dorve id, toko baju online, fashion pria, fashion wanita, baju kekinian, dress wanita, kemeja pria, kaos couple, hoodie, baju trendy, fashion indonesia, belanja online, toko fashion terpercaya';
include __DIR__ . '/includes/header.php';
?>

<!-- JSON-LD Schema for Homepage -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "Dorve.id",
  "url": "https://dorve.id",
  "description": "Pusat Fashion Pria & Wanita Indonesia - Toko Baju Online Terpercaya",
  "potentialAction": {
    "@type": "SearchAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "https://dorve.id/pages/all-products.php?search={search_term_string}"
    },
    "query-input": "required name=search_term_string"
  }
}
</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "OnlineStore",
  "name": "Dorve.id",
  "image": "https://dorve.id/public/images/logo.png",
  "url": "https://dorve.id",
  "priceRange": "Rp 50.000 - Rp 1.000.000",
  "address": {
    "@type": "PostalAddress",
    "addressCountry": "ID"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.8",
    "reviewCount": "2500",
    "bestRating": "5",
    "worstRating": "1"
  }
}
</script>

<link rel="stylesheet" href="/homepage-luxury-style.css">

<!-- ========== HERO BANNER SLIDER (8-10 BANNERS) ========== -->
<?php if (!empty($slider_banners)): ?>
<section class="luxury-hero-slider">
    <?php foreach ($slider_banners as $index => $banner): ?>
        <div class="hero-slide <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>">
            <div class="hero-slide-bg" style="background-image: url('<?= htmlspecialchars($banner['image_url']) ?>')"></div>
            <div class="hero-slide-overlay"></div>
            <div class="container">
                <div class="hero-slide-content">
                    <?php if (!empty($banner['title'])): ?>
                        <h1 class="hero-slide-title" data-aos="fade-up" data-aos-delay="200">
                            <?= htmlspecialchars($banner['title']) ?>
                        </h1>
                    <?php endif; ?>
                    <?php if (!empty($banner['subtitle'])): ?>
                        <p class="hero-slide-subtitle" data-aos="fade-up" data-aos-delay="400">
                            <?= htmlspecialchars($banner['subtitle']) ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($banner['cta_text']) && !empty($banner['link_url'])): ?>
                        <a href="<?= htmlspecialchars($banner['link_url']) ?>" 
                           class="hero-slide-cta" 
                           data-aos="fade-up" 
                           data-aos-delay="600">
                            <?= htmlspecialchars($banner['cta_text']) ?>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if (count($slider_banners) > 1): ?>
        <!-- Navigation -->
        <div class="slider-nav">
            <?php foreach ($slider_banners as $index => $banner): ?>
                <button class="slider-dot <?= $index === 0 ? 'active' : '' ?>" 
                        data-slide="<?= $index ?>" 
                        onclick="goToSlide(<?= $index ?>)"
                        aria-label="Go to slide <?= $index + 1 ?>">
                </button>
            <?php endforeach; ?>
        </div>
        
        <!-- Arrows -->
        <button class="slider-arrow slider-arrow-left" onclick="prevSlide()" aria-label="Previous slide">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button>
        <button class="slider-arrow slider-arrow-right" onclick="nextSlide()" aria-label="Next slide">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
        
        <!-- Progress Bar -->
        <div class="slider-progress-bar">
            <div class="slider-progress-fill"></div>
        </div>
    <?php endif; ?>
</section>
<?php else: ?>
<!-- Default Hero -->
<section class="luxury-hero-slider">
    <div class="hero-slide active">
        <div class="hero-slide-bg" style="background-image: url('/public/images/Dorve1.png')"></div>
        <div class="hero-slide-overlay"></div>
        <div class="container">
            <div class="hero-slide-content">
                <h1 class="hero-slide-title">Dorve.id – Fashion Online Terpercaya</h1>
                <p class="hero-slide-subtitle">Koleksi Terbaru Fashion Pria, Wanita & Couple Terlengkap di Indonesia</p>
                <a href="/pages/all-products.php" class="hero-slide-cta">
                    Belanja Sekarang
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========== CATEGORY MARQUEE (TIDAK TIMPA) ========== -->
<?php if (!empty($categories)): ?>
<section class="category-marquee-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">Jelajahi Koleksi Kami</span>
            <h2 class="section-title">Kategori Fashion Terlengkap</h2>
            <p class="section-description">Temukan fashion pilihan dari berbagai kategori yang sesuai dengan gaya Anda</p>
        </div>
    </div>
    
    <div class="category-marquee-wrapper" data-aos="fade-up" data-aos-delay="200">
        <div class="category-marquee-track">
            <?php 
            // Duplicate for seamless loop
            $marquee_categories = array_merge($categories, $categories);
            foreach ($marquee_categories as $category): 
            ?>
                <a href="/pages/all-products.php?category=<?= $category['id'] ?>" class="category-marquee-card">
                    <div class="category-icon-box">
                        <?php if (!empty($category['icon'])): ?>
                            <?php if (filter_var($category['icon'], FILTER_VALIDATE_URL)): ?>
                                <img src="<?= htmlspecialchars($category['icon']) ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="category-icon-img">
                            <?php else: ?>
                                <span class="category-icon-emoji"><?= htmlspecialchars($category['icon']) ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="category-icon-emoji">🛍️</span>
                        <?php endif; ?>
                    </div>
                    <h3 class="category-name"><?= htmlspecialchars($category['name']) ?></h3>
                    <span class="category-cta">Belanja →</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========== SEO CONTENT: BRAND STORY ========== -->
<section class="brand-story-section">
    <div class="container">
        <div class="brand-story-grid">
            <div class="brand-story-image" data-aos="fade-right">
                <img src="/public/images/Dorve2.png" alt="Dorve.id - Toko Fashion Online Terpercaya Indonesia">
            </div>
            <div class="brand-story-content" data-aos="fade-left">
                <span class="section-subtitle">Tentang Kami</span>
                <h2 class="section-title">Dorve.id – Pusat Fashion Online Terpercaya di Indonesia</h2>
                <div class="brand-story-text">
                    <p><strong>Dorve.id</strong> adalah destinasi belanja fashion online terpercaya yang menyediakan koleksi <strong>baju pria</strong>, <strong>baju wanita</strong>, dan <strong>fashion couple</strong> terlengkap di Indonesia. Kami berkomitmen menghadirkan <strong>fashion kekinian</strong> berkualitas tinggi dengan harga terjangkau untuk semua kalangan.</p>
                    
                    <p>Sejak berdiri, Dorve.id telah melayani ribuan pelanggan di seluruh Indonesia dengan berbagai koleksi fashion seperti <strong>dress wanita elegan</strong>, <strong>kemeja pria formal</strong>, <strong>kaos casual</strong>, <strong>hoodie trendy</strong>, hingga <strong>outfit couple matching</strong>. Setiap produk yang kami tawarkan melewati quality control ketat untuk memastikan Anda mendapatkan fashion terbaik.</p>
                    
                    <p>Dengan layanan <strong>gratis ongkir</strong> untuk pembelian tertentu, sistem pembayaran aman, dan pengiriman cepat ke seluruh Indonesia, Dorve.id menjadikan pengalaman belanja online Anda lebih mudah dan menyenangkan. Kami juga menyediakan COD (Cash on Delivery) untuk area tertentu, memberikan fleksibilitas maksimal bagi pelanggan.</p>
                </div>
                
                <div class="brand-stats">
                    <div class="stat-item">
                        <span class="stat-number">50K+</span>
                        <span class="stat-label">Pelanggan Puas</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">10K+</span>
                        <span class="stat-label">Produk Fashion</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">4.8/5</span>
                        <span class="stat-label">Rating Toko</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== FEATURED PRODUCTS ========== -->
<?php if (!empty($featured_products)): ?>
<section class="products-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">Pilihan Terbaik</span>
            <h2 class="section-title">Produk Unggulan Kami</h2>
            <p class="section-description">Koleksi fashion pilihan yang paling disukai pelanggan</p>
        </div>
        
        <div class="products-grid">
            <?php foreach ($featured_products as $index => $product): ?>
                <a href="/pages/product-detail.php?id=<?= $product['id'] ?>" 
                   class="product-card" 
                   data-aos="fade-up" 
                   data-aos-delay="<?= $index * 100 ?>">
                    <div class="product-image-wrapper">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="product-image">
                        <?php else: ?>
                            <img src="/public/images/image.png" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="product-image">
                        <?php endif; ?>
                        <?php if ($product['is_featured']): ?>
                            <span class="product-badge badge-featured">⭐ Featured</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <?php if ($product['category_name']): ?>
                            <span class="product-category"><?= htmlspecialchars($product['category_name']) ?></span>
                        <?php endif; ?>
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="product-price">
                            <?php if ($product['discount_price']): ?>
                                <span class="price-original"><?= formatPrice($product['price']) ?></span>
                                <span class="price-final"><?= formatPrice($product['discount_price']) ?></span>
                            <?php else: ?>
                                <span class="price-final"><?= formatPrice($product['price']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="section-cta" data-aos="fade-up">
            <a href="/pages/all-products.php" class="btn-primary-large">
                Lihat Semua Produk
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========== SEO CONTENT: FASHION CATEGORIES ========== -->
<section class="fashion-categories-seo">
    <div class="container">
        <div class="seo-content-grid">
            <div class="seo-content-col" data-aos="fade-right">
                <h2>Fashion Wanita Terlengkap di Dorve.id</h2>
                <p>Temukan koleksi <strong>baju wanita</strong> lengkap untuk segala kebutuhan fashion Anda. Dari <strong>dress wanita elegan</strong> untuk acara formal, <strong>blouse trendy</strong> untuk ke kantor, hingga <strong>outfit casual</strong> untuk aktivitas sehari-hari. Dorve.id menyediakan berbagai pilihan <strong>fashion wanita kekinian</strong> yang selalu update mengikuti tren terbaru.</p>
                
                <p>Koleksi <strong>dress wanita</strong> kami tersedia dalam berbagai model: mini dress untuk tampilan fresh, midi dress untuk kesan elegan, hingga maxi dress untuk gaya bohemian. Semua produk <strong>baju wanita murah</strong> kami dibuat dengan material berkualitas dan jahitan rapi, memastikan kenyamanan maksimal saat dipakai.</p>
                
                <ul class="seo-list">
                    <li><strong>Dress Wanita</strong> – Mini, Midi, Maxi Dress untuk berbagai acara</li>
                    <li><strong>Blouse & Top</strong> – Atasan wanita trendy dan elegan</li>
                    <li><strong>Celana Wanita</strong> – Jeans, kulot, palazzo, celana panjang</li>
                    <li><strong>Rok Wanita</strong> – Rok pendek, rok panjang, rok A-line</li>
                    <li><strong>Outer & Jaket</strong> – Blazer, cardigan, jaket wanita</li>
                </ul>
            </div>
            
            <div class="seo-content-col" data-aos="fade-left">
                <h2>Fashion Pria & Baju Couple di Dorve.id</h2>
                <p>Dorve.id juga menawarkan <strong>fashion pria</strong> lengkap untuk pria modern. Koleksi <strong>kemeja pria</strong> formal untuk meeting atau acara penting, <strong>kaos pria keren</strong> untuk casual look, hingga <strong>hoodie pria trendy</strong> untuk streetwear style. Setiap produk dirancang dengan detail sempurna dan material premium.</p>
                
                <p>Khusus untuk pasangan, kami menghadirkan koleksi <strong>baju couple</strong> eksklusif yang matching dan stylish. Dari <strong>kaos couple keren</strong>, <strong>hoodie couple matching</strong>, hingga <strong>kemeja couple</strong> untuk acara special. Cocok juga untuk <strong>family gathering</strong> dengan koleksi baju keluarga kami.</p>
                
                <ul class="seo-list">
                    <li><strong>Kemeja Pria</strong> – Lengan panjang & pendek, formal & casual</li>
                    <li><strong>Kaos Pria</strong> – T-shirt, polo shirt dengan desain trendy</li>
                    <li><strong>Hoodie & Sweater</strong> – Streetwear pria berkualitas premium</li>
                    <li><strong>Celana Pria</strong> – Jeans, chinos, jogger pants</li>
                    <li><strong>Baju Couple</strong> – Matching outfit untuk pasangan & keluarga</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ========== NEW ARRIVALS ========== -->
<?php if (!empty($new_arrivals)): ?>
<section class="products-section" style="background: linear-gradient(135deg, #F8F9FA 0%, #FFFFFF 100%);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">Koleksi Terbaru</span>
            <h2 class="section-title">New Arrivals</h2>
            <p class="section-description">Produk fashion terbaru yang baru saja tiba di Dorve.id</p>
        </div>
        
        <div class="products-grid">
            <?php foreach ($new_arrivals as $index => $product): ?>
                <a href="/pages/product-detail.php?id=<?= $product['id'] ?>" 
                   class="product-card" 
                   data-aos="fade-up" 
                   data-aos-delay="<?= $index * 100 ?>">
                    <div class="product-image-wrapper">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="product-image">
                        <?php else: ?>
                            <img src="/public/images/image.png" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="product-image">
                        <?php endif; ?>
                        <span class="product-badge badge-new">✨ New</span>
                    </div>
                    <div class="product-info">
                        <?php if ($product['category_name']): ?>
                            <span class="product-category"><?= htmlspecialchars($product['category_name']) ?></span>
                        <?php endif; ?>
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="product-price">
                            <?php if ($product['discount_price']): ?>
                                <span class="price-original"><?= formatPrice($product['price']) ?></span>
                                <span class="price-final"><?= formatPrice($product['discount_price']) ?></span>
                            <?php else: ?>
                                <span class="price-final"><?= formatPrice($product['price']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="section-cta" data-aos="fade-up">
            <a href="/pages/all-products.php?filter=new" class="btn-primary-large">
                Lihat Semua Produk Baru
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========== WHY SHOP AT DORVE ========== -->
<section class="why-shop-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">Kenapa Dorve.id?</span>
            <h2 class="section-title">Belanja Fashion Online Aman & Nyaman</h2>
            <p class="section-description">Keunggulan berbelanja di toko fashion online terpercaya Indonesia</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card" data-aos="fade-up" data-aos-delay="0">
                <div class="feature-icon">🚚</div>
                <h3 class="feature-title">Gratis Ongkir</h3>
                <p class="feature-description">Nikmati gratis ongkir untuk pembelian di atas Rp 500.000 ke seluruh Indonesia</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-icon">💳</div>
                <h3 class="feature-title">Pembayaran Aman</h3>
                <p class="feature-description">Berbagai metode pembayaran: Transfer Bank, E-wallet, COD tersedia</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-icon">✅</div>
                <h3 class="feature-title">Produk Original</h3>
                <p class="feature-description">100% produk original dengan quality control ketat sebelum pengiriman</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-icon">🔄</div>
                <h3 class="feature-title">Easy Return</h3>
                <p class="feature-description">Kebijakan return mudah dan customer-friendly untuk kepuasan Anda</p>
            </div>
        </div>
    </div>
</section>

<!-- ========== FINAL SEO CONTENT ========== -->
<section class="final-seo-section">
    <div class="container">
        <div class="final-seo-content" data-aos="fade-up">
            <h2>Belanja Fashion Online Terlengkap & Terpercaya di Dorve.id</h2>
            <p><strong>Dorve.id</strong> adalah <strong>toko baju online terpercaya</strong> yang menjadi solusi lengkap untuk semua kebutuhan fashion Anda. Dengan koleksi yang terus diperbarui mengikuti tren fashion terkini, kami memastikan Anda selalu tampil stylish dan percaya diri di setiap kesempatan.</p>
            
            <p>Kami menyediakan berbagai kategori fashion mulai dari <strong>baju wanita</strong> (dress, blouse, celana, rok), <strong>fashion pria</strong> (kemeja, kaos, hoodie, celana), hingga <strong>baju couple</strong> untuk pasangan. Setiap produk dipilih dengan teliti dan melewati quality control untuk memastikan kualitas terbaik sampai ke tangan Anda.</p>
            
            <p>Pengalaman belanja online di Dorve.id sangat mudah dan aman. Website kami dilengkapi dengan sistem pembayaran terenkripsi, berbagai pilihan metode pembayaran (transfer bank, e-wallet, COD), dan layanan customer service yang responsif siap membantu 24/7. Pengiriman cepat ke seluruh Indonesia dengan tracking real-time memastikan Anda bisa memantau pesanan kapan saja.</p>
            
            <p>Bergabunglah dengan ribuan pelanggan puas yang telah mempercayai <strong>Dorve.id</strong> sebagai destinasi <strong>belanja baju online</strong> favorit mereka. Dapatkan promo menarik, diskon spesial, dan program reward untuk setiap pembelian. Follow social media kami untuk update produk terbaru dan penawaran eksklusif!</p>
            
            <div class="seo-keywords">
                <span class="keyword-tag">Dorve</span>
                <span class="keyword-tag">Dorve.id</span>
                <span class="keyword-tag">Toko Baju Online</span>
                <span class="keyword-tag">Fashion Indonesia</span>
                <span class="keyword-tag">Baju Wanita</span>
                <span class="keyword-tag">Baju Pria</span>
                <span class="keyword-tag">Fashion Pria</span>
                <span class="keyword-tag">Fashion Wanita</span>
                <span class="keyword-tag">Dress Wanita</span>
                <span class="keyword-tag">Kemeja Pria</span>
                <span class="keyword-tag">Baju Couple</span>
                <span class="keyword-tag">Baju Kekinian</span>
                <span class="keyword-tag">Fashion Trendy</span>
                <span class="keyword-tag">Belanja Online</span>
                <span class="keyword-tag">Toko Fashion Terpercaya</span>
            </div>
        </div>
    </div>
</section>

<!-- Popup Banner -->
<?php if ($popup_banner): ?>
<div id="bannerPopup" class="banner-popup">
    <div class="banner-popup-content">
        <button onclick="closePopup()" class="popup-close" aria-label="Close popup">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <a href="<?= htmlspecialchars($popup_banner['link_url']) ?>" onclick="closePopup()">
            <img src="<?= htmlspecialchars($popup_banner['image_url']) ?>" 
                 alt="<?= htmlspecialchars($popup_banner['title']) ?>">
        </a>
        <?php if ($popup_banner['cta_text']): ?>
            <a href="<?= htmlspecialchars($popup_banner['link_url']) ?>" 
               class="popup-cta" 
               onclick="closePopup()">
                <?= htmlspecialchars($popup_banner['cta_text']) ?>
            </a>
        <?php endif; ?>
    </div>
</div>
<script>
setTimeout(function() {
    if (!sessionStorage.getItem('popupShown')) {
        document.getElementById('bannerPopup').classList.add('show');
        document.body.style.overflow = 'hidden';
        sessionStorage.setItem('popupShown', 'true');
    }
}, 3000);

function closePopup() {
    document.getElementById('bannerPopup').classList.remove('show');
    document.body.style.overflow = 'auto';
}

document.getElementById('bannerPopup')?.addEventListener('click', function(e) {
    if (e.target === this) closePopup();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closePopup();
});
</script>
<?php endif; ?>

<script>
// Hero Slider
let currentSlide = 0;
const slides = document.querySelectorAll('.hero-slide');
const dots = document.querySelectorAll('.slider-dot');
const totalSlides = slides.length;
const progressFill = document.querySelector('.slider-progress-fill');
let sliderInterval;
const slideDuration = 6000;

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    slides[index].classList.add('active');
    if (dots[index]) dots[index].classList.add('active');
    currentSlide = index;
    
    if (progressFill) {
        progressFill.style.animation = 'none';
        setTimeout(() => {
            progressFill.style.animation = `slideProgress ${slideDuration}ms linear`;
        }, 10);
    }
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
    if (sliderInterval) clearInterval(sliderInterval);
    if (totalSlides > 1) {
        sliderInterval = setInterval(nextSlide, slideDuration);
    }
}

if (totalSlides > 1) {
    resetInterval();
}

// AOS Init
if (typeof AOS !== 'undefined') {
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });
}
</script>

<!-- AOS Animation Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
