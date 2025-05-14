/**
 * Menu Management JavaScript
 * This file contains code specific to the admin menu management page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add CSS for product thumbnails and form rows
    const style = document.createElement('style');
    style.textContent = `
        .product-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: var(--radius);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .flex-grow-1 {
            flex-grow: 1;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-badge.available {
            background-color: rgba(0, 128, 0, 0.1);
            color: var(--status-delivered);
        }

        .status-badge.out-of-stock {
            background-color: rgba(255, 0, 0, 0.1);
            color: var(--status-cancelled);
        }

        .status-badge.seasonal {
            background-color: rgba(255, 165, 0, 0.1);
            color: var(--status-preparing);
        }

        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            color: var(--gray-500);
        }
    `;
    document.head.appendChild(style);

    // Product image preview
    const productImage = document.getElementById('productImage');
    const imagePreview = document.getElementById('imagePreview');

    if (productImage && imagePreview) {
        productImage.addEventListener('change', function(event) {
            if (event.target.files.length > 0) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(event.target.files[0]);
            } else {
                imagePreview.style.display = 'none';
            }
        });
    }

    // Filter products by category
    const categoryFilter = document.getElementById('categoryFilter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterProducts);
    }

    // Filter products by availability
    const availabilityFilter = document.getElementById('availabilityFilter');
    if (availabilityFilter) {
        availabilityFilter.addEventListener('change', filterProducts);
    }

    // Search products
    const searchInput = document.getElementById('searchProducts');
    if (searchInput) {
        searchInput.addEventListener('input', filterProducts);
    }

    // Function to filter products
    function filterProducts() {
        const categoryValue = categoryFilter.value;
        const availabilityValue = availabilityFilter.value;
        const searchValue = searchInput.value.toLowerCase();

        const rows = document.querySelectorAll('#productsTable tbody tr');

        rows.forEach(row => {
            const categoryMatch = !categoryValue || row.dataset.category === categoryValue;
            const availabilityMatch = !availabilityValue || row.dataset.availability === availabilityValue;

            const productName = row.querySelector('td[data-column="name"]').textContent.toLowerCase();
            const searchMatch = !searchValue || productName.includes(searchValue);

            if (categoryMatch && availabilityMatch && searchMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Delete product
    const deleteButtons = document.querySelectorAll('.delete-product');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.id;
            const productName = this.dataset.name;

            // Show confirmation
            if (confirm(`Are you sure you want to delete "${productName}"? This action cannot be undone.`)) {
                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.disabled = true;

                // Send AJAX request to delete product
                fetch('/ayskrimWebsite/api/admin/products/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${productId}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Remove product from DOM
                        const productRow = this.closest('tr');

                        // Add fade-out animation
                        productRow.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        productRow.style.opacity = '0';
                        productRow.style.transform = 'scale(0.95)';

                        // Remove after animation completes
                        setTimeout(() => {
                            productRow.remove();
                        }, 500);

                        // Show success message
                        const alertContainer = document.createElement('div');
                        alertContainer.className = 'alert alert-success';
                        alertContainer.setAttribute('data-auto-close', '5000');
                        alertContainer.innerHTML = `
                            <div class="alert-content">
                                <div class="alert-title">Success</div>
                                <p>${data.message}</p>
                            </div>
                            <button class="alert-close">&times;</button>
                        `;

                        // Insert alert at the top of the content area
                        const contentArea = document.querySelector('.admin-content');
                        contentArea.insertBefore(alertContainer, contentArea.firstChild);

                        // Auto-close alert after 5 seconds
                        setTimeout(() => {
                            alertContainer.remove();
                        }, 5000);
                    } else {
                        throw new Error(data.error || 'Failed to delete product');
                    }
                })
                .catch(error => {
                    console.error('Error deleting product:', error);

                    // Restore button
                    this.innerHTML = '<i class="fas fa-trash-alt"></i>';
                    this.disabled = false;

                    // Show error message
                    const alertContainer = document.createElement('div');
                    alertContainer.className = 'alert alert-danger';
                    alertContainer.setAttribute('data-auto-close', '5000');
                    alertContainer.innerHTML = `
                        <div class="alert-content">
                            <div class="alert-title">Error</div>
                            <p>Failed to delete product: ${error.message}</p>
                        </div>
                        <button class="alert-close">&times;</button>
                    `;

                    // Insert alert at the top of the content area
                    const contentArea = document.querySelector('.admin-content');
                    contentArea.insertBefore(alertContainer, contentArea.firstChild);

                    // Auto-close alert after 5 seconds
                    setTimeout(() => {
                        alertContainer.remove();
                    }, 5000);
                });
            }
        });
    });

    // Handle alert close buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('alert-close')) {
            const alert = e.target.closest('.alert');
            if (alert) {
                alert.remove();
            }
        }
    });

    // Edit product
    const editButtons = document.querySelectorAll('.edit-product');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.id;
            const modal = document.getElementById('editProductModal');
            const form = modal.querySelector('form');
            const loadingSpinner = modal.querySelector('.loading-spinner');
            const modalBody = modal.querySelector('.modal-body');

            // Show modal
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Set product ID
            document.getElementById('editProductId').value = productId;

            // Show loading spinner
            loadingSpinner.style.display = 'flex';

            // Fetch product data
            fetch(`/ayskrimWebsite/api/admin/products/get.php?id=${productId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.error || 'Failed to fetch product data');
                    }

                    // Hide loading spinner
                    loadingSpinner.style.display = 'none';

                    // Get product data
                    const product = data.product;

                    // Create form fields
                    const formHtml = `
                        <div class="form-group">
                            <label for="editProductName" class="form-label">Product Name</label>
                            <input type="text" id="editProductName" name="name" class="form-input" value="${product.name}" required>
                        </div>

                        <div class="form-group">
                            <label for="editProductDescription" class="form-label">Description</label>
                            <textarea id="editProductDescription" name="description" class="form-textarea">${product.description || ''}</textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="editProductPrice" class="form-label">Price (₱)</label>
                                <input type="number" id="editProductPrice" name="price" class="form-input" step="0.01" min="0" value="${product.price}" required>
                            </div>

                            <div class="form-group">
                                <label for="editProductCategory" class="form-label">Category</label>
                                <select id="editProductCategory" name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    ${getCategoryOptions(product.category_id)}
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="editProductStock" class="form-label">Stock</label>
                                <input type="number" id="editProductStock" name="stock" class="form-input" min="0" value="${product.stock}" required>
                            </div>

                            <div class="form-group">
                                <label for="editProductAvailability" class="form-label">Availability</label>
                                <select id="editProductAvailability" name="availability_status" class="form-select" required>
                                    <option value="Available" ${product.availability_status === 'Available' ? 'selected' : ''}>Available</option>
                                    <option value="Out of Stock" ${product.availability_status === 'Out of Stock' ? 'selected' : ''}>Out of Stock</option>
                                    <option value="Seasonal" ${product.availability_status === 'Seasonal' ? 'selected' : ''}>Seasonal</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="editProductImage" class="form-label">Product Image</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img src="/ayskrimWebsite/assets/images/products/${product.image_url}" alt="${product.name}" class="product-thumbnail" style="width: 80px; height: 80px;">
                                <div>Current image: ${product.image_url}</div>
                            </div>
                            <input type="file" id="editProductImage" name="image" class="form-input" accept="image/*">
                            <input type="hidden" name="current_image" value="${product.image_url}">
                            <div class="image-preview-container mt-2">
                                <img id="editImagePreview" class="image-preview" style="display: none; max-width: 100%; max-height: 200px;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="editProductFlavor" class="form-label">Flavor Profile</label>
                            <input type="text" id="editProductFlavor" name="flavor_profile" class="form-input" value="${product.flavor_profile || ''}">
                        </div>

                        <div class="form-group">
                            <label for="editProductIngredients" class="form-label">Ingredients</label>
                            <textarea id="editProductIngredients" name="ingredients" class="form-textarea">${product.ingredients || ''}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="editProductDietary" class="form-label">Dietary Type</label>
                            <select id="editProductDietary" name="dietary_type" class="form-select">
                                <option value="Regular" ${product.dietary_type === 'Regular' ? 'selected' : ''}>Regular</option>
                                <option value="Sugar-Free" ${product.dietary_type === 'Sugar-Free' ? 'selected' : ''}>Sugar-Free</option>
                                <option value="Dairy-Free" ${product.dietary_type === 'Dairy-Free' ? 'selected' : ''}>Dairy-Free</option>
                                <option value="Vegan" ${product.dietary_type === 'Vegan' ? 'selected' : ''}>Vegan</option>
                            </select>
                        </div>
                    `;

                    // Update form content
                    modalBody.innerHTML = formHtml;

                    // Initialize image preview for edit form
                    const editProductImage = document.getElementById('editProductImage');
                    const editImagePreview = document.getElementById('editImagePreview');

                    if (editProductImage && editImagePreview) {
                        editProductImage.addEventListener('change', function(event) {
                            if (event.target.files.length > 0) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    editImagePreview.src = e.target.result;
                                    editImagePreview.style.display = 'block';
                                };
                                reader.readAsDataURL(event.target.files[0]);
                            } else {
                                editImagePreview.style.display = 'none';
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching product data:', error);
                    loadingSpinner.style.display = 'none';
                    modalBody.innerHTML = `
                        <div class="alert alert-danger">
                            <div class="alert-content">
                                <div class="alert-title">Error</div>
                                <p>Failed to load product data. Please try again.</p>
                                <p class="text-sm">${error.message}</p>
                            </div>
                        </div>
                    `;
                });
        });
    });

    // Helper function to generate category options
    function getCategoryOptions(selectedCategoryId) {
        const categorySelect = document.getElementById('productCategory');
        let options = '';

        if (categorySelect) {
            Array.from(categorySelect.options).forEach(option => {
                if (option.value) {
                    const selected = parseInt(option.value) === parseInt(selectedCategoryId) ? 'selected' : '';
                    options += `<option value="${option.value}" ${selected}>${option.text}</option>`;
                }
            });
        }

        return options;
    }

    // Handle edit product form submission
    const editProductForm = document.getElementById('editProductForm');
    if (editProductForm) {
        editProductForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const modal = document.getElementById('editProductModal');

            // Disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            // Send AJAX request
            fetch('/ayskrimWebsite/api/admin/products/update.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Close modal
                    modal.classList.remove('active');
                    document.body.style.overflow = '';

                    // Show success message
                    const alertContainer = document.createElement('div');
                    alertContainer.className = 'alert alert-success';
                    alertContainer.setAttribute('data-auto-close', '5000');
                    alertContainer.innerHTML = `
                        <div class="alert-content">
                            <div class="alert-title">Success</div>
                            <p>${data.message}</p>
                        </div>
                        <button class="alert-close">&times;</button>
                    `;

                    // Insert alert at the top of the content area
                    const contentArea = document.querySelector('.admin-content');
                    contentArea.insertBefore(alertContainer, contentArea.firstChild);

                    // Auto-close alert after 5 seconds
                    setTimeout(() => {
                        alertContainer.remove();
                    }, 5000);

                    // Reload page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.error || 'Failed to update product');
                }
            })
            .catch(error => {
                console.error('Error updating product:', error);

                // Show error message in the modal
                const errorContainer = modal.querySelector('.error-message') || document.createElement('div');
                errorContainer.className = 'alert alert-danger error-message';
                errorContainer.innerHTML = `
                    <div class="alert-content">
                        <div class="alert-title">Error</div>
                        <p>${error.message}</p>
                    </div>
                `;

                // Insert error message if it doesn't exist
                if (!modal.querySelector('.error-message')) {
                    const modalBody = modal.querySelector('.modal-body');
                    modalBody.insertBefore(errorContainer, modalBody.firstChild);
                }

                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.innerHTML = 'Save Changes';
            });
        });
    }

    // Handle add product form submission
    const addProductForm = document.getElementById('addProductForm');
    if (addProductForm) {
        addProductForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const modal = document.getElementById('addProductModal');

            // Disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

            // Send AJAX request
            fetch('/ayskrimWebsite/api/admin/products/create.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Close modal
                    modal.classList.remove('active');
                    document.body.style.overflow = '';

                    // Show success message
                    const alertContainer = document.createElement('div');
                    alertContainer.className = 'alert alert-success';
                    alertContainer.setAttribute('data-auto-close', '5000');
                    alertContainer.innerHTML = `
                        <div class="alert-content">
                            <div class="alert-title">Success</div>
                            <p>${data.message}</p>
                        </div>
                        <button class="alert-close">&times;</button>
                    `;

                    // Insert alert at the top of the content area
                    const contentArea = document.querySelector('.admin-content');
                    contentArea.insertBefore(alertContainer, contentArea.firstChild);

                    // Auto-close alert after 5 seconds
                    setTimeout(() => {
                        alertContainer.remove();
                    }, 5000);

                    // Reload page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.error || 'Failed to add product');
                }
            })
            .catch(error => {
                console.error('Error adding product:', error);

                // Show error message in the modal
                const errorContainer = modal.querySelector('.error-message') || document.createElement('div');
                errorContainer.className = 'alert alert-danger error-message';
                errorContainer.innerHTML = `
                    <div class="alert-content">
                        <div class="alert-title">Error</div>
                        <p>${error.message}</p>
                    </div>
                `;

                // Insert error message if it doesn't exist
                if (!modal.querySelector('.error-message')) {
                    const modalBody = modal.querySelector('.modal-body');
                    modalBody.insertBefore(errorContainer, modalBody.firstChild);
                }

                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.innerHTML = 'Add Product';
            });
        });
    }

    // Export products
    const exportButton = document.getElementById('exportProducts');
    if (exportButton) {
        exportButton.addEventListener('click', function() {
            // Get table data
            const table = document.getElementById('productsTable');
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());

            // Skip the image and actions columns
            const skipColumns = [1, 7]; // Image and Actions columns
            const filteredHeaders = headers.filter((_, index) => !skipColumns.includes(index));

            // Get visible rows
            const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => row.style.display !== 'none');

            // Convert rows to CSV data
            const csvData = rows.map(row => {
                const cells = Array.from(row.querySelectorAll('td'));
                return cells
                    .filter((_, index) => !skipColumns.includes(index))
                    .map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`);
            });

            // Create CSV content
            const csvContent = [
                filteredHeaders.join(','),
                ...csvData.map(row => row.join(','))
            ].join('\n');

            // Create download link
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', 'products.csv');
            link.style.display = 'none';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
});
