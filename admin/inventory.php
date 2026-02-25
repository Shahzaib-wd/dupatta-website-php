<?php
require_once '../includes/config.php';
requireAdminLogin();

$conn = getDBConnection();

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stock'])) {
    $productId = intval($_POST['product_id']);
    $newStock = intval($_POST['stock_quantity']);
    
    $stmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
    $stmt->bind_param('ii', $newStock, $productId);
    
    if ($stmt->execute()) {
        setFlashMessage('success', 'Stock updated successfully');
    } else {
        setFlashMessage('error', 'Failed to update stock');
    }
    
    header('Location: inventory.php');
    exit;
}

// Get filter
$stockFilter = isset($_GET['stock']) ? sanitize($_GET['stock']) : '';

// Build query
$whereClause = 'p.is_active = 1';
if ($stockFilter == 'low') {
    $whereClause .= ' AND p.stock_quantity <= p.low_stock_threshold';
} elseif ($stockFilter == 'out') {
    $whereClause .= ' AND p.stock_quantity = 0';
}

// Get products
$products = $conn->query("SELECT p.*, c.name as category_name, pi.image_path as primary_image 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                          WHERE $whereClause
                          ORDER BY p.stock_quantity ASC");

// Get stock stats
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1")->fetch_assoc()['count'];
$lowStock = $conn->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND stock_quantity <= low_stock_threshold")->fetch_assoc()['count'];
$outOfStock = $conn->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND stock_quantity = 0")->fetch_assoc()['count'];

$pageTitle = 'Inventory';
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Inventory Management</h2>
        <p class="text-muted mb-0">Track and manage product stock</p>
    </div>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="admin-card text-center">
            <h3 class="text-primary-color mb-1"><?php echo $totalProducts; ?></h3>
            <p class="text-muted mb-0">Total Products</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card text-center">
            <h3 class="text-warning mb-1"><?php echo $lowStock; ?></h3>
            <p class="text-muted mb-0">Low Stock</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card text-center">
            <h3 class="text-danger mb-1"><?php echo $outOfStock; ?></h3>
            <p class="text-muted mb-0">Out of Stock</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="admin-card mb-4">
    <div class="btn-group">
        <a href="inventory.php" class="btn btn-<?php echo !$stockFilter ? 'primary' : 'outline-primary'; ?>">All</a>
        <a href="?stock=low" class="btn btn-<?php echo $stockFilter == 'low' ? 'primary' : 'outline-primary'; ?>">Low Stock</a>
        <a href="?stock=out" class="btn btn-<?php echo $stockFilter == 'out' ? 'primary' : 'outline-primary'; ?>">Out of Stock</a>
    </div>
</div>

<!-- Products Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Current Stock</th>
                    <th>Threshold</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
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
                        <span class="badge bg-<?php echo $product['stock_quantity'] == 0 ? 'danger' : ($product['stock_quantity'] <= $product['low_stock_threshold'] ? 'warning' : 'success'); ?>">
                            <?php echo $product['stock_quantity']; ?>
                        </span>
                    </td>
                    <td><?php echo $product['low_stock_threshold']; ?></td>
                    <td>
                        <?php if ($product['stock_quantity'] == 0): ?>
                        <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Out of Stock</span>
                        <?php elseif ($product['stock_quantity'] <= $product['low_stock_threshold']): ?>
                        <span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Low Stock</span>
                        <?php else: ?>
                        <span class="text-success"><i class="bi bi-check-circle me-1"></i>In Stock</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#stockModal<?php echo $product['id']; ?>">
                            <i class="bi bi-pencil"></i> Update
                        </button>
                    </td>
                </tr>
                
                <!-- Stock Update Modal -->
                <div class="modal fade" id="stockModal<?php echo $product['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <form method="POST" action="">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Stock</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-2"><?php echo htmlspecialchars($product['name']); ?></p>
                                    <div class="mb-3">
                                        <label class="form-label">Stock Quantity</label>
                                        <input type="number" name="stock_quantity" class="form-control" value="<?php echo $product['stock_quantity']; ?>" min="0" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="update_stock" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
