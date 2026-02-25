<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = sanitize($_POST['email']);
    
    $conn = getDBConnection();
    
    // Check if already subscribed
    $checkStmt = $conn->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
    $checkStmt->bind_param('s', $email);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows > 0) {
        setFlashMessage('info', 'You are already subscribed to our newsletter!');
    } else {
        $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->bind_param('s', $email);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Thank you for subscribing to our newsletter!');
        } else {
            setFlashMessage('error', 'Something went wrong. Please try again.');
        }
    }
}

header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'index.php');
exit;
?>
