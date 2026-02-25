<?php
require_once 'includes/header.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $key => $quantity) {
            if ($quantity > 0) {
                $_SESSION['cart'][$key]['quantity'] = intval($quantity);
            } else {
                unset($_SESSION['cart'][$key]);
            }
        }
        setFlashMessage('success', 'Cart updated successfully!');
        header('Location: cart.php');
        exit;
    }
    
    if (isset($_POST['remove_item'])) {
        $key = $_POST['remove_item'];
        unset($_SESSION['cart'][$key]);
        setFlashMessage('success', 'Item removed from cart!');
        header('Location: cart.php');
        exit;
    }
    
    if (isset($_POST['apply_coupon'])) {
        $couponCode = sanitize($_POST['coupon_code']);
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("SELECT * FROM discount_codes WHERE code = ? AND is_active = 1 AND (end_date IS NULL OR end_date >= CURDATE())");
        $stmt->bind_param('s', $couponCode);
        $stmt->execute();
        $coupon = $stmt->get_result()->fetch_assoc();
        
        if ($coupon) {
            $cartTotal = getCartTotal();
            
            if ($cartTotal >= $coupon['min_order_amount']) {
                $_SESSION['coupon'] = [
                    'code' => $coupon['code'],
                    'type' => $coupon['discount_type'],
                    'value' => $coupon['discount_value']
                ];
                setFlashMessage('success', 'Coupon applied successfully!');
            } else {
                setFlashMessage('error', 'Minimum order amount of ' . formatPrice($coupon['min_order_amount']) . ' required');
            }
        } else {
            setFlashMessage('error', 'Invalid or expired coupon code');
        }
        
        header('Location: cart.php');
        exit;
    }
    
    if (isset($_POST['remove_coupon'])) {
        unset($_SESSION['coupon']);
        setFlashMessage('success', 'Coupon removed');
        header('Location: cart.php');
        exit;
    }
}

// Calculate totals
$subtotal = getCartTotal();
$discount = 0;
if (isset($_SESSION['coupon'])) {
    if ($_SESSION['coupon']['type'] == 'percentage') {
        $discount = $subtotal * ($_SESSION['coupon']['value'] / 100);
    } else {
        $discount = $_SESSION['coupon']['value'];
    }
    $discount = min($discount, $subtotal);
}
$shipping = $subtotal > 2000 ? 0 : 150;
$total = $subtotal - $discount + $shipping;

$pageTitle = 'Shopping Cart';
$pageDescription = 'Review your items and proceed to checkout';
?>

<!-- Cart Header -->
<section class="shop-header">
    <div class="container">
        <h1 class="shop-title">Shopping Cart</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                <li class="breadcrumb-item active">Cart</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Cart Content -->
<section class="cart-section">
    <div class="container">
        <?php if (!empty($_SESSION['cart'])): ?>
        <form method="POST" action="cart.php">
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="cart-table">
                        <table class="table table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['cart'] as $key => $item): 
                                    $itemTotal = $item['price'] * $item['quantity'];
                                ?>
                                <tr data-cart-row="<?php echo $key; ?>">
                                    <td>
                                        <div class="cart-product">
                                            <div class="cart-product-image">
                                                <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            </div>
                                            <div class="cart-product-info">
                                                <h5><a href="product.php?slug=<?php echo $item['slug']; ?>"><?php echo htmlspecialchars($item['name']); ?></a></h5>
                                                <?php if ($item['color']): ?>
                                                <p class="mb-0">Color: <?php echo $item['color']; ?></p>
                                                <?php endif; ?>
                                                <?php if ($item['variant']): ?>
                                                <p class="mb-0">Variant: <?php echo $item['variant']; ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php echo formatPrice($item['price']); ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="quantity-selector mx-auto" style="width: fit-content;">
                                            <button type="button" class="quantity-btn quantity-decrease">-</button>
                                            <input type="number" name="quantities[<?php echo $key; ?>]" class="quantity-input cart-quantity-input" 
                                                   value="<?php echo $item['quantity']; ?>" min="0" style="width: 50px;">
                                            <button type="button" class="quantity-btn quantity-increase">+</button>
                                        </div>
                                    </td>
                                    <td class="text-end align-middle" data-item-total="<?php echo $key; ?>">
                                        <strong><?php echo formatPrice($itemTotal); ?></strong>
                                    </td>
                                    <td class="text-end align-middle">
                                        <button type="submit" name="remove_item" value="<?php echo $key; ?>" class="cart-remove-btn">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-3">
                        <a href="shop.php" class="btn btn-outline">
                            <i class="bi bi-arrow-left me-2"></i> Continue Shopping
                        </a>
                        <button type="submit" name="update_cart" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise me-2"></i> Update Cart
                        </button>
                    </div>
                </div>
                
                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h4 class="cart-summary-title">Order Summary</h4>
                        
                        <!-- Coupon -->
                        <?php if (!isset($_SESSION['coupon'])): ?>
                        <div class="mb-4">
                            <p class="small text-muted mb-2">Have a coupon code?</p>
                            <div class="input-group">
                                <input type="text" name="coupon_code" class="form-control" placeholder="Enter code">
                                <button type="submit" name="apply_coupon" class="btn btn-outline">Apply</button>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="mb-4 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-tag-fill text-success me-2"></i> <?php echo $_SESSION['coupon']['code']; ?></span>
                                <button type="submit" name="remove_coupon" class="btn btn-sm btn-link text-danger">Remove</button>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="cart-summary-row">
                            <span>Subtotal</span>
                            <span class="cart-subtotal"><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        
                        <?php if ($discount > 0): ?>
                        <div class="cart-summary-row text-success">
                            <span>Discount</span>
                            <span>-<?php echo formatPrice($discount); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="cart-summary-row">
                            <span>Shipping</span>
                            <span><?php echo $shipping == 0 ? 'Free' : formatPrice($shipping); ?></span>
                        </div>
                        
                        <div class="cart-summary-row total">
                            <span>Total</span>
                            <span class="cart-total"><?php echo formatPrice($total); ?></span>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary w-100 btn-lg mt-3">
                            Proceed to Checkout <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                        
                        <div class="mt-3 text-center">
                            <p class="small text-muted mb-2">We accept:</p>
                            <div class="d-flex justify-content-center gap-2">
                                <i class="bi bi-credit-card fs-4 text-muted"></i>
                                <i class="bi bi-wallet2 fs-4 text-muted"></i>
                                <i class="bi bi-cash-stack fs-4 text-muted"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php else: ?>
        <!-- Empty Cart -->
        <div class="cart-empty">
            <div class="cart-empty-icon">
                <i class="bi bi-bag-x"></i>
            </div>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Looks like you haven't added anything to your cart yet.</p>
            <a href="shop.php" class="btn btn-primary btn-lg mt-3">
                <i class="bi bi-shop me-2"></i> Start Shopping
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
