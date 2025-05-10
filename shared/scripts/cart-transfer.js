document.addEventListener('DOMContentLoaded', function() {
    // Check if there's a pending guest cart to merge
    const pendingCart = localStorage.getItem('pendingGuestCart');
    if (pendingCart) {
        const cart = JSON.parse(pendingCart);
        if (cart.length > 0) {
            fetch('/ayskrimWebsite/api/orders/mergeCart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart: cart })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    localStorage.removeItem('pendingGuestCart');
                    sessionStorage.removeItem('guestCart');
                    // Update cart count immediately after successful merge
                    if (window.updateCartCount) {
                        window.updateCartCount();
                    }
                }
            })
            .catch(error => console.error('Failed to merge cart:', error));
        }
    }

    // Update cart count on page load
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
});