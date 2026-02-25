<?php
require_once '../includes/config.php';

// Clear admin session
unset($_SESSION['admin_id']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);

// Redirect to login
header('Location: login.php');
exit;
?>
