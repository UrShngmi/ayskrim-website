/**
 * Dashboard specific JavaScript
 * This file contains code specific to the admin dashboard page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add additional dashboard-specific functionality here
    
    // Quick actions hover effect
    const quickActions = document.querySelectorAll('.quick-action-card');
    quickActions.forEach(action => {
        action.addEventListener('mouseenter', function() {
            this.classList.add('hover');
        });
        
        action.addEventListener('mouseleave', function() {
            this.classList.remove('hover');
        });
    });
    
    // Add CSS for quick actions
    const style = document.createElement('style');
    style.textContent = `
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .quick-action-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background-color: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            transition: transform var(--transition-fast) ease, box-shadow var(--transition-fast) ease;
            text-decoration: none;
            color: var(--gray-700);
        }
        
        .quick-action-card:hover, .quick-action-card.hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .quick-action-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        .quick-action-icon.blue {
            background-color: var(--primary-100);
            color: var(--primary-600);
        }
        
        .quick-action-icon.green {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .quick-action-icon.yellow {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        .quick-action-icon.red {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        
        .quick-action-title {
            font-weight: 500;
            font-size: 1rem;
        }
        
        .grid-cols-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .grid-cols-2 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .p-0 {
            padding: 0 !important;
        }
        
        .py-4 {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }
        
        .text-center {
            text-align: center !important;
        }
    `;
    document.head.appendChild(style);
    
    // Initialize dashboard charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initializeDashboardCharts();
    }
});

/**
 * Initialize dashboard-specific charts
 */
function initializeDashboardCharts() {
    // Sales Overview Chart
    const salesChartEl = document.getElementById('salesChart');
    if (salesChartEl) {
        new Chart(salesChartEl, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Sales',
                    data: [65, 59, 80, 81, 56, 55, 40, 60, 75, 85, 90, 100],
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
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
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
    
    // Revenue by Category Chart
    const categoryChartEl = document.getElementById('categoryChart');
    if (categoryChartEl) {
        new Chart(categoryChartEl, {
            type: 'doughnut',
            data: {
                labels: ['Ice Cream', 'Toppings', 'Beverages', 'Desserts'],
                datasets: [{
                    data: [65, 15, 10, 10],
                    backgroundColor: [
                        '#0ea5e9',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '70%'
            }
        });
    }
}
