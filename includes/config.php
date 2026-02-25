<?php
/**
 * Dupatta Store - Database Configuration
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'dupatta_store');

// Site configuration
define('SITE_NAME', 'Elegance Dupatta Store');
define('SITE_TAGLINE', 'Wrap Yourself in Tradition');
define('SITE_URL', 'http://localhost/dupatta-store');
define('ADMIN_URL', SITE_URL . '/admin');
define('CURRENCY', '₹');
define('CURRENCY_CODE', 'INR');

// File paths
define('ROOT_PATH', dirname(__DIR__));
define('ASSETS_URL', SITE_URL . '/assets');
define('IMAGES_URL', ASSETS_URL . '/images');
define('PRODUCT_IMAGES_URL', IMAGES_URL . '/products');
define('CATEGORY_IMAGES_URL', IMAGES_URL . '/categories');

// Upload paths
define('UPLOAD_PATH', ROOT_PATH . '/assets/images');
define('PRODUCT_UPLOAD_PATH', UPLOAD_PATH . '/products');
define('CATEGORY_UPLOAD_PATH', UPLOAD_PATH . '/categories');

// Session configuration
session_start();

// Create database connection
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            
            $conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Sorry, we're experiencing technical difficulties. Please try again later.");
        }
    }
    
    return $conn;
}

// Helper function to sanitize input
function sanitize($data) {
    $conn = getDBConnection();
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $conn->real_escape_string($data);
}

// Helper function to format price
function formatPrice($price) {
    return CURRENCY . ' ' . number_format($price, 2);
}

// Helper function to generate slug
function generateSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

// Helper function to generate unique order number
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Helper function to display flash messages
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

// Helper function to check if user is logged in as admin
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && $_SESSION['admin_role'] === 'admin';
}

// Helper function to require admin login
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

// Get cart count
function getCartCount() {
    if (isset($_SESSION['cart'])) {
        return array_sum(array_column($_SESSION['cart'], 'quantity'));
    }
    return 0;
}

// Get cart total
function getCartTotal() {
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
    }
    return $total;
}

// Get site setting
function getSiteSetting($key) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return null;
}

// Close database connection
function closeDBConnection() {
    static $conn = null;
    if ($conn !== null) {
        $conn->close();
        $conn = null;
    }
}
