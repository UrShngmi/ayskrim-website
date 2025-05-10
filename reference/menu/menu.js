document.addEventListener("DOMContentLoaded", () => {
  // Header scroll effect
  const header = document.querySelector(".site-header")

  window.addEventListener("scroll", () => {
    if (window.scrollY > 10) {
      header.classList.add("scrolled")
    } else {
      header.classList.remove("scrolled")
    }
  })

  // Mobile menu toggle
  const mobileMenuButton = document.querySelector(".mobile-menu-button")
  const closeNavButton = document.querySelector(".close-nav-button")
  const mobileNav = document.getElementById("mobileNav")

  mobileMenuButton.addEventListener("click", () => {
    mobileNav.classList.add("open")
  })

  closeNavButton.addEventListener("click", () => {
    mobileNav.classList.remove("open")
  })

  // Filter buttons
  const filterButtons = document.querySelectorAll(".filter-button")

  filterButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // Remove active class from all buttons
      filterButtons.forEach((btn) => btn.classList.remove("active"))

      // Add active class to clicked button
      this.classList.add("active")

      // Here you would typically filter the products
      const category = this.getAttribute("data-category")
      console.log(`Filtering by: ${category}`)

      // For a real implementation, you would filter the products here
      // filterProducts(category);
    })
  })

  // Favorite button toggle
  const favoriteButtons = document.querySelectorAll(".favorite-button")

  favoriteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      this.classList.toggle("active")

      // Toggle between regular and solid heart icon
      const icon = this.querySelector("i")
      if (icon.classList.contains("far")) {
        icon.classList.remove("far")
        icon.classList.add("fas")
      } else {
        icon.classList.remove("fas")
        icon.classList.add("far")
      }
    })
  })

  // Add to cart functionality
  const addToCartButtons = document.querySelectorAll(".add-to-cart-button")

  addToCartButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // Get product info from parent elements
      const card = this.closest(".product-card")
      const productName = card.querySelector(".product-title").textContent

      // Show a simple notification (in a real app, you'd update the cart)
      alert(`Added ${productName} to cart!`)

      // Animate the button
      this.classList.add("clicked")
      setTimeout(() => {
        this.classList.remove("clicked")
      }, 300)
    })
  })

  // Animation for elements as they come into view
  const animateElements = document.querySelectorAll(".animate-fade-in")

  // Simple animation on load (in a real app, you might use Intersection Observer)
  animateElements.forEach((element, index) => {
    setTimeout(() => {
      element.style.opacity = "1"
      element.style.transform = "translateY(0)"
    }, 100 * index)
  })

  // Product card hover effect
  const productCards = document.querySelectorAll(".product-card")

  productCards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-4px)"
      this.style.boxShadow = "var(--shadow-pink)"
    })

    card.addEventListener("mouseleave", function () {
      this.style.transform = ""
      this.style.boxShadow = ""
    })
  })

  // Home page specific functionality
  // Check if we're on the home page
  const isHomePage = document.querySelector(".home-page")

  if (isHomePage) {
    // Parallax effect for mountains and clouds
    window.addEventListener("scroll", () => {
      const scrollY = window.scrollY

      // Parallax for hero mountains
      const mountains = document.querySelector(".mountains-img")
      if (mountains) {
        mountains.style.transform = `translateY(${scrollY * 0.1}px)`
      }

      // Parallax for clouds
      const clouds = document.querySelectorAll(".cloud")
      clouds.forEach((cloud, index) => {
        const speed = 0.05 + index * 0.02
        cloud.style.transform = `translateY(${scrollY * speed}px)`
      })

      // Parallax for floating islands
      const islands = document.querySelectorAll(".flavor-island")
      islands.forEach((island, index) => {
        const speed = 0.03 + index * 0.01
        const baseTransform = island.style.transform || ""
        const translateY = baseTransform.includes("translateY") ? baseTransform : `translateY(${-scrollY * speed}px)`
        island.style.transform = translateY
      })
    })

    // Floating animation for decorative elements
    const decorations = document.querySelectorAll(".ice-cream-decoration")
    decorations.forEach((decoration, index) => {
      decoration.style.animation = `float ${8 + index}s infinite ease-in-out ${index * 0.5}s`
    })
  }
})
