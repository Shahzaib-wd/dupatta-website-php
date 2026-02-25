<?php
require_once 'includes/header.php';

$conn = getDBConnection();

// Get filter parameters
$categorySlug = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$colorFilter = isset($_GET['color']) ? $_GET['color'] : [];
$fabricFilter = isset($_GET['fabric']) ? $_GET['fabric'] : [];
$patternFilter = isset($_GET['pattern']) ? $_GET['pattern'] : [];
$priceMin = isset($_GET['price_min']) ? floatval($_GET['price_min']) : 0;
$priceMax = isset($_GET['price_max']) ? floatval($_GET['price_max']) : 50000;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build query
$whereConditions = ['p.is_active = 1'];
$params = [];
$types = '';

if ($categorySlug) {
    $whereConditions[] = 'c.slug = ?';
    $params[] = $categorySlug;
    $types .= 's';
}

if ($search) {
    $whereConditions[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if (!empty($colorFilter)) {
    $colorPlaceholders = implode(',', array_fill(0, count($colorFilter), '?'));
    $whereConditions[] = "pc.color_name IN ($colorPlaceholders)";
    $params = array_merge($params, $colorFilter);
    $types .= str_repeat('s', count($colorFilter));
}

if ($priceMin > 0 || $priceMax < 50000) {
    $whereConditions[] = 'p.price BETWEEN ? AND ?';
    $params[] = $priceMin;
    $params[] = $priceMax;
    $types .= 'dd';
}

$whereClause = implode(' AND ', $whereConditions);

// Sort options
$orderBy = 'p.created_at DESC';
switch ($sort) {
    case 'price_low':
        $orderBy = 'p.price ASC';
        break;
    case 'price_high':
        $orderBy = 'p.price DESC';
        break;
    case 'name':
        $orderBy = 'p.name ASC';
        break;
    case 'popular':
        $orderBy = 'p.is_bestseller DESC, p.created_at DESC';
        break;
}

// Get total count
$countQuery = "SELECT COUNT(DISTINCT p.id) as total 
               FROM products p 
               LEFT JOIN categories c ON p.category_id = c.id 
               LEFT JOIN product_colors pc ON p.id = pc.product_id
               WHERE $whereClause";

$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalResult = $countStmt->get_result()->fetch_assoc();
$totalProducts = $totalResult['total'];
$totalPages = ceil($totalProducts / $perPage);

// Get products
$productsQuery = "SELECT p.*, pi.image_path as primary_image, c.name as category_name, c.slug as category_slug
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                  LEFT JOIN product_colors pc ON p.id = pc.product_id
                  WHERE $whereClause
                  GROUP BY p.id
                  ORDER BY $orderBy
                  LIMIT ? OFFSET ?";

$productsStmt = $conn->prepare($productsQuery);
$allParams = array_merge($params, [$perPage, $offset]);
$allTypes = $types . 'ii';
$productsStmt->bind_param($allTypes, ...$allParams);
$productsStmt->execute();
$productsResult = $productsStmt->get_result();

// Get categories for filter
$categoriesQuery = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$categoriesResult = $conn->query($categoriesQuery);

// Get colors for filter
$colorsQuery = "SELECT DISTINCT color_name, color_code FROM product_colors ORDER BY color_name";
$colorsResult = $conn->query($colorsQuery);

// Get fabrics for filter (from product material)
$fabricsQuery = "SELECT DISTINCT material FROM products WHERE material IS NOT NULL AND material != '' ORDER BY material";
$fabricsResult = $conn->query($fabricsQuery);

// Page title
$pageTitle = $categorySlug ? ucwords(str_replace('-', ' ', $categorySlug)) : ($search ? "Search: $search" : 'Shop All');
$pageDescription = 'Browse our collection of beautiful dupattas. Filter by color, fabric, pattern and more.';
?>

<!-- Shop Header -->
<section class="shop-header">
    <div class="container">
        <h1 class="shop-title"><?php echo $pageTitle; ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                <li class="breadcrumb-item active"><?php echo $pageTitle; ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Shop Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4 mb-lg-0">
                <div class="filter-sidebar">
                    <div class="d-flex justify-content-between align-items-center mb-3 d-lg-none">
                        <h5 class="mb-0">Filters</h5>
                        <button class="btn btn-sm btn-outline" onclick="clearFilters()">Clear All</button>
                    </div>
                    
                    <form id="filter-form" method="GET" action="shop.php">
                        <?php if ($categorySlug): ?>
                        <input type="hidden" name="category" value="<?php echo $categorySlug; ?>">
                        <?php endif; ?>
                        <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?php echo $search; ?>">
                        <?php endif; ?>
                        
                        <!-- Categories Filter -->
                        <div class="filter-section">
                            <h6 class="filter-section-title">Categories</h6>
                            <ul class="filter-options">
                                <?php while ($cat = $categoriesResult->fetch_assoc()): ?>
                                <li>
                                    <label>
                                        <input type="radio" name="category" value="<?php echo $cat['slug']; ?>" 
                                            <?php echo $categorySlug == $cat['slug'] ? 'checked' : ''; ?>
                                            onchange="this.form.submit()">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </label>
                                </li>
                                <?php endwhile; ?>
                                <?php if ($categorySlug): ?>
                                <li>
                                    <label>
                                        <input type="radio" name="category" value="" 
                                            onchange="this.form.submit()">
                                        <em>All Categories</em>
                                    </label>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        
                        <!-- Price Filter -->
                        <div class="filter-section">
                            <h6 class="filter-section-title">Price Range</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small"><?php echo formatPrice($priceMin); ?></span>
                                    <span class="small"><?php echo formatPrice($priceMax); ?></span>
                                </div>
                                <input type="range" class="form-range" name="price_max" min="0" max="50000" 
                                       value="<?php echo $priceMax; ?>" step="500" onchange="this.form.submit()">
                            </div>
                        </div>
                        
                        <!-- Colors Filter -->
                        <div class="filter-section">
                            <h6 class="filter-section-title">Colors</h6>
                            <ul class="filter-options">
                                <?php while ($color = $colorsResult->fetch_assoc()): ?>
                                <li>
                                    <label>
                                        <input type="checkbox" name="color[]" value="<?php echo $color['color_name']; ?>"
                                            <?php echo in_array($color['color_name'], $colorFilter) ? 'checked' : ''; ?>
                                            onchange="this.form.submit()">
                                        <span class="d-inline-block rounded-circle me-2" 
                                              style="width: 16px; height: 16px; background-color: <?php echo $color['color_code']; ?>; border: 1px solid #ddd;"></span>
                                        <?php echo htmlspecialchars($color['color_name']); ?>
                                    </label>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                        
                        <!-- Fabric Filter -->
                        <div class="filter-section">
                            <h6 class="filter-section-title">Fabric</h6>
                            <ul class="filter-options">
                                <?php while ($fabric = $fabricsResult->fetch_assoc()): ?>
                                <li>
                                    <label>
                                        <input type="checkbox" name="fabric[]" value="<?php echo $fabric['material']; ?>"
                                            <?php echo in_array($fabric['material'], $fabricFilter) ? 'checked' : ''; ?>
                                            onchange="this.form.submit()">
                                        <?php echo htmlspecialchars($fabric['material']); ?>
                                    </label>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 d-lg-none">Apply Filters</button>
                    </form>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-lg-9">
                <!-- Toolbar -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <p class="mb-0 text-muted">Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + $perPage, $totalProducts); ?> of <?php echo $totalProducts; ?> products</p>
                    <div class="filter-group">
                        <label class="filter-label d-none d-md-block">Sort by:</label>
                        <select class="filter-select" name="sort" onchange="window.location.href='?<?php echo http_build_query(array_merge($_GET, ['sort' => ''])); ?>&sort=' + this.value">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                            <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                        </select>
                    </div>
                </div>
                
                <!-- Products -->
                <?php if ($productsResult->num_rows > 0): ?>
                <div class="row g-4">
                    <?php while ($product = $productsResult->fetch_assoc()): ?>
                    <div class="col-6 col-lg-4">
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
                                     class="product-image"
                                     loading="lazy">
                                
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
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-search" style="font-size: 4rem; color: var(--color-accent-blush); margin-bottom: 1rem; display: block;"></i>
                    <h4>No products found</h4>
                    <p class="text-muted">Try adjusting your filters or search criteria</p>
                    <a href="shop.php" class="btn btn-primary mt-3">View All Products</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
