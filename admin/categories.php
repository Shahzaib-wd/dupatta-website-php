<?php
require_once '../includes/config.php';
requireAdminLogin();

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("UPDATE categories SET is_active = 0 WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        setFlashMessage('success', 'Category deleted successfully');
    } else {
        setFlashMessage('error', 'Failed to delete category');
    }
    header('Location: categories.php');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = sanitize($_POST['name']);
    $slug = generateSlug($name);
    $description = sanitize($_POST['description']);
    $sortOrder = intval($_POST['sort_order']);
    
    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, sort_order = ? WHERE id = ?");
        $stmt->bind_param('sssii', $name, $slug, $description, $sortOrder, $id);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, sort_order) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssi', $name, $slug, $description, $sortOrder);
    }
    
    if ($stmt->execute()) {
        setFlashMessage('success', $id ? 'Category updated' : 'Category added');
    } else {
        setFlashMessage('error', 'Failed to save category');
    }
    
    header('Location: categories.php');
    exit;
}

// Get categories
$categories = $conn->query("SELECT c.*, COUNT(p.id) as product_count 
                            FROM categories c 
                            LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
                            WHERE c.is_active = 1 
                            GROUP BY c.id 
                            ORDER BY c.sort_order, c.name");

$pageTitle = 'Categories';
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Categories</h2>
        <p class="text-muted mb-0">Manage product categories</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
        <i class="bi bi-plus-lg me-2"></i>Add Category
    </button>
</div>

<!-- Categories Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Products</th>
                    <th>Sort Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($cat['name']); ?></td>
                    <td><code><?php echo $cat['slug']; ?></code></td>
                    <td><?php echo $cat['product_count']; ?></td>
                    <td><?php echo $cat['sort_order']; ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editCategory(<?php echo htmlspecialchars(json_encode($cat)); ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="?delete=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete()">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="id" id="cat_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" id="cat_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="cat_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="cat_sort" class="form-control" value="0">
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
function editCategory(category) {
    document.getElementById('cat_id').value = category.id;
    document.getElementById('cat_name').value = category.name;
    document.getElementById('cat_description').value = category.description;
    document.getElementById('cat_sort').value = category.sort_order;
    document.getElementById('modalTitle').textContent = 'Edit Category';
    
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

document.getElementById('categoryModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('cat_id').value = '';
    document.getElementById('cat_name').value = '';
    document.getElementById('cat_description').value = '';
    document.getElementById('cat_sort').value = '0';
    document.getElementById('modalTitle').textContent = 'Add Category';
});
</script>

<?php include 'includes/footer.php'; ?>
