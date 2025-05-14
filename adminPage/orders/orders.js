/**
 * Orders Management JavaScript
 * This file contains code specific to the admin orders management page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add CSS for orders-specific elements
    const style = document.createElement('style');
    style.textContent = `
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: var(--spacing-3);
            margin-top: var(--spacing-4);
        }

        .filters-form .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .filters-form .form-row {
                grid-template-columns: 1fr;
            }
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            color: var(--gray-500);
        }

        .view-link {
            color: var(--primary-600);
            text-decoration: none;
            font-size: 0.875rem;
        }

        .view-link:hover {
            text-decoration: underline;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-gray-500 {
            color: var(--gray-500);
        }

        .status-badge.pending {
            background-color: rgba(128, 128, 128, 0.1);
            color: var(--status-pending);
        }

        .status-badge.preparing {
            background-color: rgba(255, 165, 0, 0.1);
            color: var(--status-preparing);
        }

        .status-badge.out-for-delivery {
            background-color: rgba(0, 0, 255, 0.1);
            color: var(--status-out-for-delivery);
        }

        .status-badge.delivered {
            background-color: rgba(0, 128, 0, 0.1);
            color: var(--status-delivered);
        }

        .status-badge.cancelled {
            background-color: rgba(255, 0, 0, 0.1);
            color: var(--status-cancelled);
        }

        .status-badge.paid {
            background-color: rgba(0, 128, 0, 0.1);
            color: var(--status-delivered);
        }

        .status-badge.failed {
            background-color: rgba(255, 0, 0, 0.1);
            color: var(--status-cancelled);
        }

        .status-badge.refunded {
            background-color: rgba(128, 128, 128, 0.1);
            color: var(--status-pending);
        }

        .modal-lg {
            max-width: 800px;
        }

        .order-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .order-info-card {
            background-color: var(--gray-50);
            border-radius: var(--radius-md);
            padding: 1rem;
        }

        .order-info-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--gray-700);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .order-items-table th,
        .order-items-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .order-items-table th {
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .order-total-row {
            font-weight: 600;
        }

        .order-status-history {
            margin-top: 1.5rem;
        }

        .status-timeline {
            margin-top: 1rem;
        }

        .status-item {
            display: flex;
            margin-bottom: 1rem;
            position: relative;
        }

        .status-item:before {
            content: '';
            position: absolute;
            top: 24px;
            left: 10px;
            bottom: -12px;
            width: 2px;
            background-color: var(--gray-200);
        }

        .status-item:last-child:before {
            display: none;
        }

        .status-marker {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: var(--primary-500);
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .status-content {
            flex: 1;
        }

        .status-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .status-date {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-bottom: 0.25rem;
        }

        .status-notes {
            font-size: 0.875rem;
            color: var(--gray-700);
            background-color: var(--gray-50);
            padding: 0.5rem;
            border-radius: var(--radius);
            margin-top: 0.5rem;
        }
    `;
    document.head.appendChild(style);

    // View order details
    const viewButtons = document.querySelectorAll('.view-order');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.id;
            const modal = document.getElementById('viewOrderModal');
            const loadingSpinner = modal.querySelector('.loading-spinner');
            const orderDetails = modal.querySelector('.order-details');

            // Show modal
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Show loading spinner
            loadingSpinner.style.display = 'flex';
            orderDetails.style.display = 'none';

            // Fetch order details
            fetch(`/ayskrimWebsite/api/admin/orders/get.php?id=${orderId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Hide loading spinner
                    loadingSpinner.style.display = 'none';
                    orderDetails.style.display = 'block';

                    if (!data.success) {
                        throw new Error(data.error || 'Failed to load order details');
                    }

                    // Get order data from response
                    const order = data.order;

                    // Format order status for CSS class
                    const orderStatusClass = order.order_status.toLowerCase().replace(/ /g, '-');
                    const paymentStatusClass = order.payment_status.toLowerCase();

                    // Example of populating order details
                    let html = `
                        <div class="order-details-grid">
                            <div class="order-info-card">
                                <div class="order-info-title">Order Information</div>
                                <div><strong>Order ID:</strong> #${order.id}</div>
                                <div><strong>Date:</strong> ${new Date(order.created_at).toLocaleString()}</div>
                                <div><strong>Status:</strong> <span class="status-badge ${orderStatusClass}">${order.order_status}</span></div>
                                <div><strong>Payment Status:</strong> <span class="status-badge ${paymentStatusClass}">${order.payment_status}</span></div>
                                <div><strong>Payment Method:</strong> ${order.payment_method || 'N/A'}</div>
                                <div><strong>Total Amount:</strong> ₱${parseFloat(order.total_amount).toFixed(2)}</div>
                            </div>

                            <div class="order-info-card">
                                <div class="order-info-title">Customer Information</div>
                                <div><strong>Name:</strong> ${order.customer.full_name}</div>
                                <div><strong>Email:</strong> ${order.customer.email}</div>
                                <div><strong>Phone:</strong> ${order.customer.phone || 'N/A'}</div>
                                <div><strong>Address:</strong> ${order.shipping_address}</div>
                            </div>
                        </div>

                        <div class="order-items">
                            <div class="order-info-title">Order Items</div>
                            <table class="order-items-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    // Add order items
                    if (order.items && order.items.length > 0) {
                        order.items.forEach(item => {
                            const itemPrice = parseFloat(item.price);
                            const itemQuantity = parseInt(item.quantity);
                            const itemTotal = itemPrice * itemQuantity;

                            html += `
                                <tr>
                                    <td>${item.product_name}</td>
                                    <td>₱${itemPrice.toFixed(2)}</td>
                                    <td>${itemQuantity}</td>
                                    <td>₱${itemTotal.toFixed(2)}</td>
                                </tr>
                            `;
                        });
                    } else {
                        html += `
                            <tr>
                                <td colspan="4" class="text-center">No items found</td>
                            </tr>
                        `;
                    }

                    // Add order totals
                    const subtotal = parseFloat(order.subtotal || 0);
                    const shippingFee = parseFloat(order.shipping_fee || 0);
                    const totalAmount = parseFloat(order.total_amount);

                    html += `
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                        <td>₱${subtotal.toFixed(2)}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Shipping Fee:</strong></td>
                                        <td>₱${shippingFee.toFixed(2)}</td>
                                    </tr>
                                    <tr class="order-total-row">
                                        <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                        <td>₱${totalAmount.toFixed(2)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    `;

                    // Add status history if available
                    if (order.status_history && order.status_history.length > 0) {
                        html += `
                            <div class="order-status-history">
                                <div class="order-info-title">Status History</div>
                                <div class="status-timeline">
                        `;

                        order.status_history.forEach(status => {
                            html += `
                                <div class="status-item">
                                    <div class="status-marker"></div>
                                    <div class="status-content">
                                        <div class="status-title">${status.status} (Payment: ${status.payment_status})</div>
                                        <div class="status-date">${new Date(status.created_at).toLocaleString()} by ${status.admin_name}</div>
                                        ${status.notes ? `<div class="status-notes">${status.notes}</div>` : ''}
                                    </div>
                                </div>
                            `;
                        });

                        html += `
                                </div>
                            </div>
                        `;
                    }

                    // Add action buttons
                    html += `
                        <div class="form-actions mt-4">
                            <button type="button" class="btn btn-secondary modal-close">Close</button>
                            <button type="button" class="btn btn-primary update-status-btn" data-id="${order.id}" data-status="${order.order_status}" data-payment="${order.payment_status}">
                                <i class="fas fa-edit"></i> Update Status
                            </button>
                            <button type="button" class="btn btn-info print-order">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    `;

                    orderDetails.innerHTML = html;

                    // Add event listener to the update status button in the modal
                    const updateStatusBtn = orderDetails.querySelector('.update-status-btn');
                    if (updateStatusBtn) {
                        updateStatusBtn.addEventListener('click', function() {
                            const updateModal = document.getElementById('updateStatusModal');
                            const updateOrderId = document.getElementById('updateOrderId');
                            const orderStatusSelect = document.getElementById('orderStatus');
                            const paymentStatusSelect = document.getElementById('paymentStatus');

                            // Set values
                            updateOrderId.value = this.dataset.id;
                            orderStatusSelect.value = this.dataset.status;
                            paymentStatusSelect.value = this.dataset.payment;

                            // Close view modal
                            modal.classList.remove('active');

                            // Show update modal
                            updateModal.classList.add('active');
                            document.body.style.overflow = 'hidden';
                        });
                    }

                    // Add event listener to the print button
                    const printBtn = orderDetails.querySelector('.print-order');
                    if (printBtn) {
                        printBtn.addEventListener('click', function() {
                            printOrderDetails(order);
                        });
                    }

                    // Add event listener to the close button
                    const closeBtn = orderDetails.querySelector('.modal-close');
                    if (closeBtn) {
                        closeBtn.addEventListener('click', function() {
                            modal.classList.remove('active');
                            document.body.style.overflow = '';
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching order details:', error);
                    loadingSpinner.style.display = 'none';
                    orderDetails.style.display = 'block';
                    orderDetails.innerHTML = `
                        <div class="alert alert-danger">
                            <div class="alert-title">Error</div>
                            <p>Failed to load order details. Please try again.</p>
                            <p class="text-sm">${error.message}</p>
                        </div>
                        <div class="form-actions mt-4">
                            <button type="button" class="btn btn-secondary modal-close">Close</button>
                        </div>
                    `;

                    // Add event listener to the close button
                    const closeBtn = orderDetails.querySelector('.modal-close');
                    if (closeBtn) {
                        closeBtn.addEventListener('click', function() {
                            modal.classList.remove('active');
                            document.body.style.overflow = '';
                        });
                    }
                });
        });
    });

    // Update order status
    const updateButtons = document.querySelectorAll('.update-status');
    updateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.id;
            const currentStatus = this.dataset.status;
            const currentPayment = this.dataset.payment;
            const modal = document.getElementById('updateStatusModal');

            // Show modal
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Set order ID
            document.getElementById('updateOrderId').value = orderId;

            // Set current status
            document.getElementById('orderStatus').value = currentStatus;
            document.getElementById('paymentStatus').value = currentPayment;

            // Clear notes field
            document.getElementById('statusNotes').value = '';
        });
    });

    // Handle update status form submission
    const updateStatusForm = document.getElementById('updateStatusForm');
    if (updateStatusForm) {
        updateStatusForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const modal = document.getElementById('updateStatusModal');

            // Disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

            // Send AJAX request
            fetch('/ayskrimWebsite/api/admin/orders/update.php', {
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

                    // Reload the page after a short delay to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.error || 'Failed to update order status');
                }
            })
            .catch(error => {
                console.error('Error updating order status:', error);

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
                submitButton.innerHTML = 'Update Status';
            });
        });
    }

    // Add event listener to close buttons for modals
    document.querySelectorAll('.modal-close').forEach(button => {
        button.addEventListener('click', function() {
            // Find the closest modal
            const modal = this.closest('.modal-backdrop');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    /**
     * Print order details
     * @param {Object} orderData - The order data to print
     */
    function printOrderDetails(orderData) {
        // Format order status for CSS class
        const orderStatusClass = orderData.order_status.toLowerCase().replace(/ /g, '-');
        const paymentStatusClass = orderData.payment_status.toLowerCase();

        // Create print window
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>Order #${orderData.id} Details</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        padding: 20px;
                        color: #333;
                        line-height: 1.5;
                    }
                    .print-header {
                        text-align: center;
                        margin-bottom: 20px;
                        padding-bottom: 10px;
                        border-bottom: 2px solid #e5e7eb;
                    }
                    .print-header h1 {
                        margin: 0;
                        font-size: 24px;
                    }
                    .print-header p {
                        margin: 5px 0 0;
                        color: #6b7280;
                    }
                    .order-details-grid {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 20px;
                        margin-bottom: 20px;
                    }
                    .order-info-card {
                        background-color: #f9fafb;
                        border-radius: 6px;
                        padding: 15px;
                    }
                    .order-info-title {
                        font-weight: 600;
                        margin-bottom: 10px;
                        color: #374151;
                        font-size: 14px;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                    }
                    .order-items-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }
                    .order-items-table th,
                    .order-items-table td {
                        padding: 10px;
                        text-align: left;
                        border-bottom: 1px solid #e5e7eb;
                    }
                    .order-items-table th {
                        font-weight: 600;
                        color: #374151;
                        font-size: 14px;
                        background-color: #f9fafb;
                    }
                    .order-total-row {
                        font-weight: 600;
                    }
                    .status-badge {
                        display: inline-block;
                        padding: 3px 8px;
                        border-radius: 9999px;
                        font-size: 12px;
                        font-weight: 500;
                    }
                    .status-badge.pending {
                        background-color: rgba(128, 128, 128, 0.1);
                        color: #808080;
                    }
                    .status-badge.preparing {
                        background-color: rgba(255, 165, 0, 0.1);
                        color: #FFA500;
                    }
                    .status-badge.out-for-delivery {
                        background-color: rgba(0, 0, 255, 0.1);
                        color: #0000FF;
                    }
                    .status-badge.delivered {
                        background-color: rgba(0, 128, 0, 0.1);
                        color: #008000;
                    }
                    .status-badge.cancelled {
                        background-color: rgba(255, 0, 0, 0.1);
                        color: #FF0000;
                    }
                    .status-badge.paid {
                        background-color: rgba(0, 128, 0, 0.1);
                        color: #008000;
                    }
                    .status-badge.failed {
                        background-color: rgba(255, 0, 0, 0.1);
                        color: #FF0000;
                    }
                    .status-badge.refunded {
                        background-color: rgba(128, 128, 128, 0.1);
                        color: #808080;
                    }
                    .text-right {
                        text-align: right;
                    }
                    .footer {
                        margin-top: 30px;
                        text-align: center;
                        font-size: 12px;
                        color: #6b7280;
                    }
                    @media print {
                        body {
                            padding: 0;
                        }
                        .no-print {
                            display: none;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="print-header">
                    <h1>Order #${orderData.id}</h1>
                    <p>Generated on ${new Date().toLocaleString()}</p>
                </div>

                <div class="order-details-grid">
                    <div class="order-info-card">
                        <div class="order-info-title">Order Information</div>
                        <div><strong>Order ID:</strong> #${orderData.id}</div>
                        <div><strong>Date:</strong> ${new Date(orderData.created_at).toLocaleString()}</div>
                        <div><strong>Status:</strong> <span class="status-badge ${orderStatusClass}">${orderData.order_status}</span></div>
                        <div><strong>Payment Status:</strong> <span class="status-badge ${paymentStatusClass}">${orderData.payment_status}</span></div>
                        <div><strong>Payment Method:</strong> ${orderData.payment_method || 'N/A'}</div>
                        <div><strong>Total Amount:</strong> ₱${parseFloat(orderData.total_amount).toFixed(2)}</div>
                    </div>

                    <div class="order-info-card">
                        <div class="order-info-title">Customer Information</div>
                        <div><strong>Name:</strong> ${orderData.customer.full_name}</div>
                        <div><strong>Email:</strong> ${orderData.customer.email}</div>
                        <div><strong>Phone:</strong> ${orderData.customer.phone || 'N/A'}</div>
                        <div><strong>Address:</strong> ${orderData.shipping_address}</div>
                    </div>
                </div>

                <div class="order-items">
                    <div class="order-info-title">Order Items</div>
                    <table class="order-items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
        `);

        // Add order items
        if (orderData.items && orderData.items.length > 0) {
            orderData.items.forEach(item => {
                const itemPrice = parseFloat(item.price);
                const itemQuantity = parseInt(item.quantity);
                const itemTotal = itemPrice * itemQuantity;

                printWindow.document.write(`
                    <tr>
                        <td>${item.product_name}</td>
                        <td>₱${itemPrice.toFixed(2)}</td>
                        <td>${itemQuantity}</td>
                        <td>₱${itemTotal.toFixed(2)}</td>
                    </tr>
                `);
            });
        } else {
            printWindow.document.write(`
                <tr>
                    <td colspan="4" style="text-align: center;">No items found</td>
                </tr>
            `);
        }

        // Add order totals
        const subtotal = parseFloat(orderData.subtotal || 0);
        const shippingFee = parseFloat(orderData.shipping_fee || 0);
        const totalAmount = parseFloat(orderData.total_amount);

        printWindow.document.write(`
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                <td>₱${subtotal.toFixed(2)}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Shipping Fee:</strong></td>
                                <td>₱${shippingFee.toFixed(2)}</td>
                            </tr>
                            <tr class="order-total-row">
                                <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                <td>₱${totalAmount.toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="footer">
                    <p>Rey's Davao Icecream Delivery</p>
                    <p>Thank you for your order!</p>
                </div>

                <div class="no-print" style="margin-top: 20px; text-align: center;">
                    <button onclick="window.print();" style="padding: 8px 16px; background-color: #0ea5e9; color: white; border: none; border-radius: 4px; cursor: pointer;">Print Order</button>
                    <button onclick="window.close();" style="padding: 8px 16px; background-color: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">Close</button>
                </div>
            </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();
    }

    // Export orders
    const exportButton = document.getElementById('exportOrders');
    if (exportButton) {
        exportButton.addEventListener('click', function() {
            // Get table data
            const table = document.getElementById('ordersTable');
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());

            // Skip the actions column
            const skipColumns = [6]; // Actions column
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
            link.setAttribute('download', 'orders.csv');
            link.style.display = 'none';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
});
