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

    let searchTimeout = null;
    let loadingTimeout = null;
    let fetchDone = false;
    let showResults = null;
    let lastSearchController = null;
    let suggestionsExpanded = false;
    let lastRankedResults = [];
    let lastQuery = '';

    // Initialize map when modal opens
    pickLocationBtn.addEventListener('click', () => {
        locationPickerModal.classList.add('active');
        mapLoadingOverlay.style.display = 'flex';
        retryGeolocationBtn.style.display = 'none';
        document.getElementById('locationStatus').textContent = '';
        document.getElementById('map').innerHTML = '';
        // Clear search bar and suggestions
        addressSearchBox.value = '';
        searchSuggestions.style.display = 'none';
        searchSuggestions.innerHTML = '';
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
            // Change button text
            pickLocationBtn.textContent = 'Change Location';
            pickLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Change Location';
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

    // Helper functions for improved search functionality
    function normalizeString(str) {
        return str.toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '') // Remove diacritics
            .replace(/[^a-z0-9\s]/g, '') // Remove special characters
            .replace(/\s+/g, ' ') // Normalize spaces
            .trim();
    }

    function fuzzyScore(query, target) {
        const normQuery = normalizeString(query);
        const normTarget = normalizeString(target);
        
        // Exact match
        if (normTarget === normQuery) return 1.0;
        
        // Substring match
        if (normTarget.includes(normQuery)) return 0.9;
        
        // Word boundary match
        const queryWords = normQuery.split(/\s+/);
        const targetWords = normTarget.split(/\s+/);
        
        let wordMatchScore = 0;
        queryWords.forEach(qWord => {
            targetWords.forEach(tWord => {
                if (tWord.startsWith(qWord)) wordMatchScore += 0.8;
                else if (tWord.includes(qWord)) wordMatchScore += 0.6;
            });
        });
        
        // Levenshtein distance for typo tolerance
        const lev = levenshtein(normQuery, normTarget);
        const maxLen = Math.max(normQuery.length, normTarget.length);
        const levScore = 1 - (lev / maxLen);
        
        // Combine scores with weights
        return Math.max(
            wordMatchScore / queryWords.length,
            levScore * 0.7
        );
    }

    function levenshtein(a, b) {
        if (a.length === 0) return b.length;
        if (b.length === 0) return a.length;

        const matrix = Array(b.length + 1).fill(null).map(() => 
            Array(a.length + 1).fill(null)
        );

        for (let i = 0; i <= a.length; i++) matrix[0][i] = i;
        for (let j = 0; j <= b.length; j++) matrix[j][0] = j;

        for (let j = 1; j <= b.length; j++) {
            for (let i = 1; i <= a.length; i++) {
                const substitutionCost = a[i - 1] === b[j - 1] ? 0 : 1;
                matrix[j][i] = Math.min(
                    matrix[j][i - 1] + 1, // deletion
                    matrix[j - 1][i] + 1, // insertion
                    matrix[j - 1][i - 1] + substitutionCost // substitution
                );
            }
        }

        return matrix[b.length][a.length];
    }

    addressSearchBox.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        lastQuery = query;
        
        // Clear previous timeouts
        if (searchTimeout) clearTimeout(searchTimeout);
        if (loadingTimeout) clearTimeout(loadingTimeout);
        
        // Show loading state immediately
        searchSuggestions.innerHTML = '<div class="search-suggestions-loading"><div class="location-loading"></div>Searching...</div>';
        searchSuggestions.style.display = 'block';
        
        // Reset state
        fetchDone = false;
        showResults = null;
        suggestionsExpanded = false;
        lastRankedResults = [];
        
        if (!query) {
            searchSuggestions.style.display = 'none';
            return;
        }

        // Set minimum loading time to ensure smooth UX
        const minLoadingTime = 500;
        loadingTimeout = setTimeout(() => {
            if (fetchDone && typeof showResults === 'function') {
                showResults();
            }
        }, minLoadingTime);

        // Debounce the actual search
        searchTimeout = setTimeout(() => {
            if (lastSearchController) {
                lastSearchController.abort();
            }
            
            lastSearchController = new AbortController();
            const signal = lastSearchController.signal;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=20`, { signal })
                .then(res => res.json())
                .then(results => {
                    fetchDone = true;
                    lastRankedResults = results.map(place => {
                        const score = fuzzyScore(query, place.display_name);
                        return { place, score };
                    })
                    .filter(r => r.score > 0.3)
                    .sort((a, b) => b.score - a.score);

                    showResults = () => {
                        searchSuggestions.innerHTML = '';
                        
                        if (!lastRankedResults.length) {
                            searchSuggestions.innerHTML = '<div class="search-suggestions-loading">No results found</div>';
                            searchSuggestions.style.display = 'block';
                            return;
                        }

                        const displayCount = suggestionsExpanded ? 6 : 3;
                        const topResults = lastRankedResults.slice(0, displayCount);

                        // Group results by relevance
                        const exactMatches = topResults.filter(r => r.score > 0.9);
                        const partialMatches = topResults.filter(r => r.score > 0.6 && r.score <= 0.9);
                        const fuzzyMatches = topResults.filter(r => r.score <= 0.6);

                        // Render results by group
                        if (exactMatches.length > 0) {
                            renderResultGroup(exactMatches, 'Exact Matches', lastQuery, 'exact');
                        }
                        if (partialMatches.length > 0) {
                            renderResultGroup(partialMatches, 'Partial Matches', lastQuery, 'partial');
                        }
                        if (fuzzyMatches.length > 0) {
                            renderResultGroup(fuzzyMatches, 'Similar Matches', lastQuery, 'fuzzy');
                        }

                        // Show more/less button if needed
                        if (lastRankedResults.length > 3 && !suggestionsExpanded) {
                            const showMoreDiv = document.createElement('div');
                            showMoreDiv.className = 'suggestion show-more';
                            showMoreDiv.innerHTML = `
                                <span>Show ${Math.min(lastRankedResults.length - 3, 3)} more results</span>
                                <i class="fas fa-chevron-down"></i>
                            `;
                            showMoreDiv.onclick = () => {
                                suggestionsExpanded = true;
                                showResults();
                            };
                            searchSuggestions.appendChild(showMoreDiv);
                        } else if (suggestionsExpanded && lastRankedResults.length > 3) {
                            const showLessDiv = document.createElement('div');
                            showLessDiv.className = 'suggestion show-more';
                            showLessDiv.innerHTML = `
                                <span>Show less</span>
                                <i class="fas fa-chevron-up"></i>
                            `;
                            showLessDiv.onclick = () => {
                                suggestionsExpanded = false;
                                showResults();
                            };
                            searchSuggestions.appendChild(showLessDiv);
                        }
                    };

                    if (loadingTimeout) {
                        clearTimeout(loadingTimeout);
                        showResults();
                    }
                })
                .catch(error => {
                    if (error.name === 'AbortError') return;
                    console.error('Search error:', error);
                    searchSuggestions.innerHTML = '<div class="search-suggestions-loading">Error searching for locations</div>';
                });
        }, 300); // Debounce delay
    });

    function renderResultGroup(results, groupTitle, query, groupType) {
        const groupDiv = document.createElement('div');
        groupDiv.className = 'suggestion-group';
        
        const titleDiv = document.createElement('div');
        titleDiv.className = 'suggestion-group-title ' + (groupType ? `group-${groupType}` : '');
        titleDiv.textContent = groupTitle;
        groupDiv.appendChild(titleDiv);

        results.forEach(({ place }) => {
            const div = document.createElement('div');
            div.className = 'suggestion';
            div.innerHTML = highlightMatch(place.display_name, query);
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
            groupDiv.appendChild(div);
        });

        searchSuggestions.appendChild(groupDiv);
    }

    // Helper to highlight all query words in a string
    function highlightMatch(text, query) {
        if (!query) return text;
        // Split query into words, ignore empty
        const words = query.split(/\s+/).filter(Boolean);
        if (!words.length) return text;
        // Build regex to match any word, case-insensitive
        const regex = new RegExp('(' + words.map(w => w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|') + ')', 'gi');
        return text.replace(regex, match => `<b class=\"highlighted-match\">${match}</b>`);
    }

    // Close suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (!addressSearchBox.contains(e.target) && !searchSuggestions.contains(e.target)) {
            searchSuggestions.style.display = 'none';
            suggestionsExpanded = false;
        }
    });
}); 