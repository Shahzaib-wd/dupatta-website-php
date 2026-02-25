<?php
require_once '../includes/config.php';
requireAdminLogin();

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        setFlashMessage('success', 'Product deleted successfully');
    } else {
        setFlashMessage('error', 'Failed to delete product');
    }
    header('Location: products.php');
    exit;
}

// Get filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$stock = isset($_GET['stock']) ? sanitize($_GET['stock']) : '';

// Build query
$whereConditions = ['p.is_active = 1'];
$params = [];
$types = '';

if ($search) {
    $whereConditions[] = '(p.name LIKE ? OR p.sku LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if ($category) {
    $whereConditions[] = 'p.category_id = ?';
    $params[] = $category;
    $types .= 'i';
}

if ($stock === 'low') {
    $whereConditions[] = 'p.stock_quantity <= p.low_stock_threshold';
} elseif ($stock === 'out') {
    $whereConditions[] = 'p.stock_quantity = 0';
}

$whereClause = implode(' AND ', $whereConditions);

// Get products
$productsQuery = "SELECT p.*, c.name as category_name, pi.image_path as primary_image 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                  WHERE $whereClause
                  ORDER BY p.created_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($productsQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $products = $conn->query($productsQuery);
}

// Get categories for filter
$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");

$pageTitle = 'Products';
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Products</h2>
        <p class="text-muted mb-0">Manage your product catalog</p>
    </div>
    <a href="products-add.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add Product
    </a>
</div>

<!-- Filters -->
<div class="admin-card mb-4">
    <form method="GET" action="" class="row g-3">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo $search; ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                    <?php echo $cat['name']; ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="stock" class="form-select">
                <option value="">All Stock</option>
                <option value="low" <?php echo $stock == 'low' ? 'selected' : ''; ?>>Low Stock</option>
                <option value="out" <?php echo $stock == 'out' ? 'selected' : ''; ?>>Out of Stock</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100">Filter</button>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products->num_rows > 0): ?>
                <?php while ($product = $products->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo $product['primary_image'] ? '../assets/images/products/' . $product['primary_image'] : 'https://via.placeholder.com/50'; ?>" 
                                 alt="" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            <div class="ms-3">
                                <h6 class="mb-0"><?php echo htmlspecialchars($product['name']); ?></h6>
                                <small class="text-muted">SKU: <?php echo $product['sku']; ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?php echo $product['category_name'] ?? 'Uncategorized'; ?></td>
                    <td>
                        <?php if ($product['sale_price']): ?>
                        <span class="text-decoration-line-through text-muted"><?php echo formatPrice($product['price']); ?></span><br>
                        <span class="text-success"><?php echo formatPrice($product['sale_price']); ?></span>
                        <?php else: ?>
                        <?php echo formatPrice($product['price']); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($product['stock_quantity'] == 0): ?>
                        <span class="badge bg-danger">Out of Stock</span>
                        <?php elseif ($product['stock_quantity'] <= $product['low_stock_threshold']): ?>
                        <span class="badge bg-warning"><?php echo $product['stock_quantity']; ?> left</span>
                        <?php else: ?>
                        <span class="badge bg-success"><?php echo $product['stock_quantity']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($product['is_featured']): ?>
                        <span class="badge bg-info">Featured</span>
                        <?php endif; ?>
                        <?php if ($product['is_new_arrival']): ?>
                        <span class="badge bg-primary">New</span>
                        <?php endif; ?>
                        <?php if ($product['is_bestseller']): ?>
                        <span class="badge bg-warning">Bestseller</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="products-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete()">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted mb-0">No products found</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
