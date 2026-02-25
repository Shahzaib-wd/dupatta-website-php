<?php
require_once '../includes/config.php';
requireAdminLogin();

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("UPDATE discount_codes SET is_active = 0 WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        setFlashMessage('success', 'Discount code deleted');
    } else {
        setFlashMessage('error', 'Failed to delete');
    }
    header('Location: discounts.php');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $code = strtoupper(sanitize($_POST['code']));
    $description = sanitize($_POST['description']);
    $discountType = sanitize($_POST['discount_type']);
    $discountValue = floatval($_POST['discount_value']);
    $minOrderAmount = floatval($_POST['min_order_amount']);
    $maxUses = !empty($_POST['max_uses']) ? intval($_POST['max_uses']) : null;
    $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $endDate = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    
    if ($id) {
        $stmt = $conn->prepare("UPDATE discount_codes SET code = ?, description = ?, discount_type = ?, 
                               discount_value = ?, min_order_amount = ?, max_uses = ?, start_date = ?, end_date = ? 
                               WHERE id = ?");
        $stmt->bind_param('sssddissi', $code, $description, $discountType, $discountValue, $minOrderAmount, $maxUses, $startDate, $endDate, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO discount_codes (code, description, discount_type, discount_value, 
                               min_order_amount, max_uses, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssddiss', $code, $description, $discountType, $discountValue, $minOrderAmount, $maxUses, $startDate, $endDate);
    }
    
    if ($stmt->execute()) {
        setFlashMessage('success', $id ? 'Discount updated' : 'Discount created');
    } else {
        setFlashMessage('error', 'Failed to save: ' . $conn->error);
    }
    
    header('Location: discounts.php');
    exit;
}

// Get discount codes
$discounts = $conn->query("SELECT * FROM discount_codes WHERE is_active = 1 ORDER BY created_at DESC");

$pageTitle = 'Discount Codes';
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Discount Codes</h2>
        <p class="text-muted mb-0">Manage promotional codes</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#discountModal">
        <i class="bi bi-plus-lg me-2"></i>Add Code
    </button>
</div>

<!-- Discounts Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Min Order</th>
                    <th>Uses</th>
                    <th>Valid Period</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($discount = $discounts->fetch_assoc()): 
                    $isExpired = $discount['end_date'] && strtotime($discount['end_date']) < time();
                    $isNotStarted = $discount['start_date'] && strtotime($discount['start_date']) > time();
                    $maxReached = $discount['max_uses'] && $discount['uses_count'] >= $discount['max_uses'];
                ?>
                <tr>
                    <td>
                        <strong class="text-primary-color"><?php echo $discount['code']; ?></strong>
                        <?php if ($discount['description']): ?>
                        <p class="mb-0 small text-muted"><?php echo $discount['description']; ?></p>
                        <?php endif; ?>
                    </td>
                    <td><?php echo ucfirst($discount['discount_type']); ?></td>
                    <td>
                        <?php echo $discount['discount_type'] == 'percentage' ? $discount['discount_value'] . '%' : formatPrice($discount['discount_value']); ?>
                    </td>
                    <td><?php echo $discount['min_order_amount'] > 0 ? formatPrice($discount['min_order_amount']) : '-'; ?></td>
                    <td><?php echo $discount['uses_count']; ?><?php echo $discount['max_uses'] ? ' / ' . $discount['max_uses'] : ''; ?></td>
                    <td>
                        <?php if ($discount['start_date']): ?>
                        <?php echo date('M d', strtotime($discount['start_date'])); ?> -
                        <?php endif; ?>
                        <?php echo $discount['end_date'] ? date('M d, Y', strtotime($discount['end_date'])) : 'No expiry'; ?>
                    </td>
                    <td>
                        <?php if ($isExpired): ?>
                        <span class="badge bg-secondary">Expired</span>
                        <?php elseif ($isNotStarted): ?>
                        <span class="badge bg-info">Scheduled</span>
                        <?php elseif ($maxReached): ?>
                        <span class="badge bg-warning">Max Uses</span>
                        <?php else: ?>
                        <span class="badge bg-success">Active</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editDiscount(<?php echo htmlspecialchars(json_encode($discount)); ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="?delete=<?php echo $discount['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete()">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Discount Modal -->
<div class="modal fade" id="discountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="id" id="discount_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Discount Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Code *</label>
                        <input type="text" name="code" id="discount_code" class="form-control text-uppercase" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="discount_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Type *</label>
                            <select name="discount_type" id="discount_type" class="form-select" required>
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Value *</label>
                            <input type="number" name="discount_value" id="discount_value" class="form-control" step="0.01" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Min Order Amount</label>
                            <input type="number" name="min_order_amount" id="discount_min" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Max Uses</label>
                            <input type="number" name="max_uses" id="discount_max" class="form-control">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="discount_start" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="discount_end" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editDiscount(discount) {
    document.getElementById('discount_id').value = discount.id;
    document.getElementById('discount_code').value = discount.code;
    document.getElementById('discount_description').value = discount.description;
    document.getElementById('discount_type').value = discount.discount_type;
    document.getElementById('discount_value').value = discount.discount_value;
    document.getElementById('discount_min').value = discount.min_order_amount;
    document.getElementById('discount_max').value = discount.max_uses;
    document.getElementById('discount_start').value = discount.start_date;
    document.getElementById('discount_end').value = discount.end_date;
    document.getElementById('modalTitle').textContent = 'Edit Discount Code';
    
    new bootstrap.Modal(document.getElementById('discountModal')).show();
}

document.getElementById('discountModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('discount_id').value = '';
    document.getElementById('discount_code').value = '';
    document.getElementById('discount_description').value = '';
    document.getElementById('discount_type').value = 'percentage';
    document.getElementById('discount_value').value = '';
    document.getElementById('discount_min').value = '0';
    document.getElementById('discount_max').value = '';
    document.getElementById('discount_start').value = '';
    document.getElementById('discount_end').value = '';
    document.getElementById('modalTitle').textContent = 'Add Discount Code';
});
</script>

<?php include 'includes/footer.php'; ?>
