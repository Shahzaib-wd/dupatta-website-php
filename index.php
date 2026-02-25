<?php
require_once 'includes/header.php';

// Get featured categories
$conn = getDBConnection();
$categoriesQuery = "SELECT * FROM categories WHERE is_active = 1 AND parent_id IS NULL ORDER BY sort_order LIMIT 4";
$categoriesResult = $conn->query($categoriesQuery);

// Get featured products
$featuredQuery = "SELECT p.*, pi.image_path as primary_image, c.name as category_name 
                  FROM products p 
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.is_active = 1 AND p.is_featured = 1 
                  ORDER BY p.created_at DESC LIMIT 8";
$featuredResult = $conn->query($featuredQuery);

// Get new arrivals
$newArrivalsQuery = "SELECT p.*, pi.image_path as primary_image, c.name as category_name 
                     FROM products p 
                     LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                     LEFT JOIN categories c ON p.category_id = c.id
                     WHERE p.is_active = 1 AND p.is_new_arrival = 1 
                     ORDER BY p.created_at DESC LIMIT 4";
$newArrivalsResult = $conn->query($newArrivalsQuery);

// Get bestsellers
$bestsellersQuery = "SELECT p.*, pi.image_path as primary_image, c.name as category_name 
                     FROM products p 
                     LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                     LEFT JOIN categories c ON p.category_id = c.id
                     WHERE p.is_active = 1 AND p.is_bestseller = 1 
                     ORDER BY p.created_at DESC LIMIT 4";
$bestsellersResult = $conn->query($bestsellersQuery);

// Get testimonials
$testimonialsQuery = "SELECT * FROM testimonials WHERE is_active = 1 ORDER BY display_order LIMIT 4";
$testimonialsResult = $conn->query($testimonialsQuery);

$pageTitle = 'Home';
$pageDescription = 'Discover the finest collection of traditional and contemporary dupattas at Elegance Dupatta Store. Shop silk, cotton, embroidered, and bandhani dupattas.';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-decoration hero-decoration-1"></div>
    <div class="hero-decoration hero-decoration-2"></div>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="hero-title">
                        Wrap Yourself in <span>Elegance</span>
                    </h1>
                    <p class="hero-subtitle">
                        Discover our exquisite collection of handcrafted dupattas. Each piece tells a story of tradition, craftsmanship, and timeless beauty.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="shop.php" class="btn btn-primary btn-lg">
                            Shop Now <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                        <a href="shop.php?category=new-arrivals" class="btn btn-secondary btn-lg">
                            New Arrivals
                        </a>
                    </div>
                    <div class="mt-5 d-flex gap-4">
                        <div>
                            <h4 class="mb-0 font-playfair">500+</h4>
                            <small class="text-muted">Unique Designs</small>
                        </div>
                        <div>
                            <h4 class="mb-0 font-playfair">10K+</h4>
                            <small class="text-muted">Happy Customers</small>
                        </div>
                        <div>
                            <h4 class="mb-0 font-playfair">50+</h4>
                            <small class="text-muted">Artisan Partners</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image-wrapper">
                    <img src="assets/images/hero/hero-dupatta.png" alt="Elegant Dupatta Collection" class="hero-image img-fluid" onerror="this.src='https://images.unsplash.com/photo-1596464716127-f2a82984de30?w=600'">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories -->
<section class="category-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Shop by Category</h2>
            <p class="section-subtitle">Explore our curated collections</p>
        </div>
        <div class="row g-4">
            <?php 
            $categoryImages = [
                'new-arrivals' => 'https://images.unsplash.com/photo-1610030469983-98e37383abd9?w=400',
                'bestsellers' => 'https://images.unsplash.com/photo-1583391733952-6c78dd99de9a?w=400',
                'premium-collection' => 'https://images.unsplash.com/photo-1596464716127-f2a82984de30?w=400',
                'silk-dupattas' => 'https://images.unsplash.com/photo-1606293926075-69a00febf780?w=400'
            ];
            $categoryIndex = 0;
            while ($category = $categoriesResult->fetch_assoc()): 
                $image = $categoryImages[$category['slug']] ?? 'https://images.unsplash.com/photo-1610030469983-98e37383abd9?w=400';
            ?>
            <div class="col-6 col-lg-3">
                <a href="shop.php?category=<?php echo $category['slug']; ?>" class="text-decoration-none">
                    <div class="category-card">
                        <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                        <div class="category-card-overlay">
                            <h3 class="category-card-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="category-card-count">Shop Now</p>
                        </div>
                    </div>
                </a>
            </div>
            <?php $categoryIndex++; endwhile; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="product-section">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="section-title mb-0 text-start">Featured Collection</h2>
                <p class="section-subtitle text-start mb-0">Handpicked just for you</p>
            </div>
            <a href="shop.php" class="btn btn-outline d-none d-md-inline-block">
                View All <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-4">
            <?php while ($product = $featuredResult->fetch_assoc()): ?>
            <div class="col-6 col-lg-3">
                <div class="product-card">
                    <div class="product-image-wrapper">
                        <?php if ($product['is_new_arrival']): ?>
                        <span class="product-badge badge-new">New</span>
                        <?php elseif ($product['is_bestseller']): ?>
                        <span class="product-badge badge-bestseller">Bestseller</span>
                        <?php endif; ?>
                        <?php if ($product['sale_price']): ?>
                        <span class="product-badge badge-sale">Sale</span>
                        <?php endif; ?>
                        
                        <img src="<?php echo $product['primary_image'] ? PRODUCT_IMAGES_URL . '/' . $product['primary_image'] : 'https://images.unsplash.com/photo-1610030469983-98e37383abd9?w=400'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-image">
                        
                        <div class="product-actions">
                            <button class="product-action-btn add-to-wishlist" data-product-id="<?php echo $product['id']; ?>" title="Add to Wishlist">
                                <i class="bi bi-heart"></i>
                            </button>
                            <button class="product-action-btn add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>" title="Add to Cart">
                                <i class="bi bi-bag"></i>
                            </button>
                            <a href="product.php?slug=<?php echo $product['slug']; ?>" class="product-action-btn" title="Quick View">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <p class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Dupatta'); ?></p>
                        <h3 class="product-title">
                            <a href="product.php?slug=<?php echo $product['slug']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                        </h3>
                        <div class="product-price">
                            <span class="price-current"><?php echo formatPrice($product['sale_price'] ?? $product['price']); ?></span>
                            <?php if ($product['sale_price']): ?>
                            <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-4 d-md-none">
            <a href="shop.php" class="btn btn-outline">View All Products</a>
        </div>
    </div>
</section>

<!-- New Arrivals Banner -->
<section class="py-5" style="background: linear-gradient(135deg, #f8e1e7 0%, #f5d0d9 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <p class="text-uppercase letter-spacing-2 mb-2" style="font-size: 0.85rem; color: var(--color-text-secondary);">Just Arrived</p>
                <h2 class="font-playfair mb-3" style="font-size: 2.5rem;">Spring Collection 2024</h2>
                <p class="mb-4" style="color: var(--color-text-secondary);">Discover the latest additions to our collection. Fresh designs, vibrant colors, and exquisite craftsmanship await you.</p>
                <a href="shop.php?category=new-arrivals" class="btn btn-primary">Explore Collection</a>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0">
                <div class="row g-3">
                    <?php while ($product = $newArrivalsResult->fetch_assoc()): ?>
                    <div class="col-6">
                        <a href="product.php?slug=<?php echo $product['slug']; ?>" class="text-decoration-none">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                                <img src="<?php echo $product['primary_image'] ? PRODUCT_IMAGES_URL . '/' . $product['primary_image'] : 'https://images.unsplash.com/photo-1610030469983-98e37383abd9?w=300'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="card-img-top" style="aspect-ratio: 1; object-fit: cover;">
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-1" style="font-size: 0.9rem;"><?php echo htmlspecialchars($product['name']); ?></h6>
                                    <p class="mb-0" style="color: var(--color-accent-blush); font-weight: 600;"><?php echo formatPrice($product['price']); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bestsellers -->
<section class="category-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Customer Favorites</h2>
            <p class="section-subtitle">Our most loved dupattas</p>
        </div>
        <div class="row g-4">
            <?php while ($product = $bestsellersResult->fetch_assoc()): ?>
            <div class="col-6 col-lg-3">
                <div class="product-card">
                    <div class="product-image-wrapper">
                        <span class="product-badge badge-bestseller">Bestseller</span>
                        <img src="<?php echo $product['primary_image'] ? PRODUCT_IMAGES_URL . '/' . $product['primary_image'] : 'https://images.unsplash.com/photo-1610030469983-98e37383abd9?w=400'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-image">
                        <div class="product-actions">
                            <button class="product-action-btn add-to-wishlist" data-product-id="<?php echo $product['id']; ?>">
                                <i class="bi bi-heart"></i>
                            </button>
                            <button class="product-action-btn add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                <i class="bi bi-bag"></i>
                            </button>
                            <a href="product.php?slug=<?php echo $product['slug']; ?>" class="product-action-btn">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <p class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Dupatta'); ?></p>
                        <h3 class="product-title">
                            <a href="product.php?slug=<?php echo $product['slug']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                        </h3>
                        <div class="product-price">
                            <span class="price-current"><?php echo formatPrice($product['sale_price'] ?? $product['price']); ?></span>
                            <?php if ($product['sale_price']): ?>
                            <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Features Banner -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-6 col-lg-3">
                <div class="p-4">
                    <i class="bi bi-truck" style="font-size: 2.5rem; color: var(--color-accent-blush); margin-bottom: 1rem; display: block;"></i>
                    <h5 class="font-playfair">Free Shipping</h5>
                    <p class="text-muted small mb-0">On orders above ₹2,000</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="p-4">
                    <i class="bi bi-shield-check" style="font-size: 2.5rem; color: var(--color-accent-blush); margin-bottom: 1rem; display: block;"></i>
                    <h5 class="font-playfair">Secure Payment</h5>
                    <p class="text-muted small mb-0">100% secure checkout</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="p-4">
                    <i class="bi bi-arrow-return-left" style="font-size: 2.5rem; color: var(--color-accent-blush); margin-bottom: 1rem; display: block;"></i>
                    <h5 class="font-playfair">Easy Returns</h5>
                    <p class="text-muted small mb-0">7-day return policy</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="p-4">
                    <i class="bi bi-headset" style="font-size: 2.5rem; color: var(--color-accent-blush); margin-bottom: 1rem; display: block;"></i>
                    <h5 class="font-playfair">24/7 Support</h5>
                    <p class="text-muted small mb-0">Dedicated support team</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials-section bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">What Our Customers Say</h2>
            <p class="section-subtitle">Real stories from our happy customers</p>
        </div>
        <div class="row g-4">
            <?php while ($testimonial = $testimonialsResult->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-3">
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi bi-star<?php echo $i <= $testimonial['rating'] ? '-fill' : ''; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['testimonial']); ?>"</p>
                    <div class="testimonial-author">
                        <?php if ($testimonial['customer_image']): ?>
                        <img src="<?php echo ASSETS_URL; ?>/images/testimonials/<?php echo $testimonial['customer_image']; ?>" 
                             alt="<?php echo htmlspecialchars($testimonial['customer_name']); ?>" 
                             class="testimonial-avatar">
                        <?php else: ?>
                        <div class="testimonial-avatar d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, var(--color-accent-blush), var(--color-accent-peach)); color: white; font-size: 1.5rem;">
                            <?php echo strtoupper(substr($testimonial['customer_name'], 0, 1)); ?>
                        </div>
                        <?php endif; ?>
                        <div class="text-start">
                            <h5 class="testimonial-name mb-0"><?php echo htmlspecialchars($testimonial['customer_name']); ?></h5>
                            <p class="testimonial-role mb-0">Verified Buyer</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Instagram Feed Section -->
<section class="category-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Follow Us on Instagram</h2>
            <p class="section-subtitle">@elegancedupattastore</p>
        </div>
        <div class="row g-2">
            <?php for ($i = 1; $i <= 6; $i++): ?>
            <div class="col-4 col-lg-2">
                <a href="https://instagram.com" target="_blank" class="d-block position-relative overflow-hidden" style="border-radius: 8px; aspect-ratio: 1;">
                    <img src="https://images.unsplash.com/photo-<?php echo ['1610030469983-98e37383abd9', '1596464716127-f2a82984de30', '1583391733952-6c78dd99de9a', '1606293926075-69a00febf780', '1610030469983-98e37383abd9', '1596464716127-f2a82984de30'][$i-1]; ?>?w=300" 
                         alt="Instagram" class="w-100 h-100 object-fit-cover transition-transform" style="transition: transform 0.3s;">
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.3); opacity: 0; transition: opacity 0.3s;">
                        <i class="bi bi-instagram text-white" style="font-size: 1.5rem;"></i>
                    </div>
                </a>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<style>
.letter-spacing-2 {
    letter-spacing: 2px;
}
.object-fit-cover {
    object-fit: cover;
}
.transition-transform:hover {
    transform: scale(1.1);
}
a[style*="aspect-ratio: 1"] > div:hover {
    opacity: 1 !important;
}
</style>

<?php require_once 'includes/footer.php'; ?>
