$(document).ready(function() {
    // Function to animate price changes
    function animatePriceChange() {
        $('.price-animate').addClass('pulse');
        setTimeout(function() {
            $('.price-animate').removeClass('pulse');
        }, 300);
    }

    // Function to update cart totals
    function updateCartTotals() {
        let subtotal = 0;
        if (window.isLoggedIn) {
            $('.cart-item').each(function() {
                const price = parseFloat($(this).find('.product-price').text().replace('₱', ''));
                const quantity = parseInt($(this).find('.quantity').text());
                subtotal += price * quantity;
            });
        } else {
            // For guests, use the cart from sessionStorage or from the rendered DOM
            const cart = JSON.parse(sessionStorage.getItem('guestCart') || '[]');
            subtotal = cart.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
        }

        const total = subtotal;

        $('.summary-item:first .summary-value').text('₱' + subtotal.toFixed(2));
        $('.summary-total .total-value').text('₱' + total.toFixed(2));

        $('.checkout-btn').prop('disabled', subtotal === 0);
    }

    // Function to update cart count
    function updateCartCount() {
        if (window.isLoggedIn) {
            $.ajax({
                url: '/ayskrimWebsite/api/orders/getCartCount.php',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        setCartCount(response.count);
                    }
                },
                error: function() {
                    setCartCount(0);
                }
            });
        } else {
            const cart = JSON.parse(sessionStorage.getItem('guestCart') || '[]');
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            setCartCount(count);
        }
    }

    // Function to set cart count
    function setCartCount(count) {
        $('.cart-count').text(count);
        $('.cart-count').css('display', count > 0 ? 'flex' : 'none');
        sessionStorage.setItem('cartCount', count);
    }

    // Helper function to get total cart quantity
    function getTotalCartQuantity() {
        if (window.isLoggedIn) {
            let total = 0;
            $('.cart-item').each(function() {
                total += parseInt($(this).find('.quantity').text());
            });
            return total;
        } else {
            const cart = JSON.parse(sessionStorage.getItem('guestCart') || '[]');
            return cart.reduce((sum, item) => sum + item.quantity, 0);
        }
    }

    // Increase quantity
    $(document).on('click', '.quantity-btn.increase', function() {
        const id = $(this).data('id');
        const quantityElement = $(this).siblings('.quantity');
        const currentQuantity = parseInt(quantityElement.text());
        const newQuantity = currentQuantity + 1;

        if (window.isLoggedIn) {
            $.ajax({
                url: '/ayskrimWebsite/api/orders/updateCart.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ action: 'update_quantity', product_id: id, quantity: newQuantity }),
                success: function(response) {
                    if (response.success) {
                        quantityElement.text(newQuantity);
                        const price = parseFloat(quantityElement.closest('.cart-item').find('.product-price').text().replace('₱', ''));
                        const newTotal = (price * newQuantity).toFixed(2);
                        quantityElement.closest('.cart-item').find('.total-price').text('₱' + newTotal);
                        updateCartTotals();
                        updateCartCount();
                        $('.item-count').text(getTotalCartQuantity());
                    }
                }
            });
        } else {
            let cart = JSON.parse(sessionStorage.getItem('guestCart') || '[]');
            let item = cart.find(i => i.product_id == id);
            if (item) {
                item.quantity = newQuantity;
                sessionStorage.setItem('guestCart', JSON.stringify(cart));
                quantityElement.text(newQuantity);
                const price = parseFloat(quantityElement.closest('.cart-item').find('.product-price').text().replace('₱', ''));
                const newTotal = (price * newQuantity).toFixed(2);
                quantityElement.closest('.cart-item').find('.total-price').text('₱' + newTotal);
                updateCartTotals();
                updateCartCount();
                $('.item-count').text(getTotalCartQuantity());
            }
        }
    });

    // Decrease quantity
    $(document).on('click', '.quantity-btn.decrease', function() {
        const id = $(this).data('id');
        const quantityElement = $(this).siblings('.quantity');
        const currentQuantity = parseInt(quantityElement.text());

        if (currentQuantity > 1) {
            const newQuantity = currentQuantity - 1;

            if (window.isLoggedIn) {
                $.ajax({
                    url: '/ayskrimWebsite/api/orders/updateCart.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ action: 'update_quantity', product_id: id, quantity: newQuantity }),
                    success: function(response) {
                        if (response.success) {
                            quantityElement.text(newQuantity);
                            const price = parseFloat(quantityElement.closest('.cart-item').find('.product-price').text().replace('₱', ''));
                            const newTotal = (price * newQuantity).toFixed(2);
                            quantityElement.closest('.cart-item').find('.total-price').text('₱' + newTotal);
                            updateCartTotals();
                            updateCartCount();
                            $('.item-count').text(getTotalCartQuantity());
                        }
                    }
                });
            } else {
                let cart = JSON.parse(sessionStorage.getItem('guestCart') || '[]');
                let item = cart.find(i => i.product_id == id);
                if (item) {
                    item.quantity = newQuantity;
                    sessionStorage.setItem('guestCart', JSON.stringify(cart));
                    quantityElement.text(newQuantity);
                    const price = parseFloat(quantityElement.closest('.cart-item').find('.product-price').text().replace('₱', ''));
                    const newTotal = (price * newQuantity).toFixed(2);
                    quantityElement.closest('.cart-item').find('.total-price').text('₱' + newTotal);
                    updateCartTotals();
                    updateCartCount();
                    $('.item-count').text(getTotalCartQuantity());
                }
            }
        }
    });

    // Remove product
    $(document).on('click', '.remove-btn', function() {
        const id = $(this).data('id');
        const cartItem = $(this).closest('.cart-item');

        if (window.isLoggedIn) {
            $.ajax({
                url: '/ayskrimWebsite/api/orders/updateCart.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ action: 'remove_product', product_id: id }),
                success: function(response) {
                    if (response.success) {
                        cartItem.fadeOut(300, function() {
                            $(this).remove();
                            if ($('.cart-item').length === 0) {
                                $('.cart-items-container').html(
                                    '<div class="empty-cart">' +
                                    '<div class="empty-cart-icon"><i class="fas fa-shopping-bag"></i></div>' +
                                    '<p>Your cart is empty</p>' +
                                    '<button class="btn primary-btn" onclick="window.history.back()">Continue Shopping</button>' +
                                    '</div>'
                                );
                                $('.checkout-btn').prop('disabled', true);
                            }
                            updateCartTotals();
                            updateCartCount();
                            $('.item-count').text(getTotalCartQuantity());
                        });
                    }
                }
            });
        } else {
            let cart = JSON.parse(sessionStorage.getItem('guestCart') || '[]');
            cart = cart.filter(i => i.product_id != id);
            sessionStorage.setItem('guestCart', JSON.stringify(cart));
            cartItem.fadeOut(300, function() {
                $(this).remove();
                if ($('.cart-item').length === 0) {
                    $('.cart-items-container').html(
                        '<div class="empty-cart">' +
                        '<div class="empty-cart-icon"><i class="fas fa-shopping-bag"></i></div>' +
                        '<p>Your cart is empty</p>' +
                        '<button class="btn primary-btn" onclick="window.history.back()">Continue Shopping</button>' +
                        '</div>'
                    );
                    $('.checkout-btn').prop('disabled', true);
                }
                updateCartTotals();
                updateCartCount();
                $('.item-count').text(getTotalCartQuantity());
            });
        }
    });

    // Clear cart
    $('#clear-cart').click(function() {
        if (confirm('Are you sure you want to clear your cart?')) {
            if (window.isLoggedIn) {
                $.ajax({
                    url: '/ayskrimWebsite/api/orders/updateCart.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ action: 'clear_cart' }),
                    success: function(response) {
                        if (response.success) {
                            $('.cart-items-container').html(
                                '<div class="empty-cart">' +
                                '<div class="empty-cart-icon"><i class="fas fa-shopping-bag"></i></div>' +
                                '<p>Your cart is empty</p>' +
                                '<button class="btn primary-btn" onclick="window.history.back()">Continue Shopping</button>' +
                                '</div>'
                            );
                            $('.checkout-btn').prop('disabled', true);
                            setCartCount(0);
                            $('.item-count').text('0');
                            updateCartTotals();
                        }
                    }
                });
            } else {
                sessionStorage.setItem('guestCart', '[]');
                setCartCount(0);
                $('.cart-items-container').html(
                    '<div class="empty-cart">' +
                    '<div class="empty-cart-icon"><i class="fas fa-shopping-bag"></i></div>' +
                    '<p>Your cart is empty</p>' +
                    '<button class="btn primary-btn" onclick="window.history.back()">Continue Shopping</button>' +
                    '</div>'
                );
                $('.checkout-btn').prop('disabled', true);
                $('.item-count').text('0');
                updateCartTotals();
            }
        }
    });

    // Payment method selection
    $('input[name="payment"]').change(function() {
        const paymentMethod = $(this).val();
        $('.radio-item').removeClass('selected');
        $(`.radio-item[data-value="${paymentMethod}"]`).addClass('selected');
        
        if (window.isLoggedIn) {
            $.ajax({
                url: '/ayskrimWebsite/api/orders/updatePayment.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ payment_method: paymentMethod }),
                success: function(response) {
                    if (!response.success) {
                        console.error('Failed to update payment method');
                    }
                }
            });
        }
    });

    // Checkout button click handler
    $('.checkout-btn').click(function() {
        if (!window.isLoggedIn) {
            // For guests, redirect to login page
            window.location.href = '/ayskrimWebsite/landingPage/login.php';
            return;
        }

        // For logged-in users, proceed to checkout
        window.location.href = '/ayskrimWebsite/customerPage/checkout/checkout.php';
    });

    // Initial cart count load
    updateCartCount();

    // Update cart count when storage changes
    window.addEventListener('storage', (e) => {
        if (e.key === 'guestCart') {
            updateCartCount();
        }
    });

    // Update cart count when page is shown (for browser navigation)
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            updateCartCount();
        }
    });

    // Expose updateCartCount to window for other scripts to use
    window.updateCartCount = updateCartCount;
});