<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart_item_id'])) {
    $cartItemId = $_POST['cart_item_id'];
    
    if (isset($_SESSION['cart'][$cartItemId])) {
        unset($_SESSION['cart'][$cartItemId]);
        
        $response['success'] = true;
        $response['message'] = 'Item removed from cart';
        $response['cartCount'] = getCartCount();
        $response['cartTotal'] = formatPrice(getCartTotal());
    } else {
        $response['message'] = 'Item not found in cart';
    }
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
