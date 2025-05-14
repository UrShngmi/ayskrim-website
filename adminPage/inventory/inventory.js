/**
 * Inventory Management JavaScript
 * This file contains code specific to the admin inventory management page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add CSS for inventory-specific elements
    const style = document.createElement('style');
    style.textContent = `
        .stock-badge {
            display: inline-flex;
            align-items: center;
            padding: var(--spacing-1) var(--spacing-3);
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .stock-badge.out-of-stock {
            background-color: rgba(255, 0, 0, 0.1);
            color: var(--status-cancelled);
        }

        .stock-badge.critical {
            background-color: rgba(255, 0, 0, 0.1);
            color: var(--status-cancelled);
        }

        .stock-badge.low {
            background-color: rgba(255, 165, 0, 0.1);
            color: var(--status-preparing);
        }

        .stock-badge.normal {
            background-color: rgba(0, 128, 0, 0.1);
            color: var(--status-delivered);
        }

        .low-stock {
            background-color: rgba(245, 158, 11, 0.05);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: var(--spacing-3);
            margin-top: var(--spacing-4);
        }

        .search-box {
            position: relative;
        }

        .search-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: var(--spacing-1) var(--spacing-3);
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge.success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .badge.info {
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .badge.warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .badge.danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .badge.secondary {
            background-color: rgba(107, 114, 128, 0.1);
            color: var(--gray-600);
        }

        .product-selection {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            padding: var(--spacing-2);
        }

        .product-checkbox {
            display: flex;
            align-items: center;
            padding: var(--spacing-2);
            border-bottom: 1px solid var(--gray-100);
        }

        .product-checkbox:last-child {
            border-bottom: none;
        }

        .product-checkbox label {
            flex: 1;
            margin-left: var(--spacing-2);
            margin-bottom: 0;
            cursor: pointer;
        }

        .current-stock {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-left: var(--spacing-2);
        }

        .quantity-input {
            width: 80px;
            margin-left: var(--spacing-2);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .view-all {
            font-size: 0.875rem;
            color: var(--primary-600);
            text-decoration: none;
        }

        .view-all:hover {
            text-decoration: underline;
        }
    `;
    document.head.appendChild(style);

    // Search inventory
    const searchInput = document.getElementById('searchInventory');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#inventoryTable tbody tr');

            rows.forEach(row => {
                const productName = row.querySelector('td[data-column="name"]').textContent.toLowerCase();
                const category = row.querySelector('td[data-column="category"]').textContent.toLowerCase();

                if (productName.includes(searchValue) || category.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // View product history
    const historyButtons = document.querySelectorAll('.view-history');
    historyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.id;
            const productName = this.dataset.name;
            const modal = document.getElementById('productHistoryModal');
            const nameSpan = document.getElementById('historyProductName');
            const loadingSpinner = modal.querySelector('.loading-spinner');
            const historyContent = modal.querySelector('.history-content');

            // Show modal
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Set product name
            nameSpan.textContent = productName;

            // Show loading spinner
            loadingSpinner.style.display = 'flex';
            historyContent.style.display = 'none';

            // Fetch product history
            fetch(`/ayskrimWebsite/api/admin/inventory/history.php?id=${productId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Hide loading spinner
                    loadingSpinner.style.display = 'none';
                    historyContent.style.display = 'block';

                    if (!data.success) {
                        throw new Error(data.error || 'Failed to fetch product history');
                    }

                    const history = data.history;

                    // Populate history content
                    if (history.length === 0) {
                        historyContent.innerHTML = '<div class="alert alert-info">No history found for this product.</div>';
                    } else {
                        let html = '<div class="table-container"><table class="admin-table">';
                        html += '<thead><tr><th>Date & Time</th><th>Action</th><th>Quantity</th><th>Admin</th><th>Notes</th></tr></thead>';
                        html += '<tbody>';

                        history.forEach(item => {
                            const date = new Date(item.created_at).toLocaleString();
                            const quantityClass = item.quantity_change > 0 ? 'text-success' : 'text-danger';
                            const quantityPrefix = item.quantity_change > 0 ? '+' : '';

                            html += `<tr>
                                <td>${date}</td>
                                <td><span class="badge ${getActionClass(item.action_type)}">${item.action_type}</span></td>
                                <td class="${quantityClass}">${quantityPrefix}${item.quantity_change}</td>
                                <td>${item.admin_name}</td>
                                <td>${item.notes || 'N/A'}</td>
                            </tr>`;
                        });

                        html += '</tbody></table></div>';
                        historyContent.innerHTML = html;
                    }
                })
                .catch(error => {
                    console.error('Error fetching product history:', error);
                    loadingSpinner.style.display = 'none';
                    historyContent.style.display = 'block';
                    historyContent.innerHTML = '<div class="alert alert-danger">Error loading history. Please try again.</div>';
                });
        });
    });

    // Batch restock - enable/disable quantity inputs based on checkbox state
    const productCheckboxes = document.querySelectorAll('.product-checkbox input[type="checkbox"]');
    productCheckboxes.forEach(checkbox => {
        const quantityInput = checkbox.closest('.product-checkbox').querySelector('.quantity-input');

        // Initial state
        quantityInput.disabled = !checkbox.checked;

        checkbox.addEventListener('change', function() {
            quantityInput.disabled = !this.checked;
        });
    });

    // Helper function to get action class
    function getActionClass(action) {
        switch (action) {
            case 'Restock':
                return 'success';
            case 'Sale':
                return 'info';
            case 'Adjustment':
                return 'warning';
            case 'Waste':
                return 'danger';
            default:
                return 'secondary';
        }
    }
});
