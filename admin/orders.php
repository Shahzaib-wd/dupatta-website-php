<?php
require_once '../includes/config.php';
requireAdminLogin();

$conn = getDBConnection();

// Handle status update
if (isset($_POST['update_status'])) {
    $orderId = intval($_POST['order_id']);
    $status = sanitize($_POST['status']);
    $notes = sanitize($_POST['notes']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $orderId);
    
    if ($stmt->execute()) {
        // Add to history
        $historyStmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes, created_by) VALUES (?, ?, ?, ?)");
        $adminId = $_SESSION['admin_id'];
        $historyStmt->bind_param('issi', $orderId, $status, $notes, $adminId);
        $historyStmt->execute();
        
        setFlashMessage('success', 'Order status updated successfully');
    } else {
        setFlashMessage('error', 'Failed to update order status');
    }
    
    header('Location: orders.php');
    exit;
}

// Get filter parameters
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$dateFrom = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$whereConditions = ['1=1'];
$params = [];
$types = '';

if ($statusFilter) {
    $whereConditions[] = 'o.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

if ($dateFrom) {
    $whereConditions[] = 'DATE(o.created_at) >= ?';
    $params[] = $dateFrom;
    $types .= 's';
}

if ($dateTo) {
    $whereConditions[] = 'DATE(o.created_at) <= ?';
    $params[] = $dateTo;
    $types .= 's';
}

if ($search) {
    $whereConditions[] = '(o.order_number LIKE ? OR o.customer_email LIKE ? OR o.customer_first_name LIKE ?)';
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

$whereClause = implode(' AND ', $whereConditions);

// Get orders
$ordersQuery = "SELECT o.*, 
                (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count 
                FROM orders o 
                WHERE $whereClause
                ORDER BY o.created_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($ordersQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $orders = $stmt->get_result();
} else {
    $orders = $conn->query($ordersQuery);
}

$pageTitle = 'Orders';
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Orders</h2>
        <p class="text-muted mb-0">Manage customer orders</p>
    </div>
</div>

<!-- Filters -->
<div class="admin-card mb-4">
    <form method="GET" action="" class="row g-3">
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search orders..." value="<?php echo $search; ?>">
            </div>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" <?php echo $statusFilter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="confirmed" <?php echo $statusFilter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                <option value="processing" <?php echo $statusFilter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="shipped" <?php echo $statusFilter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                <option value="delivered" <?php echo $statusFilter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                <option value="cancelled" <?php echo $statusFilter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control" placeholder="From" value="<?php echo $dateFrom; ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="date_to" class="form-control" placeholder="To" value="<?php echo $dateTo; ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="orders.php" class="btn btn-outline">Clear</a>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders->num_rows > 0): ?>
                <?php while ($order = $orders->fetch_assoc()): ?>
                <tr>
                    <td>
                        <a href="orders-view.php?id=<?php echo $order['id']; ?>" class="fw-bold text-decoration-none">
                            <?php echo $order['order_number']; ?>
                        </a>
                    </td>
                    <td>
                        <div>
                            <strong><?php echo htmlspecialchars($order['customer_first_name'] . ' ' . $order['customer_last_name']); ?></strong>
                            <p class="mb-0 small text-muted"><?php echo $order['customer_email']; ?></p>
                        </div>
                    </td>
                    <td><?php echo $order['item_count']; ?></td>
                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : ($order['payment_status'] == 'pending' ? 'warning' : 'danger'); ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                    <td>
                        <a href="orders-view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted mb-0">No orders found</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
