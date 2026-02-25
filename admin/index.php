<?php
require_once '../includes/config.php';
requireAdminLogin();

$conn = getDBConnection();

// Get dashboard statistics

// Total Orders
$ordersQuery = "SELECT COUNT(*) as total FROM orders";
$totalOrders = $conn->query($ordersQuery)->fetch_assoc()['total'];

// Total Revenue
$revenueQuery = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
$totalRevenue = $conn->query($revenueQuery)->fetch_assoc()['total'] ?? 0;

// Total Products
$productsQuery = "SELECT COUNT(*) as total FROM products WHERE is_active = 1";
$totalProducts = $conn->query($productsQuery)->fetch_assoc()['total'];

// Total Customers (unique emails from orders)
$customersQuery = "SELECT COUNT(DISTINCT customer_email) as total FROM orders";
$totalCustomers = $conn->query($customersQuery)->fetch_assoc()['total'];

// Recent Orders
$recentOrdersQuery = "SELECT o.*, 
                      (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count 
                      FROM orders o 
                      ORDER BY o.created_at DESC LIMIT 5";
$recentOrders = $conn->query($recentOrdersQuery);

// Low Stock Products
$lowStockQuery = "SELECT p.*, pi.image_path as primary_image 
                  FROM products p 
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                  WHERE p.stock_quantity <= p.low_stock_threshold AND p.is_active = 1
                  ORDER BY p.stock_quantity ASC LIMIT 5";
$lowStockProducts = $conn->query($lowStockQuery);

// Monthly Sales Data (for chart)
$salesDataQuery = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as orders,
                    SUM(total_amount) as revenue
                   FROM orders 
                   WHERE status != 'cancelled' 
                   AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                   GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                   ORDER BY month";
$salesData = $conn->query($salesDataQuery);

$salesLabels = [];
$salesValues = [];
while ($row = $salesData->fetch_assoc()) {
    $salesLabels[] = date('M Y', strtotime($row['month'] . '-01'));
    $salesValues[] = $row['revenue'];
}

// Orders by Status
$statusQuery = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$statusResult = $conn->query($statusQuery);
$statusData = [];
while ($row = $statusResult->fetch_assoc()) {
    $statusData[$row['status']] = $row['count'];
}

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Dashboard</h2>
        <p class="text-muted mb-0">Welcome back, <?php echo $_SESSION['admin_name']; ?></p>
    </div>
    <div>
        <a href="products-add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Add Product
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="admin-card">
            <div class="admin-card-icon primary">
                <i class="bi bi-bag"></i>
            </div>
            <div class="admin-card-value"><?php echo number_format($totalOrders); ?></div>
            <div class="admin-card-label">Total Orders</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="admin-card">
            <div class="admin-card-icon success">
                <i class="bi bi-currency-rupee"></i>
            </div>
            <div class="admin-card-value"><?php echo formatPrice($totalRevenue); ?></div>
            <div class="admin-card-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="admin-card">
            <div class="admin-card-icon warning">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="admin-card-value"><?php echo number_format($totalProducts); ?></div>
            <div class="admin-card-label">Products</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="admin-card">
            <div class="admin-card-icon danger">
                <i class="bi bi-people"></i>
            </div>
            <div class="admin-card-value"><?php echo number_format($totalCustomers); ?></div>
            <div class="admin-card-label">Customers</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Sales Chart -->
    <div class="col-lg-8">
        <div class="admin-card">
            <h5 class="mb-4">Sales Overview</h5>
            <canvas id="salesChart" height="300"></canvas>
        </div>
    </div>
    
    <!-- Order Status -->
    <div class="col-lg-4">
        <div class="admin-card">
            <h5 class="mb-4">Order Status</h5>
            <canvas id="statusChart" height="300"></canvas>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- Recent Orders -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">Recent Orders</h5>
                <a href="orders.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $recentOrders->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <a href="orders-view.php?id=<?php echo $order['id']; ?>" class="fw-bold">
                                    <?php echo $order['order_number']; ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($order['customer_first_name'] . ' ' . $order['customer_last_name']); ?></td>
                            <td><?php echo $order['item_count']; ?></td>
                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">Low Stock Alert</h5>
                <a href="inventory.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <?php if ($lowStockProducts->num_rows > 0): ?>
            <div class="list-group list-group-flush">
                <?php while ($product = $lowStockProducts->fetch_assoc()): ?>
                <div class="list-group-item px-0 d-flex align-items-center">
                    <img src="<?php echo $product['primary_image'] ? '../assets/images/products/' . $product['primary_image'] : 'https://via.placeholder.com/50'; ?>" 
                         alt="" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                    <div class="ms-3 flex-grow-1">
                        <h6 class="mb-0 small"><?php echo htmlspecialchars($product['name']); ?></h6>
                        <p class="mb-0 small text-danger">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Only <?php echo $product['stock_quantity']; ?> left
                        </p>
                    </div>
                    <a href="products-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-link">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <p class="text-muted text-center py-4">
                <i class="bi bi-check-circle text-success fs-1 d-block mb-2"></i>
                All products are well stocked!
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($salesLabels); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode($salesValues); ?>,
            borderColor: '#E8B4B8',
            backgroundColor: 'rgba(232, 180, 184, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_keys($statusData)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($statusData)); ?>,
            backgroundColor: [
                '#f2994a',
                '#2f80ed',
                '#9b59b6',
                '#27ae60',
                '#eb5757'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
