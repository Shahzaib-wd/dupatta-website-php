<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'action' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
    $sessionId = session_id();
    
    $conn = getDBConnection();
    
    // Check if already in wishlist
    $checkStmt = $conn->prepare("SELECT id FROM wishlist WHERE session_id = ? AND product_id = ?");
    $checkStmt->bind_param('si', $sessionId, $productId);
    $checkStmt->execute();
    $existing = $checkStmt->get_result()->fetch_assoc();
    
    if ($existing) {
        // Remove from wishlist
        $deleteStmt = $conn->prepare("DELETE FROM wishlist WHERE session_id = ? AND product_id = ?");
        $deleteStmt->bind_param('si', $sessionId, $productId);
        $deleteStmt->execute();
        $response['action'] = 'removed';
        $response['message'] = 'Removed from wishlist';
    } else {
        // Add to wishlist
        $insertStmt = $conn->prepare("INSERT INTO wishlist (session_id, product_id) VALUES (?, ?)");
        $insertStmt->bind_param('si', $sessionId, $productId);
        $insertStmt->execute();
        $response['action'] = 'added';
        $response['message'] = 'Added to wishlist';
    }
    
    // Get wishlist count
    $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE session_id = ?");
    $countStmt->bind_param('s', $sessionId);
    $countStmt->execute();
    $countResult = $countStmt->get_result()->fetch_assoc();
    
    $response['success'] = true;
    $response['wishlistCount'] = $countResult['count'];
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
