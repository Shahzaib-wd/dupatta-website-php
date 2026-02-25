<?php
require_once 'includes/header.php';

// Check if there's a recent order
if (!isset($_SESSION['last_order'])) {
    header('Location: index.php');
    exit;
}

$orderNumber = $_SESSION['last_order']['order_number'];
$orderTotal = $_SESSION['last_order']['total'];

// Clear the order from session
unset($_SESSION['last_order']);

$pageTitle = 'Order Confirmation';
$pageDescription = 'Thank you for your order';
?>

<!-- Order Confirmation -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" 
                         style="width: 100px; height: 100px; background: linear-gradient(135deg, #27ae60, #2ecc71);">
                        <i class="bi bi-check-lg text-white" style="font-size: 3rem;"></i>
                    </div>
                    <h1 class="font-playfair mb-3">Thank You for Your Order!</h1>
                    <p class="lead text-muted">Your order has been placed successfully.</p>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Order Number</p>
                                <h5 class="font-playfair text-primary-color"><?php echo $orderNumber; ?></h5>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Order Total</p>
                                <h5 class="font-playfair"><?php echo formatPrice($orderTotal); ?></h5>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Order Status</p>
                                <h5><span class="badge bg-warning">Pending</span></h5>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-start bg-light p-4 rounded mb-4">
                    <h5 class="mb-3">What happens next?</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-envelope text-primary-color me-2"></i>
                            You will receive an order confirmation email shortly.
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-box text-primary-color me-2"></i>
                            We will process and ship your order within 1-2 business days.
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-truck text-primary-color me-2"></i>
                            You will receive tracking information once your order ships.
                        </li>
                        <li>
                            <i class="bi bi-telephone text-primary-color me-2"></i>
                            For any queries, contact us at <?php echo getSiteSetting('contact_phone'); ?>
                        </li>
                    </ul>
                </div>
                
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="shop.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-shop me-2"></i> Continue Shopping
                    </a>
                    <a href="index.php" class="btn btn-outline btn-lg">
                        <i class="bi bi-house me-2"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
