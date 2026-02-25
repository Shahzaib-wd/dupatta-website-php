<?php
require_once '../includes/config.php';
requireAdminLogin();

$conn = getDBConnection();

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order details
$orderQuery = "SELECT o.* FROM orders o WHERE o.id = ?";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param('i', $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Get order items
$itemsQuery = "SELECT oi.*, p.slug FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
$stmt = $conn->prepare($itemsQuery);
$stmt->bind_param('i', $orderId);
$stmt->execute();
$items = $stmt->get_result();

// Get status history
$historyQuery = "SELECT osh.*, u.first_name, u.last_name 
                 FROM order_status_history osh 
                 LEFT JOIN users u ON osh.created_by = u.id 
                 WHERE osh.order_id = ? 
                 ORDER BY osh.created_at DESC";
$stmt = $conn->prepare($historyQuery);
$stmt->bind_param('i', $orderId);
$stmt->execute();
$history = $stmt->get_result();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $newStatus = sanitize($_POST['status']);
    $notes = sanitize($_POST['notes']);
    
    $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $updateStmt->bind_param('si', $newStatus, $orderId);
    
    if ($updateStmt->execute()) {
        $historyStmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes, created_by) VALUES (?, ?, ?, ?)");
        $adminId = $_SESSION['admin_id'];
        $historyStmt->bind_param('issi', $orderId, $newStatus, $notes, $adminId);
        $historyStmt->execute();
        
        setFlashMessage('success', 'Order status updated');
        header("Location: orders-view.php?id=$orderId");
        exit;
    }
}

$pageTitle = 'Order #' . $order['order_number'];
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Order <?php echo $order['order_number']; ?></h2>
        <p class="text-muted mb-0">Placed on <?php echo date('F d, Y \a\t h:i A', strtotime($order['created_at'])); ?></p>
    </div>
    <div>
        <a href="orders.php" class="btn btn-outline">
            <i class="bi bi-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Order Details -->
    <div class="col-lg-8">
        <!-- Order Items -->
        <div class="admin-card mb-4">
            <h5 class="mb-4">Order Items</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $items->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo $item['product_image']; ?>" alt="" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                    <div class="ms-3">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                        <?php if ($item['color']): ?>
                                        <small class="text-muted">Color: <?php echo $item['color']; ?></small><br>
                                        <?php endif; ?>
                                        <?php if ($item['variant']): ?>
                                        <small class="text-muted">Variant: <?php echo $item['variant']; ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center"><?php echo formatPrice($item['unit_price']); ?></td>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td class="text-end"><?php echo formatPrice($item['total_price']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-group-divider">
                        <tr>
                            <td colspan="3" class="text-end">Subtotal:</td>
                            <td class="text-end"><?php echo formatPrice($order['subtotal']); ?></td>
                        </tr>
                        <?php if ($order['discount_amount'] > 0): ?>
                        <tr>
                            <td colspan="3" class="text-end">Discount <?php echo $order['discount_code'] ? '(' . $order['discount_code'] . ')' : ''; ?>:</td>
                            <td class="text-end text-success">-<?php echo formatPrice($order['discount_amount']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td colspan="3" class="text-end">Shipping:</td>
                            <td class="text-end"><?php echo $order['shipping_cost'] == 0 ? 'Free' : formatPrice($order['shipping_cost']); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end">Tax:</td>
                            <td class="text-end"><?php echo formatPrice($order['tax_amount']); ?></td>
                        </tr>
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end">Total:</td>
                            <td class="text-end"><?php echo formatPrice($order['total_amount']); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <!-- Status History -->
        <div class="admin-card">
            <h5 class="mb-4">Status History</h5>
            <div class="timeline">
                <?php while ($hist = $history->fetch_assoc()): ?>
                <div class="d-flex mb-3 pb-3 border-bottom">
                    <div class="flex-shrink-0">
                        <span class="badge bg-<?php 
                            echo $hist['status'] == 'pending' ? 'warning' : 
                                 ($hist['status'] == 'delivered' ? 'success' : 
                                 ($hist['status'] == 'cancelled' ? 'danger' : 'info')); 
                        ?>">
                            <?php echo ucfirst($hist['status']); ?>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1"><?php echo $hist['notes'] ?: 'Status updated'; ?></p>
                        <small class="text-muted">
                            by <?php echo $hist['first_name'] ? $hist['first_name'] . ' ' . $hist['last_name'] : 'System'; ?> 
                            on <?php echo date('M d, Y h:i A', strtotime($hist['created_at'])); ?>
                        </small>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Customer Info -->
        <div class="admin-card mb-4">
            <h5 class="mb-4">Customer Information</h5>
            <p class="mb-2"><strong><?php echo htmlspecialchars($order['customer_first_name'] . ' ' . $order['customer_last_name']); ?></strong></p>
            <p class="mb-2"><i class="bi bi-envelope me-2"></i><?php echo $order['customer_email']; ?></p>
            <p class="mb-0"><i class="bi bi-telephone me-2"></i><?php echo $order['customer_phone']; ?></p>
        </div>
        
        <!-- Shipping Address -->
        <div class="admin-card mb-4">
            <h5 class="mb-4">Shipping Address</h5>
            <p class="mb-1"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            <p class="mb-1"><?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_state']); ?></p>
            <p class="mb-1"><?php echo htmlspecialchars($order['shipping_pincode']); ?></p>
            <p class="mb-0"><?php echo htmlspecialchars($order['shipping_country']); ?></p>
        </div>
        
        <!-- Update Status -->
        <div class="admin-card mb-4">
            <h5 class="mb-4">Update Status</h5>
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Current Status</label>
                    <select name="status" class="form-select">
                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Add notes about this status change..."></textarea>
                </div>
                <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
            </form>
        </div>
        
        <!-- Payment Info -->
        <div class="admin-card">
            <h5 class="mb-4">Payment Information</h5>
            <p class="mb-2"><strong>Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
            <p class="mb-2"><strong>Status:</strong> 
                <span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                    <?php echo ucfirst($order['payment_status']); ?>
                </span>
            </p>
            <?php if ($order['notes']): ?>
            <hr>
            <p class="mb-0"><strong>Order Notes:</strong><br><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
