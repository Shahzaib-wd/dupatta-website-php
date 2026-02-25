<?php
// Get site settings
$instagramUrl = getSiteSetting('instagram_url');
$facebookUrl = getSiteSetting('facebook_url');
$pinterestUrl = getSiteSetting('pinterest_url');
$contactPhone = getSiteSetting('contact_phone');
$contactEmail = getSiteSetting('contact_email');
$contactAddress = getSiteSetting('contact_address');
?>

    <!-- Footer -->
    <footer class="footer bg-white border-top">
        <!-- Newsletter Section -->
        <div class="newsletter-section py-5 bg-light">
            <div class="container">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-6">
                        <h3 class="font-playfair mb-3">Join Our Newsletter</h3>
                        <p class="text-muted mb-4">Subscribe to receive updates on new arrivals, special offers, and exclusive discounts.</p>
                        <form action="<?php echo SITE_URL; ?>/newsletter-subscribe.php" method="POST" class="newsletter-form">
                            <div class="input-group">
                                <input type="email" name="email" class="form-control" placeholder="Enter your email address" required>
                                <button type="submit" class="btn btn-primary">Subscribe</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Footer -->
        <div class="footer-main py-5">
            <div class="container">
                <div class="row g-4">
                    <!-- About Column -->
                    <div class="col-lg-4 col-md-6">
                        <div class="footer-brand mb-4">
                            <span class="brand-text">Elegance</span>
                            <span class="brand-subtext d-block">Dupatta Store</span>
                        </div>
                        <p class="text-muted mb-4">
                            Discover the finest collection of traditional and contemporary dupattas. Each piece is crafted with love and attention to detail, bringing you the essence of Indian heritage.
                        </p>
                        <div class="social-links">
                            <?php if ($instagramUrl): ?>
                            <a href="<?php echo $instagramUrl; ?>" target="_blank" class="social-link">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($facebookUrl): ?>
                            <a href="<?php echo $facebookUrl; ?>" target="_blank" class="social-link">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($pinterestUrl): ?>
                            <a href="<?php echo $pinterestUrl; ?>" target="_blank" class="social-link">
                                <i class="bi bi-pinterest"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="col-lg-2 col-md-6">
                        <h5 class="footer-title">Quick Links</h5>
                        <ul class="footer-links">
                            <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/shop.php">Shop All</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/shop.php?category=new-arrivals">New Arrivals</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/shop.php?category=bestsellers">Bestsellers</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/about.php">About Us</a></li>
                        </ul>
                    </div>
                    
                    <!-- Categories -->
                    <div class="col-lg-2 col-md-6">
                        <h5 class="footer-title">Categories</h5>
                        <ul class="footer-links">
                            <li><a href="<?php echo SITE_URL; ?>/shop.php?category=silk-dupattas">Silk Dupattas</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/shop.php?category=cotton-dupattas">Cotton Dupattas</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/shop.php?category=embroidered-dupattas">Embroidered</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/shop.php?category=bandhani-dupattas">Bandhani</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/shop.php?category=wedding-collection">Wedding Collection</a></li>
                        </ul>
                    </div>
                    
                    <!-- Contact -->
                    <div class="col-lg-4 col-md-6">
                        <h5 class="footer-title">Contact Us</h5>
                        <ul class="footer-contact">
                            <li>
                                <i class="bi bi-geo-alt"></i>
                                <span><?php echo nl2br($contactAddress); ?></span>
                            </li>
                            <li>
                                <i class="bi bi-telephone"></i>
                                <a href="tel:<?php echo str_replace(' ', '', $contactPhone); ?>"><?php echo $contactPhone; ?></a>
                            </li>
                            <li>
                                <i class="bi bi-envelope"></i>
                                <a href="mailto:<?php echo $contactEmail; ?>"><?php echo $contactEmail; ?></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="footer-bottom py-3 border-top">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-0 text-muted small">
                            &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                        </p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <ul class="footer-bottom-links">
                            <li><a href="<?php echo SITE_URL; ?>/privacy-policy.php">Privacy Policy</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/terms-conditions.php">Terms & Conditions</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/shipping-returns.php">Shipping & Returns</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Newsletter Popup (shown once per session) -->
    <?php if (!isset($_SESSION['newsletter_shown'])): ?>
    <div class="modal fade" id="newsletterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-0">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal"></button>
                    <div class="row g-0">
                        <div class="col-md-5 d-none d-md-block">
                            <div class="newsletter-popup-image h-100" style="background: linear-gradient(135deg, #f8e1e7 0%, #f5d0d9 100%);">
                                <div class="d-flex align-items-center justify-content-center h-100">
                                    <i class="bi bi-envelope-heart text-white" style="font-size: 4rem;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7 p-4 p-md-5">
                            <h4 class="font-playfair mb-2">Join Our Family</h4>
                            <p class="text-muted mb-4">Subscribe and get 10% off your first order plus exclusive access to new collections.</p>
                            <form action="<?php echo SITE_URL; ?>/newsletter-subscribe.php" method="POST">
                                <div class="mb-3">
                                    <input type="email" name="email" class="form-control" placeholder="Your email address" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Subscribe Now</button>
                            </form>
                            <p class="small text-muted mt-3 mb-0">
                                <i class="bi bi-shield-check me-1"></i> We respect your privacy. Unsubscribe anytime.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php 
    $_SESSION['newsletter_shown'] = true;
    endif; 
    ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>
    
    <!-- Newsletter Popup Script -->
    <?php if (!isset($_SESSION['newsletter_shown']) || $_SESSION['newsletter_shown'] !== true): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var newsletterModal = new bootstrap.Modal(document.getElementById('newsletterModal'));
                newsletterModal.show();
            }, 5000);
        });
    </script>
    <?php endif; ?>
</body>
</html>
