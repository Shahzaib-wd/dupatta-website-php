<?php
require_once 'includes/header.php';

$conn = getDBConnection();

// Get product slug
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (!$slug) {
    header('Location: shop.php');
    exit;
}

// Get product details
$productQuery = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.slug = ? AND p.is_active = 1";
$stmt = $conn->prepare($productQuery);
$stmt->bind_param('s', $slug);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: shop.php');
    exit;
}

// Get product images
$imagesQuery = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order";
$stmt = $conn->prepare($imagesQuery);
$stmt->bind_param('i', $product['id']);
$stmt->execute();
$imagesResult = $stmt->get_result();
$images = [];
while ($img = $imagesResult->fetch_assoc()) {
    $images[] = $img;
}

// Get product colors
$colorsQuery = "SELECT * FROM product_colors WHERE product_id = ? ORDER BY color_name";
$stmt = $conn->prepare($colorsQuery);
$stmt->bind_param('i', $product['id']);
$stmt->execute();
$colorsResult = $stmt->get_result();
$colors = [];
while ($color = $colorsResult->fetch_assoc()) {
    $colors[] = $color;
}

// Get product variants
$variantsQuery = "SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY variant_name";
$stmt = $conn->prepare($variantsQuery);
$stmt->bind_param('i', $product['id']);
$stmt->execute();
$variantsResult = $stmt->get_result();
$variants = [];
while ($variant = $variantsResult->fetch_assoc()) {
    $variants[] = $variant;
}

// Get related products
$relatedQuery = "SELECT p.*, pi.image_path as primary_image, c.name as category_name 
                 FROM products p 
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE p.is_active = 1 AND p.id != ? AND (p.category_id = ? OR p.category_id IN 
                    (SELECT category_id FROM products WHERE id = ?))
                 ORDER BY RAND() LIMIT 4";
$stmt = $conn->prepare($relatedQuery);
$stmt->bind_param('iii', $product['id'], $product['category_id'], $product['id']);
$stmt->execute();
$relatedResult = $stmt->get_result();

// Page title
$pageTitle = $product['name'];
$pageDescription = substr(strip_tags($product['short_description'] ?? $product['description']), 0, 160);
?>

<!-- Breadcrumb -->
<section class="py-3 bg-light">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/shop.php">Shop</a></li>
                <?php if ($product['category_slug']): ?>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/shop.php?category=<?php echo $product['category_slug']; ?>"><?php echo $product['category_name']; ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Product Detail -->
<section class="product-detail-section">
    <div class="container">
        <div class="row">
            <!-- Product Gallery -->
            <div class="col-lg-6">
                <div class="product-gallery">
                    <div class="product-main-image">
                        <img src="<?php echo !empty($images) ? PRODUCT_IMAGES_URL . '/' . $images[0]['image_path'] : 'https://images.unsplash.com/photo-1610030469983-98e37383abd9?w=600'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             id="main-product-image">
                    </div>
                    <?php if (count($images) > 1): ?>
                    <div class="product-thumbnails">
                        <?php foreach ($images as $index => $image): ?>
                        <div class="product-thumbnail <?php echo $index == 0 ? 'active' : ''; ?>" 
                             data-image="<?php echo PRODUCT_IMAGES_URL . '/' . $image['image_path']; ?>">
                            <img src="<?php echo PRODUCT_IMAGES_URL . '/' . $image['image_path']; ?>" 
                                 alt="<?php echo htmlspecialchars($image['alt_text'] ?? $product['name']); ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="product-detail-info">
                    <p class="product-detail-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Dupatta'); ?></p>
                    <h1 class="product-detail-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-detail-price">
                        <span class="price-current"><?php echo formatPrice($product['sale_price'] ?? $product['price']); ?></span>
                        <?php if ($product['sale_price']): ?>
                        <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                        <span class="badge bg-danger">Save <?php echo formatPrice($product['price'] - $product['sale_price']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-detail-rating">
                        <span class="text-warning">
                            <?php for ($i = 1; i <= 5; $i++): ?>
                            <i class="bi bi-star-fill"></i>
                            <?php endfor; ?>
                        </span>
                        <span class="text-muted">(4.8) 24 Reviews</span>
                    </div>
                    
                    <div class="product-detail-description">
                        <?php echo nl2br(htmlspecialchars($product['short_description'] ?? substr($product['description'], 0, 200) . '...')); ?>
                    </div>
                    
                    <!-- Product Meta -->
                    <div class="product-meta">
                        <?php if ($product['sku']): ?>
                        <div class="product-meta-item">
                            <span class="product-meta-label">SKU:</span>
                            <span class="product-meta-value"><?php echo htmlspecialchars($product['sku']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($product['material']): ?>
                        <div class="product-meta-item">
                            <span class="product-meta-label">Material:</span>
                            <span class="product-meta-value"><?php echo htmlspecialchars($product['material']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($product['dimensions']): ?>
                        <div class="product-meta-item">
                            <span class="product-meta-label">Dimensions:</span>
                            <span class="product-meta-value"><?php echo htmlspecialchars($product['dimensions']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="product-meta-item">
                            <span class="product-meta-label">Availability:</span>
                            <span class="product-meta-value <?php echo $product['stock_quantity'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $product['stock_quantity'] > 0 ? 'In Stock (' . $product['stock_quantity'] . ' available)' : 'Out of Stock'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Color Selection -->
                    <?php if (!empty($colors)): ?>
                    <div class="product-colors">
                        <p class="product-colors-title">Select Color:</p>
                        <div class="color-options">
                            <?php foreach ($colors as $index => $color): ?>
                            <div class="color-option <?php echo $index == 0 ? 'active' : ''; ?>" 
                                 data-color="<?php echo $color['color_name']; ?>"
                                 title="<?php echo $color['color_name']; ?>"
                                 style="background-color: <?php echo $color['color_code']; ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Variant Selection -->
                    <?php if (!empty($variants)): ?>
                    <div class="product-variants mb-3">
                        <p class="fw-bold mb-2">Select Size/Variant:</p>
                        <select class="form-select" id="variant-select" style="max-width: 200px;">
                            <?php foreach ($variants as $variant): ?>
                            <option value="<?php echo $variant['id']; ?>" data-price-adjust="<?php echo $variant['price_adjustment']; ?>">
                                <?php echo htmlspecialchars($variant['variant_name']); ?>
                                <?php if ($variant['price_adjustment'] != 0): ?>
                                (<?php echo ($variant['price_adjustment'] > 0 ? '+' : '') . formatPrice($variant['price_adjustment']); ?>)
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Quantity -->
                    <div class="product-quantity">
                        <p class="fw-bold mb-2">Quantity:</p>
                        <div class="quantity-selector">
                            <button type="button" class="quantity-btn quantity-decrease">-</button>
                            <input type="number" class="quantity-input" id="product-quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                            <button type="button" class="quantity-btn quantity-increase">+</button>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="product-actions-detail">
                        <button class="btn btn-primary btn-lg add-to-cart-btn-main" 
                                data-product-id="<?php echo $product['id']; ?>"
                                <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="bi bi-bag me-2"></i>
                            <?php echo $product['stock_quantity'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                        </button>
                        <button class="btn btn-outline btn-lg add-to-wishlist" data-product-id="<?php echo $product['id']; ?>">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                    
                    <!-- Additional Info -->
                    <div class="mt-4 pt-4 border-top">
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <i class="bi bi-truck text-primary-color" style="font-size: 1.5rem;"></i>
                                <p class="small text-muted mb-0 mt-1">Free Shipping<br>over ₹2,000</p>
                            </div>
                            <div class="col-4">
                                <i class="bi bi-shield-check text-primary-color" style="font-size: 1.5rem;"></i>
                                <p class="small text-muted mb-0 mt-1">Secure<br>Payment</p>
                            </div>
                            <div class="col-4">
                                <i class="bi bi-arrow-return-left text-primary-color" style="font-size: 1.5rem;"></i>
                                <p class="small text-muted mb-0 mt-1">Easy 7-Day<br>Returns</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Tabs -->
        <div class="row mt-5">
            <div class="col-12">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">
                            Description
                        </button>
                    </li>
                    <?php if ($product['care_instructions']): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="care-tab" data-bs-toggle="tab" data-bs-target="#care" type="button" role="tab">
                            Care Instructions
                        </button>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab">
                            Shipping & Returns
                        </button>
                    </li>
                </ul>
                <div class="tab-content p-4 border border-top-0 rounded-bottom" id="productTabsContent">
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                    <?php if ($product['care_instructions']): ?>
                    <div class="tab-pane fade" id="care" role="tabpanel">
                        <?php echo nl2br(htmlspecialchars($product['care_instructions'])); ?>
                    </div>
                    <?php endif; ?>
                    <div class="tab-pane fade" id="shipping" role="tabpanel">
                        <h5>Shipping Information</h5>
                        <p>We offer free shipping on all orders above ₹2,000. Orders are typically processed within 1-2 business days and delivered within 5-7 business days.</p>
                        
                        <h5 class="mt-4">Return Policy</h5>
                        <p>We accept returns within 7 days of delivery. The item must be unused and in its original packaging. Please note that certain items such as personalized products are not eligible for return.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if ($relatedResult->num_rows > 0): ?>
<section class="related-products">
    <div class="container">
        <h2 class="section-title mb-4">You May Also Like</h2>
        <div class="row g-4">
            <?php while ($product = $relatedResult->fetch_assoc()): ?>
            <div class="col-6 col-lg-3">
                <div class="product-card">
                    <div class="product-image-wrapper">
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
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Color selection
    document.querySelectorAll('.color-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.color-option').forEach(o => o.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Main add to cart button
    const addToCartBtn = document.querySelector('.add-to-cart-btn-main');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const quantity = document.getElementById('product-quantity').value;
            const selectedColor = document.querySelector('.color-option.active');
            const color = selectedColor ? selectedColor.dataset.color : null;
            const variantSelect = document.getElementById('variant-select');
            const variant = variantSelect ? variantSelect.options[variantSelect.selectedIndex].text : null;
            
            addToCart(productId, quantity, color, variant);
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
