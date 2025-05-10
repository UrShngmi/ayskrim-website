document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const mobileMenu = document.getElementById('mobileMenu');

    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
        });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (mobileMenu && menuToggle && !mobileMenu.contains(event.target) && !menuToggle.contains(event.target)) {
            mobileMenu.classList.remove('active');
        }
    });

    // Gallery Carousel
    const carousel = document.getElementById('galleryCarousel');
    
    if (carousel) {
        const track = carousel.querySelector('.carousel-track');
        const slides = carousel.querySelectorAll('.carousel-slide');
        const prevButton = document.getElementById('prevButton');
        const nextButton = document.getElementById('nextButton');
        
        let currentIndex = 0;
        const slidesToShow = window.innerWidth >= 768 ? 3 : 1;
        const slideCount = slides.length;
        
        // Set initial width of slides
        slides.forEach(slide => {
            slide.style.flex = `0 0 ${100 / slidesToShow}%`;
        });
        
        // Update carousel position
        function updateCarousel() {
            const slideWidth = slides[0].offsetWidth;
            track.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
        }
        
        // Handle previous button click
        if (prevButton) {
            prevButton.addEventListener('click', () => {
                currentIndex = Math.max(0, currentIndex - 1);
                updateCarousel();
            });
        }
        
        // Handle next button click
        if (nextButton) {
            nextButton.addEventListener('click', () => {
                const maxIndex = slideCount - slidesToShow;
                currentIndex = Math.min(maxIndex, currentIndex + 1);
                updateCarousel();
            });
        }
        
        // Handle window resize
        window.addEventListener('resize', () => {
            const newSlidesToShow = window.innerWidth >= 768 ? 3 : 1;
            
            if (newSlidesToShow !== slidesToShow) {
                slides.forEach(slide => {
                    slide.style.flex = `0 0 ${100 / newSlidesToShow}%`;
                });
                
                currentIndex = Math.min(currentIndex, slideCount - newSlidesToShow);
                updateCarousel();
            }
        });
    }
    
    // Booking form validation
    const bookingForm = document.getElementById('bookingForm');
    
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simple validation
            let isValid = true;
            const requiredFields = bookingForm.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });
            
            // Email validation
            const emailField = bookingForm.querySelector('input[type="email"]');
            if (emailField && emailField.value.trim()) {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(emailField.value.trim())) {
                    isValid = false;
                    emailField.classList.add('error');
                }
            }
            
            if (isValid) {
                // In a real application, you would submit the form data to the server
                alert('Booking request submitted successfully! We will contact you shortly.');
                bookingForm.reset();
            } else {
                alert('Please fill in all required fields correctly.');
            }
        });
    }
});