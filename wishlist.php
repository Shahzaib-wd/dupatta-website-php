<?php
require_once 'includes/header.php';

$conn = getDBConnection();
$sessionId = session_id();

// Get wishlist items
$wishlistQuery = "SELECT w.*, p.*, pi.image_path as primary_image, c.name as category_name 
                  FROM wishlist w 
                  JOIN products p ON w.product_id = p.id 
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE w.session_id = ? AND p.is_active = 1
                  ORDER BY w.created_at DESC";

$stmt = $conn->prepare($wishlistQuery);
$stmt->bind_param('s', $sessionId);
$stmt->execute();
$wishlistItems = $stmt->get_result();

$pageTitle = 'My Wishlist';
$pageDescription = 'Your saved items';
?>

<!-- Wishlist Header -->
<section class="shop-header">
    <div class="container">
        <h1 class="shop-title">My Wishlist</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                <li class="breadcrumb-item active">Wishlist</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Wishlist Content -->
<section class="py-5">
    <div class="container">
        <?php if ($wishlistItems->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($item = $wishlistItems->fetch_assoc()): ?>
            <div class="col-6 col-lg-3">
                <div class="product-card">
                    <div class="product-image-wrapper">
                        <img src="<?php echo $item['primary_image'] ? PRODUCT_IMAGES_URL . '/' . $item['primary_image'] : 'https://images.unsplash.com/photo-1610030469983-98e37383abd9?w=400'; ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="product-image">
                        <div class="product-actions">
                            <button class="product-action-btn add-to-wishlist active" data-product-id="<?php echo $item['product_id']; ?>">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                            <button class="product-action-btn add-to-cart-btn" data-product-id="<?php echo $item['product_id']; ?>">
                                <i class="bi bi-bag"></i>
                            </button>
                            <a href="product.php?slug=<?php echo $item['slug']; ?>" class="product-action-btn">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <p class="product-category"><?php echo htmlspecialchars($item['category_name'] ?? 'Dupatta'); ?></p>
                        <h3 class="product-title">
                            <a href="product.php?slug=<?php echo $item['slug']; ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                        </h3>
                        <div class="product-price">
                            <span class="price-current"><?php echo formatPrice($item['sale_price'] ?? $item['price']); ?></span>
                            <?php if ($item['sale_price']): ?>
                            <span class="price-original"><?php echo formatPrice($item['price']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-heart fs-1 text-muted mb-3 d-block"></i>
            <h3>Your wishlist is empty</h3>
            <p class="text-muted">Save items you love to your wishlist and revisit them anytime.</p>
            <a href="shop.php" class="btn btn-primary btn-lg mt-3">
                <i class="bi bi-shop me-2"></i>Start Shopping
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
