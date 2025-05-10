// Navbar Lottie Animation JS

document.addEventListener("DOMContentLoaded", () => {
    // Initialize Lottie animations if lottie-web is available
    try {
        if (typeof lottie !== 'undefined') {
            // Cart icon animation
            const cartIconContainer = document.getElementById('cartIconLottie');
            if (cartIconContainer) {
                // Set a background icon as fallback first
                cartIconContainer.style.backgroundImage = "url('/ayskrimWebsite/assets/images/cart-icon.svg')";
                
                // Try to load the animation
                const cartAnimation = lottie.loadAnimation({
                    container: cartIconContainer,
                    renderer: 'svg',
                    loop: false,
                    autoplay: false,
                    path: '/ayskrimWebsite/assets/images/cart.json'
                });
                
                // On successful load, clear the background icon
                cartAnimation.addEventListener('DOMLoaded', () => {
                    cartIconContainer.style.backgroundImage = 'none';
                });
                
                // Animation error handling
                cartAnimation.addEventListener('error', () => {
                    console.log('Lottie animation could not be loaded, using fallback icon');
                });
                
                // Play animation on hover
                cartIconContainer.addEventListener('mouseenter', () => {
                    cartAnimation.play();
                });
                
                cartIconContainer.addEventListener('mouseleave', () => {
                    cartAnimation.stop();
                });
            }
        }
    } catch (error) {
        console.log('Lottie not available, using static cart icon', error);
        // Ensure the fallback icon is visible if Lottie fails
        const cartIconContainer = document.getElementById('cartIconLottie');
        if (cartIconContainer) {
            cartIconContainer.style.backgroundImage = "url('/ayskrimWebsite/assets/images/cart-icon.svg')";
        }
    }
});
