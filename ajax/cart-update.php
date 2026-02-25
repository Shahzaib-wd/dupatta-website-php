<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart_item_id']) && isset($_POST['quantity'])) {
    $cartItemId = $_POST['cart_item_id'];
    $quantity = intval($_POST['quantity']);
    
    if (isset($_SESSION['cart'][$cartItemId])) {
        if ($quantity > 0) {
            $_SESSION['cart'][$cartItemId]['quantity'] = $quantity;
            $itemTotal = $_SESSION['cart'][$cartItemId]['price'] * $quantity;
            
            $response['success'] = true;
            $response['message'] = 'Cart updated';
            $response['cartCount'] = getCartCount();
            $response['cartTotal'] = formatPrice(getCartTotal());
            $response['itemTotal'] = formatPrice($itemTotal);
        } else {
            unset($_SESSION['cart'][$cartItemId]);
            $response['success'] = true;
            $response['message'] = 'Item removed';
            $response['cartCount'] = getCartCount();
            $response['cartTotal'] = formatPrice(getCartTotal());
        }
    } else {
        $response['message'] = 'Item not found in cart';
    }
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
