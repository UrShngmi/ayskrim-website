/**
 * Expenses Management JavaScript
 * This file contains code specific to the admin expenses management page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add CSS for expenses-specific elements
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
    `;
    document.head.appendChild(style);
    
    // Initialize date pickers
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.date-picker', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    }
    
    // Delete expense confirmation
    const deleteButtons = document.querySelectorAll('.delete-expense');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const expenseId = this.dataset.id;
            const description = this.dataset.description;
            
            if (confirm(`Are you sure you want to delete the expense "${description}"? This action cannot be undone.`)) {
                window.location.href = `?action=delete&id=${expenseId}`;
            }
        });
    });
    
    // Edit expense
    const editButtons = document.querySelectorAll('.edit-expense');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const expenseId = this.dataset.id;
            const modal = document.getElementById('editExpenseModal');
            const form = modal.querySelector('form');
            const loadingSpinner = modal.querySelector('.loading-spinner');
            
            // Show modal
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Set expense ID
            document.getElementById('editExpenseId').value = expenseId;
            
            // Show loading spinner
            loadingSpinner.style.display = 'flex';
            
            // Fetch expense data
            fetch(`/ayskrimWebsite/api/admin/expenses/get.php?id=${expenseId}`)
                .then(response => response.json())
                .then(data => {
                    // Hide loading spinner
                    loadingSpinner.style.display = 'none';
                    
                    // Populate form fields
                    // This is a placeholder - in a real implementation, you would populate all form fields
                    // with the data returned from the API
                    console.log('Expense data:', data);
                    
                    // Example of populating form fields
                    // form.querySelector('#editExpenseAmount').value = data.amount;
                    // form.querySelector('#editExpenseCategory').value = data.category_id;
                    // etc.
                })
                .catch(error => {
                    console.error('Error fetching expense data:', error);
                    loadingSpinner.innerHTML = '<div class="alert alert-danger">Error loading expense data. Please try again.</div>';
                });
        });
    });
    
    // Export expenses
    const exportButton = document.getElementById('exportExpenses');
    if (exportButton) {
        exportButton.addEventListener('click', function() {
            // Get table data
            const table = document.getElementById('expensesTable');
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
            
            // Skip the actions column
            const skipColumns = [7]; // Actions column
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
            link.setAttribute('download', 'expenses.csv');
            link.style.display = 'none';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
    
    // Add new category button
    const addCategoryButton = document.getElementById('addCategoryButton');
    if (addCategoryButton) {
        addCategoryButton.addEventListener('click', function() {
            const categoryName = prompt('Enter new category name:');
            if (categoryName && categoryName.trim()) {
                // Send AJAX request to add new category
                fetch('/ayskrimWebsite/api/admin/expenses/add-category.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ name: categoryName.trim() })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Add new option to category select
                        const categorySelect = document.getElementById('expenseCategory');
                        const option = document.createElement('option');
                        option.value = data.category_id;
                        option.textContent = categoryName.trim();
                        categorySelect.appendChild(option);
                        categorySelect.value = data.category_id;
                        
                        // Also add to filter select
                        const filterSelect = document.getElementById('categoryFilter');
                        const filterOption = document.createElement('option');
                        filterOption.value = data.category_id;
                        filterOption.textContent = categoryName.trim();
                        filterSelect.appendChild(filterOption);
                        
                        showNotification('Category added successfully', 'success');
                    } else {
                        showNotification('Error adding category: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error adding category:', error);
                    showNotification('Error adding category', 'danger');
                });
            }
        });
    }
});
