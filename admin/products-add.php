<?php
require_once '../includes/config.php';
requireAdminLogin();

$conn = getDBConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $slug = generateSlug($name);
    $description = $_POST['description'];
    $shortDescription = sanitize($_POST['short_description']);
    $price = floatval($_POST['price']);
    $salePrice = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
    $sku = sanitize($_POST['sku']);
    $categoryId = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $material = sanitize($_POST['material']);
    $dimensions = sanitize($_POST['dimensions']);
    $careInstructions = $_POST['care_instructions'];
    $stockQuantity = intval($_POST['stock_quantity']);
    $lowStockThreshold = intval($_POST['low_stock_threshold']);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $isNewArrival = isset($_POST['is_new_arrival']) ? 1 : 0;
    
    // Insert product
    $productQuery = "INSERT INTO products (name, slug, description, short_description, price, sale_price, sku, 
                     category_id, material, dimensions, care_instructions, stock_quantity, low_stock_threshold,
                     is_featured, is_bestseller, is_new_arrival) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($productQuery);
    $stmt->bind_param('ssssddsisssiiiii', 
        $name, $slug, $description, $shortDescription, $price, $salePrice, $sku,
        $categoryId, $material, $dimensions, $careInstructions, $stockQuantity, $lowStockThreshold,
        $isFeatured, $isBestseller, $isNewArrival
    );
    
    if ($stmt->execute()) {
        $productId = $stmt->insert_id;
        
        // Handle primary image
        if (isset($_FILES['primary_image']) && $_FILES['primary_image']['error'] == 0) {
            $uploadDir = '../assets/images/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['primary_image']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['primary_image']['tmp_name'], $uploadFile)) {
                $imgStmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, 1)");
                $imgStmt->bind_param('is', $productId, $fileName);
                $imgStmt->execute();
            }
        }
        
        // Handle additional images
        if (isset($_FILES['additional_images'])) {
            $uploadDir = '../assets/images/products/';
            foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['additional_images']['error'][$key] == 0) {
                    $fileName = uniqid() . '_' . basename($_FILES['additional_images']['name'][$key]);
                    $uploadFile = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($tmpName, $uploadFile)) {
                        $imgStmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, 0)");
                        $imgStmt->bind_param('is', $productId, $fileName);
                        $imgStmt->execute();
                    }
                }
            }
        }
        
        // Handle colors
        if (isset($_POST['color_name'])) {
            foreach ($_POST['color_name'] as $key => $colorName) {
                if (!empty($colorName)) {
                    $colorCode = $_POST['color_code'][$key] ?? '#000000';
                    $colorStmt = $conn->prepare("INSERT INTO product_colors (product_id, color_name, color_code) VALUES (?, ?, ?)");
                    $colorStmt->bind_param('iss', $productId, $colorName, $colorCode);
                    $colorStmt->execute();
                }
            }
        }
        
        // Handle variants
        if (isset($_POST['variant_name'])) {
            foreach ($_POST['variant_name'] as $key => $variantName) {
                if (!empty($variantName)) {
                    $variantPrice = floatval($_POST['variant_price'][$key] ?? 0);
                    $variantStock = intval($_POST['variant_stock'][$key] ?? 0);
                    $variantStmt = $conn->prepare("INSERT INTO product_variants (product_id, variant_name, price_adjustment, stock_quantity) VALUES (?, ?, ?, ?)");
                    $variantStmt->bind_param('ssdi', $productId, $variantName, $variantPrice, $variantStock);
                    $variantStmt->execute();
                }
            }
        }
        
        setFlashMessage('success', 'Product added successfully');
        header('Location: products.php');
        exit;
    } else {
        setFlashMessage('error', 'Failed to add product: ' . $conn->error);
    }
}

// Get categories
$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");

$pageTitle = 'Add Product';
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Add Product</h2>
        <p class="text-muted mb-0">Create a new product</p>
    </div>
    <a href="products.php" class="btn btn-outline">
        <i class="bi bi-arrow-left me-2"></i>Back to Products
    </a>
</div>

<!-- Product Form -->
<form method="POST" action="" enctype="multipart/form-data" class="admin-form">
    <div class="row g-4">
        <!-- Basic Information -->
        <div class="col-lg-8">
            <div class="admin-card mb-4">
                <h5 class="mb-4">Basic Information</h5>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Short Description</label>
                        <textarea name="short_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Full Description</label>
                        <textarea name="description" class="form-control" rows="6"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="admin-card mb-4">
                <h5 class="mb-4">Pricing & Inventory</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Regular Price *</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="price" class="form-control" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sale Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="sale_price" class="form-control" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">SKU *</label>
                        <input type="text" name="sku" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Stock Quantity *</label>
                        <input type="number" name="stock_quantity" class="form-control" value="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Low Stock Threshold</label>
                        <input type="number" name="low_stock_threshold" class="form-control" value="5">
                    </div>
                </div>
            </div>
            
            <div class="admin-card mb-4">
                <h5 class="mb-4">Product Details</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select Category</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Material</label>
                        <input type="text" name="material" class="form-control" placeholder="e.g., Silk, Cotton">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Dimensions</label>
                        <input type="text" name="dimensions" class="form-control" placeholder="e.g., 2.5m x 1m">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Care Instructions</label>
                        <textarea name="care_instructions" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="admin-card mb-4">
                <h5 class="mb-4">Colors</h5>
                <div id="colors-container">
                    <div class="row g-2 mb-2">
                        <div class="col-5">
                            <input type="text" name="color_name[]" class="form-control" placeholder="Color Name">
                        </div>
                        <div class="col-5">
                            <input type="color" name="color_code[]" class="form-control" style="height: 38px;">
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-outline-danger w-100" onclick="this.parentElement.parentElement.remove()">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline btn-sm" id="add-color">
                    <i class="bi bi-plus me-1"></i>Add Color
                </button>
            </div>
            
            <div class="admin-card mb-4">
                <h5 class="mb-4">Variants</h5>
                <div id="variants-container">
                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <input type="text" name="variant_name[]" class="form-control" placeholder="Variant Name">
                        </div>
                        <div class="col-3">
                            <input type="number" name="variant_price[]" class="form-control" placeholder="Price Adj" step="0.01">
                        </div>
                        <div class="col-3">
                            <input type="number" name="variant_stock[]" class="form-control" placeholder="Stock">
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-outline-danger w-100" onclick="this.parentElement.parentElement.remove()">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline btn-sm" id="add-variant">
                    <i class="bi bi-plus me-1"></i>Add Variant
                </button>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="admin-card mb-4">
                <h5 class="mb-4">Product Status</h5>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                    <label class="form-check-label" for="is_featured">Featured Product</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_bestseller" id="is_bestseller">
                    <label class="form-check-label" for="is_bestseller">Bestseller</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_new_arrival" id="is_new_arrival" checked>
                    <label class="form-check-label" for="is_new_arrival">New Arrival</label>
                </div>
            </div>
            
            <div class="admin-card mb-4">
                <h5 class="mb-4">Primary Image</h5>
                <input type="file" name="primary_image" class="form-control" accept="image/*" required>
                <img id="primary-preview" class="mt-3 rounded" style="max-width: 100%; display: none;">
            </div>
            
            <div class="admin-card mb-4">
                <h5 class="mb-4">Additional Images</h5>
                <div id="additional-images">
                    <input type="file" name="additional_images[]" class="form-control mb-2" accept="image/*">
                </div>
                <button type="button" class="btn btn-outline btn-sm" id="add-more-images">
                    <i class="bi bi-plus me-1"></i>Add More
                </button>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-2"></i>Save Product
                </button>
                <a href="products.php" class="btn btn-outline">Cancel</a>
            </div>
        </div>
    </div>
</form>

<script>
document.querySelector('input[name="primary_image"]').addEventListener('change', function() {
    previewImage(this, 'primary-preview');
});
</script>

<?php include 'includes/footer.php'; ?>
