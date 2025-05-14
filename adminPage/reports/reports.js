/**
 * Reports & Analytics JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date pickers
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.date-picker', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    }

    // Handle period change
    const periodSelect = document.getElementById('period');
    const dateRangeFields = document.querySelectorAll('.date-range');

    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            const isCustom = this.value === 'custom';
            
            dateRangeFields.forEach(field => {
                field.classList.toggle('hidden', !isCustom);
            });
            
            if (!isCustom) {
                // Set date range based on selected period
                const today = new Date();
                let startDate = new Date();
                
                switch (this.value) {
                    case 'today':
                        startDate = new Date(today);
                        break;
                    case 'week':
                        startDate.setDate(today.getDate() - 7);
                        break;
                    case 'month':
                        startDate.setMonth(today.getMonth() - 1);
                        break;
                    case 'quarter':
                        startDate.setMonth(today.getMonth() - 3);
                        break;
                    case 'year':
                        startDate.setFullYear(today.getFullYear() - 1);
                        break;
                }
                
                // Format dates for hidden inputs
                document.getElementById('start-date').value = formatDate(startDate);
                document.getElementById('end-date').value = formatDate(today);
            }
        });
    }

    // Handle report type change
    const reportTypeSelect = document.getElementById('report-type');
    const reportSections = document.querySelectorAll('.report-section');

    if (reportTypeSelect) {
        reportTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            reportSections.forEach(section => {
                section.classList.toggle('active', section.id === selectedType + '-report');
            });
        });
    }

    // Export report button
    const exportButton = document.querySelector('.export-report-btn');
    if (exportButton) {
        exportButton.addEventListener('click', function() {
            exportCurrentReport();
        });
    }

    // Initialize charts
    initializeCharts();
});

/**
 * Format date as YYYY-MM-DD
 */
function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * Initialize all charts
 */
function initializeCharts() {
    // Only initialize if Chart.js is available
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded');
        return;
    }

    // Set default chart options
    Chart.defaults.font.family = "'Poppins', sans-serif";
    Chart.defaults.color = '#4b5563';
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.7)';
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 4;
    Chart.defaults.plugins.tooltip.titleFont.weight = 'bold';

    // Initialize sales chart
    initializeSalesChart();
    
    // Initialize category sales chart
    initializeCategorySalesChart();
    
    // Initialize product sales chart
    initializeProductSalesChart();
    
    // Initialize booking chart
    initializeBookingChart();
    
    // Initialize expense chart
    initializeExpenseChart();
    
    // Initialize expense category chart
    initializeExpenseCategoryChart();
    
    // Initialize profit/loss chart
    initializeProfitLossChart();
    
    // Initialize profit margin chart
    initializeProfitMarginChart();
}

/**
 * Initialize sales chart
 */
function initializeSalesChart() {
    const salesChartEl = document.getElementById('salesChart');
    if (!salesChartEl || !window.salesChartLabels || !window.salesChartData) return;

    new Chart(salesChartEl, {
        type: 'line',
        data: {
            labels: window.salesChartLabels,
            datasets: [{
                label: 'Revenue',
                data: window.salesChartData,
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14, 165, 233, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: ₱' + context.raw.toLocaleString('en-PH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString('en-PH');
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Initialize category sales chart
 */
function initializeCategorySalesChart() {
    const categorySalesChartEl = document.getElementById('categorySalesChart');
    if (!categorySalesChartEl || !window.categoryLabels || !window.categoryData) return;

    new Chart(categorySalesChartEl, {
        type: 'doughnut',
        data: {
            labels: window.categoryLabels,
            datasets: [{
                data: window.categoryData,
                backgroundColor: [
                    '#0ea5e9',
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6',
                    '#ec4899',
                    '#06b6d4',
                    '#14b8a6',
                    '#f97316',
                    '#6366f1'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 15,
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw.toLocaleString('en-PH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            return context.label + ': ₱' + value;
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });
}

/**
 * Initialize product sales chart
 */
function initializeProductSalesChart() {
    const productSalesChartEl = document.getElementById('productSalesChart');
    if (!productSalesChartEl || !window.productLabels || !window.productData) return;

    new Chart(productSalesChartEl, {
        type: 'bar',
        data: {
            labels: window.productLabels,
            datasets: [{
                label: 'Revenue',
                data: window.productData,
                backgroundColor: '#0ea5e9'
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: ₱' + context.raw.toLocaleString('en-PH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString('en-PH');
                        }
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Initialize booking chart
 */
function initializeBookingChart() {
    const bookingChartEl = document.getElementById('bookingChart');
    if (!bookingChartEl || !window.bookingLabels || !window.bookingData) return;

    new Chart(bookingChartEl, {
        type: 'line',
        data: {
            labels: window.bookingLabels,
            datasets: [{
                label: 'Booking Revenue',
                data: window.bookingData,
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: ₱' + context.raw.toLocaleString('en-PH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString('en-PH');
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Initialize expense chart
 */
function initializeExpenseChart() {
    const expenseChartEl = document.getElementById('expenseChart');
    if (!expenseChartEl || !window.salesChartLabels || !window.expenseChartData) return;

    new Chart(expenseChartEl, {
        type: 'line',
        data: {
            labels: window.salesChartLabels,
            datasets: [{
                label: 'Expenses',
                data: window.expenseChartData,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return 'Expense: ₱' + context.raw.toLocaleString('en-PH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString('en-PH');
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Initialize expense category chart
 */
function initializeExpenseCategoryChart() {
    const expenseCategoryChartEl = document.getElementById('expenseCategoryChart');
    if (!expenseCategoryChartEl || !window.expenseCategoryLabels || !window.expenseCategoryData) return;

    new Chart(expenseCategoryChartEl, {
        type: 'pie',
        data: {
            labels: window.expenseCategoryLabels,
            datasets: [{
                data: window.expenseCategoryData,
                backgroundColor: [
                    '#ef4444',
                    '#f97316',
                    '#f59e0b',
                    '#10b981',
                    '#06b6d4',
                    '#0ea5e9',
                    '#8b5cf6',
                    '#ec4899',
                    '#6366f1',
                    '#14b8a6'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 15,
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw.toLocaleString('en-PH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            return context.label + ': ₱' + value;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize profit/loss chart
 */
function initializeProfitLossChart() {
    const profitLossChartEl = document.getElementById('profitLossChart');
    if (!profitLossChartEl || !window.salesChartLabels || !window.salesChartData || !window.expenseChartData) return;

    new Chart(profitLossChartEl, {
        type: 'bar',
        data: {
            labels: window.salesChartLabels,
            datasets: [
                {
                    label: 'Revenue',
                    data: window.salesChartData,
                    backgroundColor: '#0ea5e9'
                },
                {
                    label: 'Expenses',
                    data: window.expenseChartData,
                    backgroundColor: '#ef4444'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ₱' + context.raw.toLocaleString('en-PH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString('en-PH');
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Initialize profit margin chart
 */
function initializeProfitMarginChart() {
    const profitMarginChartEl = document.getElementById('profitMarginChart');
    if (!profitMarginChartEl || !window.salesChartLabels || !window.salesChartData || !window.expenseChartData) return;

    // Calculate profit margin for each day
    const profitMarginData = window.salesChartData.map((revenue, index) => {
        const expense = window.expenseChartData[index] || 0;
        return revenue > 0 ? ((revenue - expense) / revenue) * 100 : 0;
    });

    new Chart(profitMarginChartEl, {
        type: 'line',
        data: {
            labels: window.salesChartLabels,
            datasets: [{
                label: 'Profit Margin',
                data: profitMarginData,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return 'Profit Margin: ' + context.raw.toFixed(1) + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Export current report to CSV
 */
function exportCurrentReport() {
    // Get current report type
    const reportType = document.getElementById('report-type').value;
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    
    // Prepare CSV data based on report type
    let csvData = [];
    let filename = '';
    
    switch (reportType) {
        case 'sales':
            filename = `sales_report_${startDate}_to_${endDate}.csv`;
            csvData = prepareSalesReportCSV();
            break;
        case 'products':
            filename = `product_performance_${startDate}_to_${endDate}.csv`;
            csvData = prepareProductReportCSV();
            break;
        case 'bookings':
            filename = `booking_analytics_${startDate}_to_${endDate}.csv`;
            csvData = prepareBookingReportCSV();
            break;
        case 'expenses':
            filename = `expense_report_${startDate}_to_${endDate}.csv`;
            csvData = prepareExpenseReportCSV();
            break;
        case 'profit':
            filename = `profit_loss_${startDate}_to_${endDate}.csv`;
            csvData = prepareProfitReportCSV();
            break;
    }
    
    // Create and download CSV file
    if (csvData.length > 0) {
        const csvContent = csvData.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.display = 'none';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

/**
 * Prepare sales report CSV data
 */
function prepareSalesReportCSV() {
    if (!window.salesChartLabels || !window.salesChartData) return [];
    
    // Create header row
    const csvData = ['Date,Revenue'];
    
    // Add data rows
    window.salesChartLabels.forEach((label, index) => {
        const revenue = window.salesChartData[index].toFixed(2);
        csvData.push(`"${label}","${revenue}"`);
    });
    
    return csvData;
}

/**
 * Prepare product report CSV data
 */
function prepareProductReportCSV() {
    // Get table data
    const table = document.querySelector('#product-report .admin-table');
    if (!table) return [];
    
    // Get headers
    const headers = Array.from(table.querySelectorAll('thead th'))
        .map(th => th.textContent.trim());
    
    // Create header row
    const csvData = [headers.join(',')];
    
    // Add data rows
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    rows.forEach(row => {
        const cells = Array.from(row.querySelectorAll('td'))
            .map(cell => {
                // Get text content, removing any HTML
                let text = cell.textContent.trim();
                // Escape double quotes
                text = text.replace(/"/g, '""');
                return `"${text}"`;
            });
        
        csvData.push(cells.join(','));
    });
    
    return csvData;
}
