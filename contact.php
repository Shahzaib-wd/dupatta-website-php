<?php
require_once 'includes/header.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // In a real application, you would send an email here
    // For now, we'll just show a success message
    $success = true;
}

$pageTitle = 'Contact Us';
$pageDescription = 'Get in touch with us for any queries or support.';
?>

<!-- Contact Header -->
<section class="shop-header">
    <div class="container">
        <h1 class="shop-title">Contact Us</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                <li class="breadcrumb-item active">Contact</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Contact Content -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Info -->
            <div class="col-lg-4">
                <h3 class="font-playfair mb-4">Get in Touch</h3>
                <p class="text-muted mb-4">We'd love to hear from you. Reach out to us for any queries, feedback, or just to say hello!</p>
                
                <div class="mb-4">
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-geo-alt text-primary-color fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-1">Address</h6>
                            <p class="text-muted mb-0"><?php echo nl2br(getSiteSetting('contact_address')); ?></p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-telephone text-primary-color fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-1">Phone</h6>
                            <p class="text-muted mb-0"><?php echo getSiteSetting('contact_phone'); ?></p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start">
                        <i class="bi bi-envelope text-primary-color fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-1">Email</h6>
                            <p class="text-muted mb-0"><?php echo getSiteSetting('contact_email'); ?></p>
                        </div>
                    </div>
                </div>
                
                <h6 class="mb-3">Follow Us</h6>
                <div class="social-links">
                    <a href="<?php echo getSiteSetting('instagram_url'); ?>" target="_blank" class="social-link">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="<?php echo getSiteSetting('facebook_url'); ?>" target="_blank" class="social-link">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="<?php echo getSiteSetting('pinterest_url'); ?>" target="_blank" class="social-link">
                        <i class="bi bi-pinterest"></i>
                    </a>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="font-playfair mb-4">Send us a Message</h4>
                        
                        <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>Thank you for your message! We'll get back to you soon.
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Your Name *</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Subject *</label>
                                    <input type="text" name="subject" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message *</label>
                                    <textarea name="message" class="form-control" rows="5" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-send me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
