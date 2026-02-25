<?php
// Admin header - already logged in check done in individual files
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | Admin' : 'Admin'; ?> | <?php echo SITE_NAME; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lato:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .admin-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .admin-sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .admin-sidebar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #3D3D3D;
        }
        
        .admin-sidebar-brand span {
            color: #E8B4B8;
        }
        
        .admin-nav {
            list-style: none;
            padding: 1rem 0;
            margin: 0;
        }
        
        .admin-nav-item {
            margin-bottom: 0.25rem;
        }
        
        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            color: #6B6B6B;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .admin-nav-link:hover,
        .admin-nav-link.active {
            background-color: #F9F5F0;
            color: #E8B4B8;
            border-left-color: #E8B4B8;
        }
        
        .admin-nav-link i {
            font-size: 1.1rem;
            width: 24px;
        }
        
        .admin-main {
            margin-left: 260px;
            min-height: 100vh;
            background-color: #F9F5F0;
        }
        
        .admin-header {
            background-color: #fff;
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-content {
            padding: 2rem;
        }
        
        .admin-card {
            background-color: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .admin-table {
            width: 100%;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        .admin-table th {
            background-color: #F9F5F0;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-pending { background-color: rgba(242, 153, 74, 0.2); color: #f2994a; }
        .status-confirmed { background-color: rgba(47, 128, 237, 0.2); color: #2f80ed; }
        .status-processing { background-color: rgba(47, 128, 237, 0.2); color: #2f80ed; }
        .status-shipped { background-color: rgba(155, 89, 182, 0.2); color: #9b59b6; }
        .status-delivered { background-color: rgba(39, 174, 96, 0.2); color: #27ae60; }
        .status-cancelled { background-color: rgba(235, 87, 87, 0.2); color: #eb5757; }
        
        @media (max-width: 991.98px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="admin-body">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <a href="index.php" class="admin-sidebar-brand text-decoration-none">
                Elegance <span>Admin</span>
            </a>
        </div>
        
        <ul class="admin-nav">
            <li class="admin-nav-item">
                <a href="index.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="bi bi-grid"></i>
                    Dashboard
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="orders.php" class="admin-nav-link <?php echo strpos(basename($_SERVER['PHP_SELF']), 'order') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-bag"></i>
                    Orders
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="products.php" class="admin-nav-link <?php echo strpos(basename($_SERVER['PHP_SELF']), 'product') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-box-seam"></i>
                    Products
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="categories.php" class="admin-nav-link <?php echo strpos(basename($_SERVER['PHP_SELF']), 'categor') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-tags"></i>
                    Categories
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="inventory.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : ''; ?>">
                    <i class="bi bi-boxes"></i>
                    Inventory
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="discounts.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'discounts.php' ? 'active' : ''; ?>">
                    <i class="bi bi-tag"></i>
                    Discounts
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="customers.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i>
                    Customers
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="testimonials.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'testimonials.php' ? 'active' : ''; ?>">
                    <i class="bi bi-chat-square-quote"></i>
                    Testimonials
                </a>
            </li>
            <li class="admin-nav-item mt-4 pt-4 border-top">
                <a href="../index.php" target="_blank" class="admin-nav-link">
                    <i class="bi bi-box-arrow-up-right"></i>
                    View Website
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="logout.php" class="admin-nav-link text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </a>
            </li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <main class="admin-main">
        <!-- Header -->
        <header class="admin-header">
            <button class="btn btn-link d-lg-none" onclick="document.querySelector('.admin-sidebar').classList.toggle('show')">
                <i class="bi bi-list fs-4"></i>
            </button>
            
            <div class="d-flex align-items-center gap-3 ms-auto">
                <a href="../index.php" target="_blank" class="btn btn-sm btn-outline">
                    <i class="bi bi-globe me-1"></i> View Site
                </a>
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle text-decoration-none" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo $_SESSION['admin_name']; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>
        
        <!-- Content -->
        <div class="admin-content">
            <?php 
            $flash = getFlashMessage();
            if ($flash): 
            ?>
            <div class="alert alert-<?php echo $flash['type'] == 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
