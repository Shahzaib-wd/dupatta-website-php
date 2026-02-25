<?php
require_once 'includes/header.php';

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
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
$tax = ($subtotal - $discount) * 0.05; // 5% tax
$total = $subtotal - $discount + $shipping + $tax;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $conn = getDBConnection();
    
    // Get form data
    $email = sanitize($_POST['email']);
    $firstName = sanitize($_POST['first_name']);
    $lastName = sanitize($_POST['last_name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $pincode = sanitize($_POST['pincode']);
    $country = sanitize($_POST['country']);
    $notes = sanitize($_POST['order_notes']);
    $paymentMethod = sanitize($_POST['payment_method']);
    
    // Generate order number
    $orderNumber = generateOrderNumber();
    
    // Insert order
    $orderQuery = "INSERT INTO orders (order_number, customer_email, customer_first_name, customer_last_name, 
                   customer_phone, shipping_address, shipping_city, shipping_state, shipping_pincode, 
                   shipping_country, subtotal, discount_amount, discount_code, shipping_cost, tax_amount, 
                   total_amount, status, payment_status, payment_method, notes) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?)";
    
    $discountCode = $_SESSION['coupon']['code'] ?? null;
    
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param('ssssssssssdddsddss', 
        $orderNumber, $email, $firstName, $lastName, $phone, 
        $address, $city, $state, $pincode, $country,
        $subtotal, $discount, $discountCode, $shipping, $tax, $total, $paymentMethod, $notes
    );
    
    if ($stmt->execute()) {
        $orderId = $stmt->insert_id;
        
        // Insert order items
        $itemQuery = "INSERT INTO order_items (order_id, product_id, product_name, product_image, 
                      quantity, unit_price, total_price, color, variant) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $itemStmt = $conn->prepare($itemQuery);
        
        foreach ($_SESSION['cart'] as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $itemStmt->bind_param('isssidsss', 
                $orderId, $item['product_id'], $item['name'], $item['image'],
                $item['quantity'], $item['price'], $itemTotal, $item['color'], $item['variant']
            );
            $itemStmt->execute();
            
            // Update stock quantity
            $updateStock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $updateStock->bind_param('ii', $item['quantity'], $item['product_id']);
            $updateStock->execute();
        }
        
        // Add order status history
        $historyQuery = "INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'pending', 'Order placed')";
        $historyStmt = $conn->prepare($historyQuery);
        $historyStmt->bind_param('i', $orderId);
        $historyStmt->execute();
        
        // Clear cart and coupon
        $_SESSION['cart'] = [];
        unset($_SESSION['coupon']);
        
        // Store order info for thank you page
        $_SESSION['last_order'] = [
            'order_number' => $orderNumber,
            'total' => $total
        ];
        
        // Redirect to thank you page
        header('Location: order-confirmation.php');
        exit;
    } else {
        setFlashMessage('error', 'Something went wrong. Please try again.');
    }
}

$pageTitle = 'Checkout';
$pageDescription = 'Complete your order';
?>

<!-- Checkout Header -->
<section class="shop-header">
    <div class="container">
        <h1 class="shop-title">Checkout</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="cart.php">Cart</a></li>
                <li class="breadcrumb-item active">Checkout</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Checkout Content -->
<section class="checkout-section">
    <div class="container">
        <form method="POST" action="checkout.php" class="needs-validation" novalidate>
            <div class="row">
                <!-- Billing/Shipping Details -->
                <div class="col-lg-8">
                    <div class="checkout-form mb-4">
                        <h4 class="checkout-section-title">Contact Information</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" name="phone" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="checkout-form mb-4">
                        <h4 class="checkout-section-title">Shipping Address</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address *</label>
                                <textarea name="address" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City *</label>
                                <input type="text" name="city" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State *</label>
                                <input type="text" name="state" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">PIN Code *</label>
                                <input type="text" name="pincode" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Country *</label>
                                <select name="country" class="form-select" required>
                                    <option value="India" selected>India</option>
                                    <option value="USA">United States</option>
                                    <option value="UK">United Kingdom</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Order Notes (Optional)</label>
                                <textarea name="order_notes" class="form-control" rows="3" placeholder="Special instructions for delivery..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="checkout-form">
                        <h4 class="checkout-section-title">Payment Method</h4>
                        <div class="payment-methods">
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                                <label class="form-check-label d-flex align-items-center ms-2" for="cod">
                                    <i class="bi bi-cash-stack me-3 fs-4"></i>
                                    <div>
                                        <strong>Cash on Delivery</strong>
                                        <p class="mb-0 small text-muted">Pay when you receive your order</p>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="payment_method" id="upi" value="upi">
                                <label class="form-check-label d-flex align-items-center ms-2" for="upi">
                                    <i class="bi bi-phone me-3 fs-4"></i>
                                    <div>
                                        <strong>UPI / Net Banking</strong>
                                        <p class="mb-0 small text-muted">Pay using UPI apps or net banking</p>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="payment_method" id="card" value="card">
                                <label class="form-check-label d-flex align-items-center ms-2" for="card">
                                    <i class="bi bi-credit-card me-3 fs-4"></i>
                                    <div>
                                        <strong>Credit / Debit Card</strong>
                                        <p class="mb-0 small text-muted">Pay securely with your card</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <p class="small text-muted mt-3">
                            <i class="bi bi-shield-check me-1"></i> Your payment information is secure. We do not store your card details.
                        </p>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="order-summary-checkout">
                        <h4 class="checkout-section-title">Order Summary</h4>
                        
                        <!-- Order Items -->
                        <div class="checkout-items mb-4">
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="checkout-item">
                                <div class="checkout-item-image">
                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="checkout-item-info">
                                    <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <p class="mb-0">Qty: <?php echo $item['quantity']; ?></p>
                                    <?php if ($item['color']): ?>
                                    <p class="mb-0 small">Color: <?php echo $item['color']; ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <strong><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Totals -->
                        <div class="cart-summary-row">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        
                        <?php if ($discount > 0): ?>
                        <div class="cart-summary-row text-success">
                            <span>Discount (<?php echo $_SESSION['coupon']['code']; ?>)</span>
                            <span>-<?php echo formatPrice($discount); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="cart-summary-row">
                            <span>Shipping</span>
                            <span><?php echo $shipping == 0 ? 'Free' : formatPrice($shipping); ?></span>
                        </div>
                        
                        <div class="cart-summary-row">
                            <span>Tax (5%)</span>
                            <span><?php echo formatPrice($tax); ?></span>
                        </div>
                        
                        <div class="cart-summary-row total">
                            <span>Total</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                        
                        <button type="submit" name="place_order" class="btn btn-primary w-100 btn-lg mt-3">
                            Place Order <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                        
                        <a href="cart.php" class="btn btn-outline w-100 mt-2">
                            <i class="bi bi-arrow-left me-2"></i> Back to Cart
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
