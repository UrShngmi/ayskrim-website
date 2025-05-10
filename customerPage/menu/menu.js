document.addEventListener("DOMContentLoaded", () => {
  // Header scroll effect removed since we're now using the navbar component

  // Mobile menu toggle functionality has been removed

  // Filter buttons functionality
  const filterButtons = document.querySelectorAll(".filter-button");

  filterButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // Remove active class from all buttons
      filterButtons.forEach((btn) => btn.classList.remove("active"));

      // Add active class to clicked button
      this.classList.add("active");

      // Get the category to filter by
      const category = this.getAttribute("data-category");
      console.log(`Filtering by: ${category}`);
      
      // Reset all hover styles to ensure clean state when filtering
      const productCards = document.querySelectorAll(".product-card");
      // Save current scroll position before filtering
      const scrollPosition = window.scrollY;
      
      productCards.forEach(card => {
        // Reset any inline styles that might interfere with transitions
        card.style.transform = "";
        card.style.boxShadow = "";
        card.style.zIndex = "";
        // Force stable positioning when filtering
        card.style.height = "";
        
        // Apply new hover classes for JavaScript-controlled hover
        card.classList.add('js-hover-ready');
        
        // Re-initialize hover functionality
        card.removeEventListener('mouseenter', handleMouseEnter);
        card.removeEventListener('mouseleave', handleMouseLeave);
        card.addEventListener('mouseenter', handleMouseEnter);
        card.addEventListener('mouseleave', handleMouseLeave);
      });
      
      // Restore scroll position to prevent jumps
      setTimeout(() => {
        window.scrollTo({
          top: scrollPosition,
          behavior: 'auto'
        });
      }, 10);
      
      // Add animation for filtering transition
      productCards.forEach(card => {
        card.classList.add("filtering");
      });
      
      // Apply filter after a short delay to allow for transition
      setTimeout(() => {
        // Show all products if "All Categories" is selected
        if (category === "All Categories") {
          productCards.forEach(card => {
            card.style.display = "block";
            // Remove filtering class after a short delay and ensure hover works
            setTimeout(() => {
              card.classList.remove("filtering");
              // Apply new JS hover approach
              card.classList.add("js-hover-ready");
              // Force consistent height
              card.style.height = card.offsetHeight + "px";
              // Re-initialize hover handlers
              card.removeEventListener('mouseenter', handleMouseEnter);
              card.removeEventListener('mouseleave', handleMouseLeave);
              card.addEventListener('mouseenter', handleMouseEnter);
              card.addEventListener('mouseleave', handleMouseLeave);
            }, 50);
          });
          return;
        }

        // Filter the products based on category
        productCards.forEach(card => {
          const cardCategory = card.getAttribute("data-category");
          if (cardCategory === category) {
            card.style.display = "block";
            // Remove filtering class after a short delay and ensure hover works
            setTimeout(() => {
              card.classList.remove("filtering");
              // Apply new JS hover approach
              card.classList.add("js-hover-ready");
              // Force consistent height
              card.style.height = card.offsetHeight + "px";
              // Re-initialize hover handlers
              card.removeEventListener('mouseenter', handleMouseEnter);
              card.removeEventListener('mouseleave', handleMouseLeave);
              card.addEventListener('mouseenter', handleMouseEnter);
              card.addEventListener('mouseleave', handleMouseLeave);
            }, 50);
          } else {
            card.style.display = "none";
            card.classList.remove("hover-ready"); // Disable hover effects for hidden cards
          }
        });
      }, 300);
    });
  });

  // Favorite button toggle
  const favoriteButtons = document.querySelectorAll(".favorite-button");

  favoriteButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      // Prevent the click from bubbling up to parent elements
      e.preventDefault();
      e.stopPropagation();

      this.classList.toggle("active");

      // Add heart pulse animation
      if (!this.classList.contains("pulse")) {
        this.classList.add("pulse");
        setTimeout(() => {
          this.classList.remove("pulse");
        }, 500);
      }

      // Toggle between regular and solid heart icon
      const icon = this.querySelector("i");
      if (icon.classList.contains("far")) {
        icon.classList.remove("far");
        icon.classList.add("fas");

        // Get product ID and add to wishlist via AJAX
        const productId = this.closest(".product-card").getAttribute("data-product-id");
        addToWishlist(productId);
      } else {
        icon.classList.remove("fas");
        icon.classList.add("far");

        // Get product ID and remove from wishlist via AJAX
        const productId = this.closest(".product-card").getAttribute("data-product-id");
        removeFromWishlist(productId);
      }
    });
  });

  // Add to cart functionality
  function addToCart(productId, productName, productPrice, productImageUrl) {
    if (window.isLoggedIn) {
      fetch('/ayskrimWebsite/api/orders/addToCart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId, quantity: 1 })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(`Added ${productName} to cart!`);
          if (window.updateCartCount) {
            window.updateCartCount();
          }
        } else {
          showNotification(data.error || 'Failed to add item to cart', 'error');
        }
      })
      .catch(() => showNotification('Failed to add item to cart', 'error'));
    } else {
      // Guest: use sessionStorage and sync to server
      let cart = JSON.parse(sessionStorage.getItem('guestCart') || '[]');
      let item = cart.find(i => i.product_id == productId);
      if (item) {
        item.quantity += 1;
      } else {
        cart.push({
          product_id: productId,
          name: productName,
          price: productPrice,
          image_url: productImageUrl,
          quantity: 1
        });
      }
      sessionStorage.setItem('guestCart', JSON.stringify(cart));
      
      // Update cart count immediately
      if (window.updateCartCount) {
        window.updateCartCount();
      }
      
      // Always sync to server after change
      // Sanitize cart: only send product_id and quantity as integers, filter invalid
      const sanitizedCart = cart
        .filter(item => item.product_id && item.quantity && Number.isInteger(Number(item.product_id)) && Number(item.quantity) > 0)
        .map(item => ({
          product_id: Number(item.product_id),
          quantity: Number(item.quantity)
        }));
      
      fetch('/ayskrimWebsite/api/orders/updateGuestCart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token: window.guestToken, cart: sanitizedCart })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('Product added to cart!', 'success');
        } else {
          showNotification(data.error || 'Failed to sync cart', 'error');
        }
      })
      .catch(() => showNotification('Failed to sync cart', 'error'));
    }
  }

  // Replace the add-to-cart button event logic:
  document.querySelectorAll('.add-to-cart-button, .add-to-cart').forEach((button) => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      const card = this.closest('.product-card') || this.closest('.menu-item');
      const productId = card.getAttribute('data-product-id');
      const productName = card.querySelector('.product-title, .menu-item-name').textContent;
      const productPrice = card.querySelector('.product-price, .menu-item-price').textContent.replace('$', '').trim();
      const productImageUrl = card.querySelector('.product-image').src;
      addToCart(productId, productName, productPrice, productImageUrl);
    });
  });

  // Utility function to show notifications
  function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
      <div class="notification-content">
        <span>${message}</span>
        <button class="notification-close"><i class="fas fa-times"></i></button>
      </div>
    `;

    // Add to the DOM
    document.body.appendChild(notification);

    // Add active class after a small delay to trigger animation
    setTimeout(() => {
      notification.classList.add('active');
    }, 10);

    // Setup close button
    const closeButton = notification.querySelector('.notification-close');
    closeButton.addEventListener('click', () => {
      notification.classList.remove('active');
      setTimeout(() => {
        notification.remove();
      }, 300);
    });

    // Auto-remove after 3 seconds
    setTimeout(() => {
      notification.classList.remove('active');
      setTimeout(() => {
        notification.remove();
      }, 300);
    }, 3000);
  };

  // Initial cart count load
  if (window.updateCartCount) {
    window.updateCartCount();
  }

  // Update cart count when storage changes
  window.addEventListener('storage', (e) => {
    if (e.key === 'guestCart' && window.updateCartCount) {
      window.updateCartCount();
    }
  });

  // Update cart count when page is shown (for browser navigation)
  window.addEventListener('pageshow', (event) => {
    if (event.persisted && window.updateCartCount) {
      window.updateCartCount();
    }
  });

  // Animation for elements as they come into view
  const animateElements = document.querySelectorAll(".animate-fade-in");

  // Simple animation on load
  animateElements.forEach((element, index) => {
    setTimeout(() => {
      element.style.opacity = "1";
      element.style.transform = "translateY(0)";
    }, 100 * index);
  });

  // More advanced animation with Intersection Observer for large pages
  if ('IntersectionObserver' in window) {
    const observerOptions = {
      root: null,
      rootMargin: '0px',
      threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);

    document.querySelectorAll('.product-card').forEach(card => {
      observer.observe(card);
    });
  }

  // Product card hover effect - we use CSS for most of it, but add extra effects here
  const productCards = document.querySelectorAll(".product-card");
  
  /**
   * HOVER ANIMATION FIX
   * 
   * This is a complete solution for the hover animation issue:
   * 1. Directly applies hover capabilities to all product cards
   * 2. Uses multiple techniques to ensure it works on first page load
   * 3. Provides fallbacks for different browser behaviors
   */
  
  let hoverInitialized = false;
  
  // Direct style injection - bypasses CSS loading issues
  function injectHoverStyles() {
    // Only inject once
    if (document.getElementById('dynamic-hover-styles')) return;
    
    const styleEl = document.createElement('style');
    styleEl.id = 'dynamic-hover-styles';
    styleEl.textContent = `
      .product-card.js-hover-ready {
        transition: transform 0.3s ease, box-shadow 0.3s ease !important;
      }
      
      .product-card.js-hover-ready:hover,
      .product-card.js-hover-active {
        transform: translateY(-8px) scale(1.02) !important;
        box-shadow: 0 20px 25px -5px rgba(249, 168, 212, 0.2), 0 10px 10px -5px rgba(249, 168, 212, 0.1) !important;
        z-index: 10 !important;
      }
    `;
    document.head.appendChild(styleEl);
  }
  
  // Direct event binding for hover behavior
  function attachHoverEvents() {
    productCards.forEach(card => {
      // Reset any inline styles that might interfere
      card.style.transform = '';
      card.style.boxShadow = '';
      card.style.zIndex = '';
      
      // Mark as hover-ready with JS-specific class
      card.classList.add('js-hover-ready');
      
      // Remove existing listeners to prevent duplicates
      card.removeEventListener('mouseenter', handleMouseEnter);
      card.removeEventListener('mouseleave', handleMouseLeave);
      
      // Add fresh event listeners
      card.addEventListener('mouseenter', handleMouseEnter);
      card.addEventListener('mouseleave', handleMouseLeave);
    });
  }
  
  // Event handler for mouse enter
  function handleMouseEnter(e) {
    if (!this.classList.contains('filtering')) {
      this.classList.add('js-hover-active');
    }
  }
  
  // Event handler for mouse leave
  function handleMouseLeave(e) {
    this.classList.remove('js-hover-active');
  }
  
  // Main hover initialization function
  function initializeHoverEffects() {
    if (hoverInitialized) return;
    
    console.log('Initializing hover effects for product cards');
    
    // Direct style injection - most reliable method
    injectHoverStyles();
    
    // Direct event binding
    attachHoverEvents();
    
    // Mark as initialized
    hoverInitialized = true;
  }
  
  // IMMEDIATE EXECUTION: First chance to initialize hover
  initializeHoverEffects();
  
  // MULTIPLE TIMING MECHANISMS: Cover all loading scenarios
  
  // When DOM is ready but before images/resources
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeHoverEffects);
  }
  
  // After a short delay (catches most timing issues)
  setTimeout(initializeHoverEffects, 50);
  
  // After all resources have loaded
  window.addEventListener('load', initializeHoverEffects);
  
  // Final fallback - after everything else
  setTimeout(initializeHoverEffects, 1000);

  productCards.forEach((card) => {
    // Make the entire card clickable
    card.addEventListener("click", function(e) {
      // Don't trigger if clicking on a button or favorite icon
      if (!e.target.closest('.add-to-cart-button') && !e.target.closest('.favorite-button')) {
        // Navigate to product detail page
        const productId = this.getAttribute("data-product-id");
        console.log(`Navigate to product detail for ID: ${productId}`);
        // Future implementation: window.location.href = `/ayskrimWebsite/customerPage/product-detail/product-detail.php?id=${productId}`;
      }
    });
  });

  // Home page specific functionality has been removed

  function loadCartCount() {
    if (window.isLoggedIn) {
      fetch('/ayskrimWebsite/api/orders/getUserCart.php')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            updateCartCount(data.total_count);
          }
        })
        .catch(() => updateCartCount(0));
    } else {
      const guestCart = JSON.parse(sessionStorage.getItem('guestCart') || '[]');
      const count = guestCart.reduce((sum, item) => sum + item.quantity, 0);
      updateCartCount(count);
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    loadCartCount();
  });
})
