document.addEventListener("DOMContentLoaded", () => {
  // Navigation item click handler
  const navItems = document.querySelectorAll(".nav-item")
  navItems.forEach((item) => {
    item.addEventListener("click", function () {
      // Remove active class from all items
      navItems.forEach((nav) => nav.classList.remove("active"))
      // Add active class to clicked item
      this.classList.add("active")

      // In a real application, you would handle navigation here
      // For example: window.location.href = this.getAttribute('data-href');
    })
  })

  // Add to cart button animation
  const addToCartBtns = document.querySelectorAll(".add-to-cart-btn")
  addToCartBtns.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault()

      // Add a small animation
      this.classList.add("clicked")

      setTimeout(() => {
        this.classList.remove("clicked")
        // Here you would typically handle the cart functionality
      }, 300)
    })
  })

  // Notification button animation
  const notificationBtn = document.querySelector(".notification-btn")
  if (notificationBtn) {
    notificationBtn.addEventListener("click", function () {
      this.classList.add("pulse")

      setTimeout(() => {
        this.classList.remove("pulse")
        // Here you would typically show notifications
      }, 300)
    })
  }

  // Add smooth scrolling for better UX
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()

      const targetId = this.getAttribute("href")
      if (targetId === "#") return

      const targetElement = document.querySelector(targetId)
      if (targetElement) {
        targetElement.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })

  // Enhanced transitions for dashboard elements
  function enhanceTransitions() {
    // Apply staggered animations to product cards
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach((card, index) => {
      card.style.animationDelay = `${0.1 * (index + 1)}s`;
      card.style.opacity = '0';
      card.style.animation = 'scaleIn 0.3s ease forwards';
    });

    // Apply animations to coupons with staggered delay
    const coupons = document.querySelectorAll('.coupon');
    coupons.forEach((coupon, index) => {
      coupon.style.animationDelay = `${0.1 * (index + 1) + 0.5}s`;
      coupon.style.opacity = '0';
      coupon.style.animation = 'slideInUp 0.3s ease forwards';
    });
    
    // Enhance hover interactions for a more fluid experience
    const interactiveElements = document.querySelectorAll('.card, .product-card, .coupon, .review-card, .nav-item, .use-btn, .add-to-cart-btn');
    interactiveElements.forEach(el => {
      el.style.transition = 'transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease';
      
      el.addEventListener('mouseenter', function() {
        if (this.classList.contains('card') || this.classList.contains('product-card') || 
            this.classList.contains('coupon') || this.classList.contains('review-card')) {
          this.style.transform = 'translateY(-2px)';
          this.style.boxShadow = '0 6px 12px -2px rgba(0,0,0,0.12)';
        }
      });
      
      el.addEventListener('mouseleave', function() {
        if (this.classList.contains('card') || this.classList.contains('product-card') || 
            this.classList.contains('coupon') || this.classList.contains('review-card')) {
          this.style.transform = 'translateY(0)';
          this.style.boxShadow = '';
        }
      });
    });
  }

  // Call enhanced transitions
  enhanceTransitions();

  // Add some additional styles dynamically
  const style = document.createElement("style")
  style.textContent = `
        .add-to-cart-btn.clicked {
            transform: scale(0.95);
        }
        
        .notification-btn.pulse {
            animation: pulse 0.5s;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .product-card:hover .product-image {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }
        
        .product-image {
            transition: transform 0.3s ease;
        }
    `
  document.head.appendChild(style)
})
