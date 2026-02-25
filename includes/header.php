<?php
require_once __DIR__ . '/config.php';

// Get current page for active menu
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentPage = $currentPage ?: 'index';

// Get cart count
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : SITE_TAGLINE; ?>">
    <meta name="keywords" content="dupatta, traditional wear, silk dupatta, cotton dupatta, embroidered dupatta, Indian fashion">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lato:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ASSETS_URL; ?>/images/favicon.ico">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar bg-light py-2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <small class="text-muted">
                        <i class="bi bi-truck me-1"></i> Free shipping on orders above <?php echo CURRENCY; ?>2,000
                    </small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small class="text-muted">
                        <a href="tel:<?php echo str_replace(' ', '', getSiteSetting('contact_phone')); ?>" class="text-muted text-decoration-none me-3">
                            <i class="bi bi-telephone me-1"></i> <?php echo getSiteSetting('contact_phone'); ?>
                        </a>
                        <a href="mailto:<?php echo getSiteSetting('contact_email'); ?>" class="text-muted text-decoration-none">
                            <i class="bi bi-envelope me-1"></i> <?php echo getSiteSetting('contact_email'); ?>
                        </a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <span class="brand-text">Elegance</span>
                <span class="brand-subtext">Dupatta Store</span>
            </a>
            
            <!-- Mobile Toggle -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'index' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'shop' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/shop.php">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'new-arrivals' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/shop.php?category=new-arrivals">New Arrivals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'bestsellers' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/shop.php?category=bestsellers">Bestsellers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'about' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'contact' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/contact.php">Contact</a>
                    </li>
                </ul>
                
                <!-- Right Icons -->
                <div class="d-flex align-items-center">
                    <!-- Search -->
                    <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn-link text-dark text-decoration-none p-2">
                        <i class="bi bi-search fs-5"></i>
                    </a>
                    
                    <!-- Wishlist -->
                    <a href="<?php echo SITE_URL; ?>/wishlist.php" class="btn btn-link text-dark text-decoration-none p-2 position-relative">
                        <i class="bi bi-heart fs-5"></i>
                        <span id="wishlist-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary d-none">
                            0
                        </span>
                    </a>
                    
                    <!-- Cart -->
                    <a href="<?php echo SITE_URL; ?>/cart.php" class="btn btn-link text-dark text-decoration-none p-2 position-relative">
                        <i class="bi bi-bag fs-5"></i>
                        <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary <?php echo $cartCount > 0 ? '' : 'd-none'; ?>">
                            <?php echo $cartCount; ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php 
    $flash = getFlashMessage();
    if ($flash): 
    ?>
    <div class="alert alert-<?php echo $flash['type'] == 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show rounded-0 mb-0" role="alert">
        <div class="container">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>
