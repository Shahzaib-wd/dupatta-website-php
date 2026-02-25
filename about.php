<?php
require_once 'includes/header.php';

$pageTitle = 'About Us';
$pageDescription = 'Learn about Elegance Dupatta Store and our journey in bringing traditional Indian craftsmanship to the world.';
?>

<!-- About Header -->
<section class="shop-header">
    <div class="container">
        <h1 class="shop-title">About Us</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                <li class="breadcrumb-item active">About</li>
            </ol>
        </nav>
    </div>
</section>

<!-- About Content -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1596464716127-f2a82984de30?w=600" alt="Our Story" class="img-fluid rounded-4 shadow">
            </div>
            <div class="col-lg-6">
                <h2 class="font-playfair mb-4">Our Story</h2>
                <p class="lead text-muted">Elegance Dupatta Store was born from a passion for preserving and celebrating the rich textile heritage of India.</p>
                <p>Founded in 2020, we started with a simple mission: to bring the finest handcrafted dupattas from skilled artisans across India to customers worldwide. Each piece in our collection tells a story of tradition, craftsmanship, and dedication.</p>
                <p>We work directly with over 50 artisan families, ensuring fair wages and sustainable practices while keeping ancient techniques alive. From the intricate Bandhani of Gujarat to the luxurious Banarasi silks, every dupatta is a labor of love.</p>
            </div>
        </div>
    </div>
</section>

<!-- Values -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="font-playfair">Our Values</h2>
            <p class="text-muted">What drives us every day</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #E8B4B8, #F5D0C5);">
                        <i class="bi bi-heart text-white fs-2"></i>
                    </div>
                    <h4 class="font-playfair">Craftsmanship</h4>
                    <p class="text-muted">We celebrate the art of handcrafting, supporting artisans who have perfected their skills over generations.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #E8B4B8, #F5D0C5);">
                        <i class="bi bi-people text-white fs-2"></i>
                    </div>
                    <h4 class="font-playfair">Community</h4>
                    <p class="text-muted">We believe in fair trade practices that empower artisan communities and preserve traditional livelihoods.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #E8B4B8, #F5D0C5);">
                        <i class="bi bi-leaf text-white fs-2"></i>
                    </div>
                    <h4 class="font-playfair">Sustainability</h4>
                    <p class="text-muted">We are committed to eco-friendly practices, using natural dyes and sustainable materials wherever possible.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="py-5">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <h2 class="font-playfair text-primary-color">500+</h2>
                <p class="text-muted">Unique Designs</p>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="font-playfair text-primary-color">50+</h2>
                <p class="text-muted">Artisan Partners</p>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="font-playfair text-primary-color">10K+</h2>
                <p class="text-muted">Happy Customers</p>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="font-playfair text-primary-color">15+</h2>
                <p class="text-muted">States Covered</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
