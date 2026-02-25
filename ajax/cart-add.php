<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productId = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity'] ?? 1);
    $color = sanitize($_POST['color'] ?? '');
    $variant = sanitize($_POST['variant'] ?? '');
    
    // Get product details
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, name, price, sale_price, slug FROM products WHERE id = ? AND is_active = 1");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if ($product) {
        // Get product image
        $imgStmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1");
        $imgStmt->bind_param('i', $productId);
        $imgStmt->execute();
        $image = $imgStmt->get_result()->fetch_assoc();
        
        $cartKey = $productId . ($color ? '_' . $color : '') . ($variant ? '_' . $variant : '');
        
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Check if item already in cart
        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$cartKey] = [
                'product_id' => $productId,
                'name' => $product['name'],
                'price' => $product['sale_price'] ?? $product['price'],
                'slug' => $product['slug'],
                'image' => $image ? PRODUCT_IMAGES_URL . '/' . $image['image_path'] : 'https://images.unsplash.com/photo-1610030469983-98e37383abd9?w=200',
                'quantity' => $quantity,
                'color' => $color,
                'variant' => $variant
            ];
        }
        
        $response['success'] = true;
        $response['message'] = 'Product added to cart';
        $response['cartCount'] = getCartCount();
        $response['cartTotal'] = formatPrice(getCartTotal());
    } else {
        $response['message'] = 'Product not found';
    }
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
