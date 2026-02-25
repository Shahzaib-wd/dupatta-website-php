/**
 * Dupatta Store - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize all components
    initCart();
    initWishlist();
    initProductGallery();
    initQuantitySelector();
    initStickyHeader();
    initSmoothScroll();
    initLazyLoading();
});

/**
 * Cart Functionality
 */
function initCart() {
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const quantity = this.dataset.quantity || 1;
            addToCart(productId, quantity);
        });
    });
    
    // Remove from cart
    document.querySelectorAll('.remove-from-cart').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const cartItemId = this.dataset.cartItemId;
            removeFromCart(cartItemId);
        });
    });
    
    // Update cart quantity
    document.querySelectorAll('.cart-quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const cartItemId = this.dataset.cartItemId;
            const quantity = parseInt(this.value);
            if (quantity > 0) {
                updateCartQuantity(cartItemId, quantity);
            }
        });
    });
}

function addToCart(productId, quantity = 1, color = null, variant = null) {
    fetch('ajax/cart-add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}&color=${color || ''}&variant=${variant || ''}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cartCount);
            updateCartTotal(data.cartTotal);
            showToast('Product added to cart!', 'success');
        } else {
            showToast(data.message || 'Failed to add product', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Something went wrong', 'error');
    });
}

function removeFromCart(cartItemId) {
    fetch('ajax/cart-remove.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `cart_item_id=${cartItemId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cartCount);
            updateCartTotal(data.cartTotal);
            
            // Remove row from table
            const row = document.querySelector(`[data-cart-row="${cartItemId}"]`);
            if (row) {
                row.remove();
            }
            
            // If cart is empty, reload page
            if (data.cartCount === 0) {
                location.reload();
            }
            
            showToast('Product removed from cart', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function updateCartQuantity(cartItemId, quantity) {
    fetch('ajax/cart-update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `cart_item_id=${cartItemId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cartCount);
            updateCartTotal(data.cartTotal);
            
            // Update item total
            const itemTotal = document.querySelector(`[data-item-total="${cartItemId}"]`);
            if (itemTotal) {
                itemTotal.textContent = data.itemTotal;
            }
            
            // Update cart summary
            if (data.cartSummary) {
                document.querySelector('.cart-subtotal').textContent = data.cartSummary.subtotal;
                document.querySelector('.cart-total').textContent = data.cartSummary.total;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function updateCartCount(count) {
    const cartCountEl = document.getElementById('cart-count');
    if (cartCountEl) {
        cartCountEl.textContent = count;
        if (count > 0) {
            cartCountEl.classList.remove('d-none');
        } else {
            cartCountEl.classList.add('d-none');
        }
    }
}

function updateCartTotal(total) {
    const cartTotalEl = document.querySelector('.cart-total-header');
    if (cartTotalEl) {
        cartTotalEl.textContent = total;
    }
}

/**
 * Wishlist Functionality
 */
function initWishlist() {
    document.querySelectorAll('.add-to-wishlist').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            toggleWishlist(productId, this);
        });
    });
}

function toggleWishlist(productId, btn) {
    fetch('ajax/wishlist-toggle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateWishlistCount(data.wishlistCount);
            
            if (data.action === 'added') {
                btn.classList.add('active');
                btn.innerHTML = '<i class="bi bi-heart-fill"></i>';
                showToast('Added to wishlist!', 'success');
            } else {
                btn.classList.remove('active');
                btn.innerHTML = '<i class="bi bi-heart"></i>';
                showToast('Removed from wishlist', 'info');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function updateWishlistCount(count) {
    const wishlistCountEl = document.getElementById('wishlist-count');
    if (wishlistCountEl) {
        wishlistCountEl.textContent = count;
        if (count > 0) {
            wishlistCountEl.classList.remove('d-none');
        } else {
            wishlistCountEl.classList.add('d-none');
        }
    }
}

/**
 * Product Gallery
 */
function initProductGallery() {
    const mainImage = document.querySelector('.product-main-image img');
    const thumbnails = document.querySelectorAll('.product-thumbnail');
    
    if (mainImage && thumbnails.length > 0) {
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', function() {
                const imageSrc = this.dataset.image;
                mainImage.src = imageSrc;
                
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
    
    // Zoom on hover
    const zoomContainer = document.querySelector('.product-main-image');
    if (zoomContainer && mainImage) {
        zoomContainer.addEventListener('mousemove', function(e) {
            const { left, top, width, height } = this.getBoundingClientRect();
            const x = (e.clientX - left) / width * 100;
            const y = (e.clientY - top) / height * 100;
            
            mainImage.style.transformOrigin = `${x}% ${y}%`;
            mainImage.style.transform = 'scale(1.5)';
        });
        
        zoomContainer.addEventListener('mouseleave', function() {
            mainImage.style.transform = 'scale(1)';
        });
    }
}

/**
 * Quantity Selector
 */
function initQuantitySelector() {
    document.querySelectorAll('.quantity-selector').forEach(selector => {
        const input = selector.querySelector('.quantity-input');
        const decreaseBtn = selector.querySelector('.quantity-decrease');
        const increaseBtn = selector.querySelector('.quantity-increase');
        
        if (decreaseBtn) {
            decreaseBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                if (value > 1) {
                    input.value = value - 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
        
        if (increaseBtn) {
            increaseBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                const max = parseInt(input.dataset.max) || 99;
                if (value < max) {
                    input.value = value + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
    });
}

/**
 * Sticky Header
 */
function initStickyHeader() {
    const header = document.querySelector('.navbar.sticky-top');
    if (header) {
        let lastScroll = 0;
        
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                header.classList.add('shadow-sm');
            } else {
                header.classList.remove('shadow-sm');
            }
            
            lastScroll = currentScroll;
        });
    }
}

/**
 * Smooth Scroll
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
}

/**
 * Lazy Loading Images
 */
function initLazyLoading() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    }
}

/**
 * Toast Notifications
 */
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add styles
    toast.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${type === 'success' ? '#27ae60' : type === 'error' ? '#eb5757' : '#2f80ed'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add toast animations
const toastStyles = document.createElement('style');
toastStyles.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .toast-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .toast-content i {
        font-size: 1.25rem;
    }
`;
document.head.appendChild(toastStyles);

/**
 * Filter Toggle (Mobile)
 */
function toggleFilters() {
    const sidebar = document.querySelector('.filter-sidebar');
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
}

/**
 * Apply Filters
 */
function applyFilters() {
    const form = document.getElementById('filter-form');
    if (form) {
        form.submit();
    }
}

/**
 * Clear Filters
 */
function clearFilters() {
    const checkboxes = document.querySelectorAll('.filter-options input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = false);
    applyFilters();
}

/**
 * Copy to Clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!', 'success');
    }).catch(() => {
        showToast('Failed to copy', 'error');
    });
}

/**
 * Print Order
 */
function printOrder(orderId) {
    window.open(`print-order.php?id=${orderId}`, '_blank');
}

/**
 * Confirm Delete
 */
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

/**
 * Preview Image Upload
 */
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Format Price
 */
function formatPrice(price) {
    return '₹ ' + parseFloat(price).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

/**
 * Debounce Function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * AJAX Search
 */
const searchProducts = debounce(function(query) {
    if (query.length < 2) {
        document.getElementById('search-results').innerHTML = '';
        return;
    }
    
    fetch(`ajax/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const resultsContainer = document.getElementById('search-results');
            if (data.products && data.products.length > 0) {
                resultsContainer.innerHTML = data.products.map(product => `
                    <a href="product.php?slug=${product.slug}" class="search-result-item">
                        <img src="${product.image}" alt="${product.name}">
                        <div>
                            <h6>${product.name}</h6>
                            <p>${formatPrice(product.price)}</p>
                        </div>
                    </a>
                `).join('');
            } else {
                resultsContainer.innerHTML = '<p class="p-3 text-muted">No products found</p>';
            }
        })
        .catch(error => {
            console.error('Search error:', error);
        });
}, 300);
