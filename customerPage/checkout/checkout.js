document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const cartItemsContainer = document.querySelector('.cart-items');
    const itemCountElement = document.querySelector('.item-count');
    const subtotalElement = document.querySelector('.subtotal');
    const deliveryFeeElement = document.querySelector('.delivery-fee');
    const totalAmountElement = document.querySelector('.total-amount');
    const deliveryForm = document.getElementById('deliveryForm');
    const loadingOverlay = document.querySelector('.loading-overlay');
    const successModal = document.getElementById('successModal');
    const promoInput = document.getElementById('promo-code');
    const applyPromoBtn = document.getElementById('apply-promo');
    const addressDisplay = document.getElementById('addressDisplay');

    // Location Picker Elements
    const locationPickerModal = document.getElementById('locationPickerModal');
    const pickLocationBtn = document.getElementById('pickLocationBtn');
    const closeModalBtn = locationPickerModal.querySelector('.close-modal');
    const useCurrentLocationBtn = document.getElementById('useCurrentLocation');
    const confirmLocationBtn = document.getElementById('confirmLocation');
    const locationStatus = document.getElementById('locationStatus');
    const selectedAddress = document.getElementById('selectedAddress');
    const addressInput = document.getElementById('address');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');

    // Elements for modal content
    const modalBody = locationPickerModal.querySelector('.modal-body');
    const modalFooter = locationPickerModal.querySelector('.modal-footer');

    // Constants
    const DELIVERY_FEE = 50; // ₱50 delivery fee

    // Map variables
    let map;
    let marker;
    let selectedLocation = null;
    let mapInitialized = false;
    let originalGeolocation = null; // Store the user's original geolocation
    let lastKnownLocation = null;

    // Form submission
    deliveryForm.addEventListener('submit', handleOrderSubmission);

    // Phone number validation
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        e.target.value = value;
    });

    // Promo code application
    applyPromoBtn.addEventListener('click', handlePromoCode);

    // Map loading overlay elements
    const mapLoadingOverlay = document.getElementById('mapLoadingOverlay');
    const retryGeolocationBtn = document.getElementById('retryGeolocationBtn');

    // Address search box
    const addressSearchBox = document.getElementById('addressSearchBox');
    const searchSuggestions = document.getElementById('searchSuggestions');
    const coordsDisplay = document.getElementById('coordsDisplay');

    // Initialize map when modal opens
    pickLocationBtn.addEventListener('click', () => {
        locationPickerModal.classList.add('active');
        mapLoadingOverlay.style.display = 'flex';
        retryGeolocationBtn.style.display = 'none';
        document.getElementById('locationStatus').textContent = '';
        document.getElementById('map').innerHTML = '';
        if (selectedLocation && selectedLocation.address) {
            selectedAddress.textContent = selectedLocation.address;
            confirmLocationBtn.disabled = false;
            coordsDisplay.textContent = `Lat: ${selectedLocation.lat?.toFixed(6)}, Lng: ${selectedLocation.lng?.toFixed(6)}`;
            mapLoadingOverlay.style.display = 'none';
            // Always re-initialize the map at the selected location
            initMapWithCoords(selectedLocation.lat, selectedLocation.lng);
        } else {
            setTimeout(() => {
                getAndSetUserGeolocationOnly();
            }, 100);
        }
    });

    retryGeolocationBtn.addEventListener('click', () => {
        mapLoadingOverlay.style.display = 'flex';
        retryGeolocationBtn.style.display = 'none';
        document.getElementById('locationStatus').textContent = '';
        document.getElementById('map').innerHTML = '';
        getAndSetUserGeolocationOnly();
    });

    // Only geolocation API
    function getAndSetUserGeolocationOnly() {
        if (mapInitialized && map) {
            map.remove();
            mapInitialized = false;
        }
        mapLoadingOverlay.style.display = 'flex';
        retryGeolocationBtn.style.display = 'none';
        document.getElementById('locationStatus').textContent = '';
        document.getElementById('map').innerHTML = '';

        if (navigator.geolocation) {
            let bestPosition = null;
            let bestAccuracy = Infinity;
            let watchId = null;
            let finished = false;
            function finishWithPosition(pos) {
                if (finished) return;
                finished = true;
                if (watchId !== null) navigator.geolocation.clearWatch(watchId);
                if (pos) {
                    const { latitude, longitude } = pos.coords;
                    originalGeolocation = { lat: latitude, lng: longitude };
                    lastKnownLocation = { lat: latitude, lng: longitude };
                    showMapWithCoords(latitude, longitude);
                } else if (lastKnownLocation) {
                    showMapWithCoords(lastKnownLocation.lat, lastKnownLocation.lng);
                } else {
                    showGeolocationError();
                }
            }
            watchId = navigator.geolocation.watchPosition(
                (pos) => {
                    if (pos.coords.accuracy < bestAccuracy) {
                        bestAccuracy = pos.coords.accuracy;
                        bestPosition = pos;
                    }
                },
                (error) => {
                    finishWithPosition(null);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
            setTimeout(() => {
                finishWithPosition(bestPosition);
            }, 3000);
        } else if (lastKnownLocation) {
            showMapWithCoords(lastKnownLocation.lat, lastKnownLocation.lng);
        } else {
            showGeolocationError();
        }
    }

    function showGeolocationError() {
        mapLoadingOverlay.style.display = 'flex';
        retryGeolocationBtn.style.display = 'block';
        document.getElementById('locationStatus').textContent = 'Unable to access your location. Please enable location services or search for your address.';
    }

    function showMapWithCoords(lat, lng) {
        lastKnownLocation = { lat, lng };
        mapLoadingOverlay.style.display = 'none';
        initMapWithCoords(lat, lng);
    }

    function initMapWithCoords(lat, lng) {
        // Always remove previous map instance if it exists
        if (mapInitialized && map) {
            map.remove();
            map = null;
            mapInitialized = false;
        }
        map = L.map('map').setView([lat, lng], 17);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        marker = L.marker([lat, lng], {
            draggable: true,
            autoPan: true
        }).addTo(map);
        marker.on('dragend', handleMarkerDrag);
        map.on('click', handleMapClick);
        mapInitialized = true;
        updateMarkerPosition(lat, lng);
        reverseGeocode(lat, lng);
    }

    // Handle map click
    function handleMapClick(e) {
        const { lat, lng } = e.latlng;
        updateMarkerPosition(lat, lng);
        reverseGeocode(lat, lng);
    }

    // Handle marker drag
    function handleMarkerDrag(e) {
        const { lat, lng } = e.target.getLatLng();
        reverseGeocode(lat, lng);
    }

    // Update marker position
    function updateMarkerPosition(lat, lng) {
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], map.getZoom());
    }

    // Reverse geocode coordinates to address
    async function reverseGeocode(lat, lng) {
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
            const data = await response.json();
            
            if (data.display_name) {
                selectedLocation = {
                    lat,
                    lng,
                    address: data.display_name
                };
                
                selectedAddress.textContent = data.display_name;
                confirmLocationBtn.disabled = false;
                coordsDisplay.textContent = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
            } else {
                confirmLocationBtn.disabled = true;
                coordsDisplay.textContent = '';
            }
        } catch (error) {
            console.error('Error reverse geocoding:', error);
            locationStatus.innerHTML = '<div class="location-error">Error getting address details</div>';
            confirmLocationBtn.disabled = true;
            coordsDisplay.textContent = '';
        }
    }

    // Confirm location
    confirmLocationBtn.addEventListener('click', () => {
        if (selectedLocation) {
            addressInput.value = selectedLocation.address;
            latitudeInput.value = selectedLocation.lat;
            longitudeInput.value = selectedLocation.lng;
            addressDisplay.textContent = selectedLocation.address;
            addressDisplay.classList.add('selected');
            locationPickerModal.classList.remove('active');
            locationStatus.innerHTML = '';
        }
    });

    function updateTotals(cartItems) {
        const subtotal = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const discount = parseFloat(document.querySelector('.discount-value')?.textContent.replace('-₱', '') || '0');
        const total = subtotal + DELIVERY_FEE - discount;

        subtotalElement.textContent = `₱${subtotal.toFixed(2)}`;
        deliveryFeeElement.textContent = `₱${DELIVERY_FEE.toFixed(2)}`;
        totalAmountElement.textContent = `₱${total.toFixed(2)}`;
    }

    async function handlePromoCode() {
        const promoCode = promoInput.value.trim();
        if (!promoCode) {
            showError('Please enter a promo code');
            return;
        }

        try {
            const response = await fetch('/ayskrimWebsite/api/orders/applyPromo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ promo_code: promoCode })
            });

            const data = await response.json();

            if (data.success) {
                showSuccess('Promo code applied successfully!');
            } else {
                showError(data.error || 'Invalid promo code');
            }
        } catch (error) {
            console.error('Error applying promo code:', error);
            showError('Failed to apply promo code');
        }
    }

    async function handleOrderSubmission(e) {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        loadingOverlay.classList.add('active');

        try {
            const formData = new FormData(deliveryForm);
            const orderData = {
                fullName: formData.get('fullName'),
                phone: formData.get('phone'),
                address: formData.get('address'),
                latitude: formData.get('latitude'),
                longitude: formData.get('longitude'),
                instructions: formData.get('instructions'),
                paymentMethod: formData.get('paymentMethod'),
                totalAmount: parseFloat(document.querySelector('.total-amount').textContent.replace('₱', '').replace(',', '')),
                items: Array.from(document.querySelectorAll('.cart-item')).map(item => ({
                    productId: item.dataset.productId,
                    quantity: parseInt(item.querySelector('.item-quantity').textContent.replace('x', '')),
                    price: parseFloat(item.querySelector('.item-price').textContent.replace('₱', '').replace(',', ''))
                }))
            };

            const response = await fetch('/ayskrimWebsite/api/orders/createOrder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(orderData)
            });

            const data = await response.json();

            if (data.success) {
                sessionStorage.setItem('lastOrder', JSON.stringify({
                    orderId: data.orderId,
                    trackingCode: data.trackingCode,
                    transactionId: data.transactionId
                }));
                
                showSuccessModal();
            } else {
                showError(data.error || 'Failed to place order');
            }
        } catch (error) {
            console.error('Error submitting order:', error);
            showError('Failed to place order');
        } finally {
            loadingOverlay.classList.remove('active');
        }
    }

    function validateForm() {
        const fullName = document.getElementById('fullName').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const address = document.getElementById('address').value.trim();
        const latitude = document.getElementById('latitude').value;
        const longitude = document.getElementById('longitude').value;
        const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked');

        if (!fullName) {
            showError('Please enter your full name');
            return false;
        }

        if (!phone || phone.length !== 11) {
            showError('Please enter a valid 11-digit phone number');
            return false;
        }

        if (!address || !latitude || !longitude) {
            showError('Please select your delivery location');
            return false;
        }

        if (!paymentMethod) {
            showError('Please select a payment method');
            return false;
        }

        return true;
    }

    function showError(message) {
        // Create error notification
        const notification = document.createElement('div');
        notification.className = 'notification error';
        notification.innerHTML = `
            <div class="notification-content">
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;

        // Add to document
        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => notification.classList.add('active'), 100);

        // Add close button functionality
        const closeButton = notification.querySelector('.notification-close');
        closeButton.addEventListener('click', () => {
            notification.classList.remove('active');
            setTimeout(() => notification.remove(), 300);
        });

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.remove('active');
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    function showSuccess(message) {
        // Create success notification
        const notification = document.createElement('div');
        notification.className = 'notification success';
        notification.innerHTML = `
            <div class="notification-content">
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;

        // Add to document
        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => notification.classList.add('active'), 100);

        // Add close button functionality
        const closeButton = notification.querySelector('.notification-close');
        closeButton.addEventListener('click', () => {
            notification.classList.remove('active');
            setTimeout(() => notification.remove(), 300);
        });

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.remove('active');
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    function showSuccessModal() {
        successModal.classList.add('active');
    }

    // Add payment method selection handler
    document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const paymentDetails = document.getElementById('paymentDetails');
            if (this.value === 'Credit Card') {
                paymentDetails.innerHTML = `
                    <div class="form-group">
                        <label for="cardNumber">Card Number</label>
                        <input type="text" id="cardNumber" class="form-control" placeholder="1234 5678 9012 3456" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="expiryDate">Expiry Date</label>
                            <input type="text" id="expiryDate" class="form-control" placeholder="MM/YY" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" class="form-control" placeholder="123" required>
                        </div>
                    </div>
                `;
            } else if (this.value === 'GCash') {
                paymentDetails.innerHTML = `
                    <div class="form-group">
                        <label for="gcashNumber">GCash Number</label>
                        <input type="text" id="gcashNumber" class="form-control" placeholder="09XX XXX XXXX" required>
                    </div>
                `;
            } else {
                paymentDetails.innerHTML = '';
            }
        });
    });

    // Use current location button
    useCurrentLocationBtn.addEventListener('click', () => {
        if (originalGeolocation) {
            updateMarkerPosition(originalGeolocation.lat, originalGeolocation.lng);
            reverseGeocode(originalGeolocation.lat, originalGeolocation.lng);
            locationStatus.innerHTML = '';
        } else {
            getAndSetUserGeolocationOnly();
        }
    });

    // Close modal
    closeModalBtn.addEventListener('click', () => {
        locationPickerModal.classList.remove('active');
        mapLoadingOverlay.style.display = 'none';
        retryGeolocationBtn.style.display = 'none';
        document.getElementById('locationStatus').textContent = '';
        // Only clear if no valid location
        if (!selectedLocation || !selectedLocation.address) {
            confirmLocationBtn.disabled = true;
            addressSearchBox.value = '';
            selectedAddress.textContent = 'No location selected';
            coordsDisplay.textContent = '';
        }
    });

    // Close modal when clicking outside
    locationPickerModal.addEventListener('click', (e) => {
        if (e.target === locationPickerModal) {
            locationPickerModal.classList.remove('active');
            mapLoadingOverlay.style.display = 'none';
            retryGeolocationBtn.style.display = 'none';
            document.getElementById('locationStatus').textContent = '';
            // Only clear if no valid location
            if (!selectedLocation || !selectedLocation.address) {
                confirmLocationBtn.disabled = true;
                addressSearchBox.value = '';
                selectedAddress.textContent = 'No location selected';
                coordsDisplay.textContent = '';
            }
        }
    });

    // Debounce utility
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Normalize string for matching (remove diacritics, lowercase, remove dashes/special chars)
    function normalizeString(str) {
        return str
            .toLowerCase()
            .normalize('NFD').replace(/\p{Diacritic}/gu, '')
            .replace(/[-_.,/\\]/g, '')
            .replace(/\s+/g, '');
    }

    let lastSearchController = null;

    // Fuzzy scoring function (simple: substring, word overlap, Levenshtein distance for short queries)
    function levenshtein(a, b) {
        const an = a ? a.length : 0;
        const bn = b ? b.length : 0;
        if (an === 0) return bn;
        if (bn === 0) return an;
        const matrix = [];
        for (let i = 0; i <= bn; ++i) matrix[i] = [i];
        for (let j = 0; j <= an; ++j) matrix[0][j] = j;
        for (let i = 1; i <= bn; ++i) {
            for (let j = 1; j <= an; ++j) {
                if (b.charAt(i - 1) === a.charAt(j - 1)) {
                    matrix[i][j] = matrix[i - 1][j - 1];
                } else {
                    matrix[i][j] = Math.min(
                        matrix[i - 1][j - 1] + 1,
                        Math.min(matrix[i][j - 1] + 1, matrix[i - 1][j] + 1)
                    );
                }
            }
        }
        return matrix[bn][an];
    }

    // Enhanced fuzzy scoring function
    function fuzzyScore(query, name) {
        const normQuery = normalizeString(query);
        const normName = normalizeString(name);
        
        // Exact match
        if (normName === normQuery) return 100;
        // Starts with query
        if (normName.startsWith(normQuery)) return 95;
        // Substring match anywhere (strong reward)
        if (normName.includes(normQuery)) {
            // The closer to the start, the higher the score
            const pos = normName.indexOf(normQuery);
            return 90 - pos;
        }
        // Word overlap with priority
        const queryWords = normQuery.split(/\s+/);
        const nameWords = normName.split(/\s+/);
        let score = 0;
        queryWords.forEach(qw => {
            // Exact word match
            if (nameWords.includes(qw)) {
                score += 30;
            }
            // Word starts with query word
            else if (nameWords.some(nw => nw.startsWith(qw))) {
                score += 20;
            }
            // Word contains query word
            else if (nameWords.some(nw => nw.includes(qw))) {
                score += 10;
            }
        });
        // Levenshtein distance for short queries
        if (normQuery.length < 6) {
            const lev = levenshtein(normQuery, normName.slice(0, normQuery.length + 2));
            score -= lev * 5;
        }
        return Math.max(0, score);
    }

    let suggestionsExpanded = false;

    addressSearchBox.addEventListener('input', debounce(function() {
        const query = this.value.trim();
        suggestionsExpanded = false; // Reset on new input
        if (query.length < 1) {
            searchSuggestions.style.display = 'none';
            searchSuggestions.innerHTML = '';
            return;
        }

        // Cancel previous fetch if still pending
        if (lastSearchController) lastSearchController.abort();
        lastSearchController = new AbortController();
        const signal = lastSearchController.signal;

        // Show loading spinner
        searchSuggestions.innerHTML = '<div class="search-suggestions-loading"><div class="location-loading"></div>Searching...</div>';
        searchSuggestions.style.display = 'block';

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=20`, { signal })
            .then(res => res.json())
            .then(results => {
                searchSuggestions.innerHTML = '';
                if (results.length === 0) {
                    searchSuggestions.innerHTML = '<div class="search-suggestions-loading">No results found</div>';
                    searchSuggestions.style.display = 'block';
                    return;
                }
                const normQuery = normalizeString(query);
                // Enhanced fuzzy rank and filter: always include substring matches
                const ranked = results.map(place => {
                    const normName = normalizeString(place.display_name);
                    const score = fuzzyScore(query, place.display_name);
                    const isSubstring = normName.includes(normQuery);
                    return { place, score, isSubstring };
                })
                // Always include substring matches, even if score is 0
                .filter(r => r.score > 0 || r.isSubstring)
                .sort((a, b) => b.score - a.score);

                // Only show 'Show more results' if there are more than 3 matches
                const displayCount = suggestionsExpanded ? 6 : 3;
                const topResults = ranked.slice(0, displayCount);

                topResults.forEach(({ place }) => {
                    const div = document.createElement('div');
                    div.className = 'suggestion';
                    // Highlight matching parts
                    const displayName = place.display_name;
                    const regex = new RegExp(query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
                    div.innerHTML = displayName.replace(regex, match => `<b>${match}</b>`);
                    div.onclick = () => {
                        addressSearchBox.value = place.display_name;
                        searchSuggestions.style.display = 'none';
                        suggestionsExpanded = false;
                        if (mapInitialized) {
                            map.setView([Number(place.lat), Number(place.lon)], map.getZoom());
                            marker.setLatLng([Number(place.lat), Number(place.lon)]);
                            lastKnownLocation = { lat: Number(place.lat), lng: Number(place.lon) };
                            reverseGeocode(Number(place.lat), Number(place.lon));
                        } else {
                            showMapWithCoords(Number(place.lat), Number(place.lon));
                        }
                    };
                    searchSuggestions.appendChild(div);
                });

                // Show 'Show more results' or 'Show less' row only if there are more than 3 matches
                if (ranked.length > 3 && !suggestionsExpanded) {
                    const showMoreDiv = document.createElement('div');
                    showMoreDiv.className = 'suggestion show-more';
                    showMoreDiv.style.display = 'flex';
                    showMoreDiv.style.justifyContent = 'space-between';
                    showMoreDiv.style.alignItems = 'center';
                    showMoreDiv.style.fontWeight = '500';
                    showMoreDiv.style.cursor = 'pointer';
                    showMoreDiv.innerHTML = `<span>Show more results</span><i class=\"fas fa-chevron-down\"></i>`;
                    showMoreDiv.onclick = () => {
                        suggestionsExpanded = true;
                        renderSuggestions(query, ranked);
                    };
                    searchSuggestions.appendChild(showMoreDiv);
                } else if (suggestionsExpanded && ranked.length > 3) {
                    const showLessDiv = document.createElement('div');
                    showLessDiv.className = 'suggestion show-more';
                    showLessDiv.style.display = 'flex';
                    showLessDiv.style.justifyContent = 'space-between';
                    showLessDiv.style.alignItems = 'center';
                    showLessDiv.style.fontWeight = '500';
                    showLessDiv.style.cursor = 'pointer';
                    showLessDiv.innerHTML = `<span>Show less</span><i class=\"fas fa-chevron-up\"></i>`;
                    showLessDiv.onclick = () => {
                        suggestionsExpanded = false;
                        renderSuggestions(query, ranked);
                    };
                    searchSuggestions.appendChild(showLessDiv);
                }

                searchSuggestions.style.display = searchSuggestions.innerHTML ? 'block' : 'none';
            })
            .catch((err) => {
                // Always clear loading spinner and hide suggestions on error/abort
                searchSuggestions.innerHTML = '';
                searchSuggestions.style.display = 'none';
            });
    }, 150));

    // Helper to re-render suggestions on expand/collapse
    function renderSuggestions(query, ranked) {
        searchSuggestions.innerHTML = '';
        const displayCount = suggestionsExpanded ? 6 : 3;
        const topResults = ranked.slice(0, displayCount);
        topResults.forEach(({ place }) => {
            const div = document.createElement('div');
            div.className = 'suggestion';
            const displayName = place.display_name;
            const regex = new RegExp(query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
            div.innerHTML = displayName.replace(regex, match => `<b>${match}</b>`);
            div.onclick = () => {
                addressSearchBox.value = place.display_name;
                searchSuggestions.style.display = 'none';
                suggestionsExpanded = false;
                if (mapInitialized) {
                    map.setView([Number(place.lat), Number(place.lon)], map.getZoom());
                    marker.setLatLng([Number(place.lat), Number(place.lon)]);
                    lastKnownLocation = { lat: Number(place.lat), lng: Number(place.lon) };
                    reverseGeocode(Number(place.lat), Number(place.lon));
                } else {
                    showMapWithCoords(Number(place.lat), Number(place.lon));
                }
            };
            searchSuggestions.appendChild(div);
        });
        // Only show 'Show more results' if there are more than 3 matches
        if (ranked.length > 3 && !suggestionsExpanded) {
            const showMoreDiv = document.createElement('div');
            showMoreDiv.className = 'suggestion show-more';
            showMoreDiv.style.display = 'flex';
            showMoreDiv.style.justifyContent = 'space-between';
            showMoreDiv.style.alignItems = 'center';
            showMoreDiv.style.fontWeight = '500';
            showMoreDiv.style.cursor = 'pointer';
            showMoreDiv.innerHTML = `<span>Show more results</span><i class=\"fas fa-chevron-down\"></i>`;
            showMoreDiv.onclick = () => {
                suggestionsExpanded = true;
                renderSuggestions(query, ranked);
            };
            searchSuggestions.appendChild(showMoreDiv);
        } else if (suggestionsExpanded && ranked.length > 3) {
            const showLessDiv = document.createElement('div');
            showLessDiv.className = 'suggestion show-more';
            showLessDiv.style.display = 'flex';
            showLessDiv.style.justifyContent = 'space-between';
            showLessDiv.style.alignItems = 'center';
            showLessDiv.style.fontWeight = '500';
            showLessDiv.style.cursor = 'pointer';
            showLessDiv.innerHTML = `<span>Show less</span><i class=\"fas fa-chevron-up\"></i>`;
            showLessDiv.onclick = () => {
                suggestionsExpanded = false;
                renderSuggestions(query, ranked);
            };
            searchSuggestions.appendChild(showLessDiv);
        }
        searchSuggestions.style.display = searchSuggestions.innerHTML ? 'block' : 'none';
    }

    // Close suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (!addressSearchBox.contains(e.target) && !searchSuggestions.contains(e.target)) {
            searchSuggestions.style.display = 'none';
            suggestionsExpanded = false;
        }
    });
}); 