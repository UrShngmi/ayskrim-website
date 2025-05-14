// Admin dashboard logic
document.addEventListener('DOMContentLoaded', function() {
  // Sidebar toggle functionality
  const sidebarToggle = document.querySelector('.sidebar-toggle');
  const adminSidebar = document.querySelector('.admin-sidebar');
  const adminMain = document.querySelector('.admin-main');

  if (sidebarToggle && adminSidebar && adminMain) {
    sidebarToggle.addEventListener('click', function() {
      adminSidebar.classList.toggle('collapsed');
      adminMain.classList.toggle('expanded');

      // Store sidebar state in localStorage
      const isCollapsed = adminSidebar.classList.contains('collapsed');
      localStorage.setItem('admin-sidebar-collapsed', isCollapsed);
    });

    // Check localStorage for sidebar state
    const isCollapsed = localStorage.getItem('admin-sidebar-collapsed') === 'true';
    if (isCollapsed) {
      adminSidebar.classList.add('collapsed');
      adminMain.classList.add('expanded');
    }
  }

  // Initialize all dropdown functionality
  function initializeDropdowns() {
    // All dropdown toggles
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
      toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = this.closest('.dropdown').querySelector('.dropdown-menu');

        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
          if (menu !== dropdown) {
            menu.classList.remove('show');
          }
        });

        // Toggle current dropdown
        dropdown.classList.toggle('show');
      });
    });
  }

  // Initialize dropdowns
  initializeDropdowns();

  // Close dropdowns when clicking outside
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
      document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
        menu.classList.remove('show');
      });
    }
  });

  // Custom event for when content is loaded via AJAX
  document.addEventListener('contentLoaded', function() {
    initializeDropdowns();
  });

  // Mobile sidebar toggle
  const mobileSidebarToggle = document.querySelector('.mobile-sidebar-toggle');
  if (mobileSidebarToggle && adminSidebar) {
    mobileSidebarToggle.addEventListener('click', function() {
      adminSidebar.classList.toggle('mobile-visible');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
      if (window.innerWidth <= 768 &&
          adminSidebar.classList.contains('mobile-visible') &&
          !adminSidebar.contains(event.target) &&
          !mobileSidebarToggle.contains(event.target)) {
        adminSidebar.classList.remove('mobile-visible');
      }
    });
  }

  // Modal functionality
  function initializeModals() {
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    const modalCloseButtons = document.querySelectorAll('[data-modal-close], .modal-close');

    modalTriggers.forEach(trigger => {
      trigger.addEventListener('click', () => {
        const modalId = trigger.getAttribute('data-modal-target');
        const modal = document.getElementById(modalId);
        if (modal) {
          modal.classList.add('active');
          document.body.style.overflow = 'hidden';
        }
      });
    });

    modalCloseButtons.forEach(button => {
      button.addEventListener('click', () => {
        const modal = button.closest('.modal-backdrop');
        if (modal) {
          modal.classList.remove('active');
          document.body.style.overflow = '';
        }
      });
    });
  }

  // Initialize modals
  initializeModals();

  // Close modal when clicking outside
  document.addEventListener('click', (event) => {
    if (event.target.classList.contains('modal-backdrop')) {
      event.target.classList.remove('active');
      document.body.style.overflow = '';
    }
  });

  // Re-initialize modals when content is loaded via AJAX
  document.addEventListener('contentLoaded', function() {
    initializeModals();
  });

  // Initialize tooltips
  const tooltips = document.querySelectorAll('[data-tooltip]');
  tooltips.forEach(tooltip => {
    tooltip.addEventListener('mouseenter', () => {
      const tooltipText = tooltip.getAttribute('data-tooltip');
      const tooltipEl = document.createElement('div');
      tooltipEl.classList.add('tooltip');
      tooltipEl.textContent = tooltipText;
      document.body.appendChild(tooltipEl);

      const rect = tooltip.getBoundingClientRect();
      tooltipEl.style.top = `${rect.top - tooltipEl.offsetHeight - 5}px`;
      tooltipEl.style.left = `${rect.left + (rect.width / 2) - (tooltipEl.offsetWidth / 2)}px`;
      tooltipEl.style.opacity = '1';
    });

    tooltip.addEventListener('mouseleave', () => {
      const tooltipEl = document.querySelector('.tooltip');
      if (tooltipEl) {
        tooltipEl.remove();
      }
    });
  });

  // Form validation
  const forms = document.querySelectorAll('.needs-validation');
  forms.forEach(form => {
    form.addEventListener('submit', (event) => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }

      form.classList.add('was-validated');
    }, false);
  });

  // Initialize date pickers
  const datePickers = document.querySelectorAll('.date-picker');
  if (datePickers.length > 0 && typeof flatpickr !== 'undefined') {
    datePickers.forEach(picker => {
      flatpickr(picker, {
        dateFormat: 'Y-m-d',
        allowInput: true
      });
    });
  }

  // Initialize time pickers
  const timePickers = document.querySelectorAll('.time-picker');
  if (timePickers.length > 0 && typeof flatpickr !== 'undefined') {
    timePickers.forEach(picker => {
      flatpickr(picker, {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        time_24hr: true,
        allowInput: true
      });
    });
  }

  // Initialize select2 dropdowns
  const enhancedSelects = document.querySelectorAll('.select2');
  if (enhancedSelects.length > 0 && typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
    jQuery(enhancedSelects).select2({
      width: '100%'
    });
  }

  // File input preview
  const fileInputs = document.querySelectorAll('.custom-file-input');
  fileInputs.forEach(input => {
    input.addEventListener('change', (event) => {
      const fileName = event.target.files[0]?.name || 'No file chosen';
      const label = input.nextElementSibling;
      if (label) {
        label.textContent = fileName;
      }

      // Image preview if it's an image
      const preview = input.closest('.form-group').querySelector('.image-preview');
      if (preview && event.target.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
          preview.src = e.target.result;
          preview.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
      }
    });
  });

  // Table row actions
  function initializeTableActions() {
    const tableActionButtons = document.querySelectorAll('.table-action-menu');
    tableActionButtons.forEach(button => {
      button.addEventListener('click', (event) => {
        event.stopPropagation();
        const menu = button.nextElementSibling;
        if (menu) {
          menu.classList.toggle('show');

          // Close other open menus
          document.querySelectorAll('.table-action-dropdown.show').forEach(dropdown => {
            if (dropdown !== menu) {
              dropdown.classList.remove('show');
            }
          });
        }
      });
    });
  }

  // Initialize table actions
  initializeTableActions();

  // Close dropdown menus when clicking outside
  document.addEventListener('click', () => {
    document.querySelectorAll('.table-action-dropdown.show').forEach(dropdown => {
      dropdown.classList.remove('show');
    });
  });

  // Re-initialize table actions when content is loaded via AJAX
  document.addEventListener('contentLoaded', function() {
    initializeTableActions();
  });

  // Alerts auto-close
  const autoCloseAlerts = document.querySelectorAll('.alert[data-auto-close]');
  autoCloseAlerts.forEach(alert => {
    const delay = parseInt(alert.getAttribute('data-auto-close'), 10) || 5000;
    setTimeout(() => {
      alert.classList.add('fade-out');
      setTimeout(() => {
        alert.remove();
      }, 300);
    }, delay);
  });

  // Alert close buttons
  const alertCloseButtons = document.querySelectorAll('.alert .alert-close');
  alertCloseButtons.forEach(button => {
    button.addEventListener('click', () => {
      const alert = button.closest('.alert');
      if (alert) {
        alert.classList.add('fade-out');
        setTimeout(() => {
          alert.remove();
        }, 300);
      }
    });
  });

  // Initialize charts if Chart.js is available
  if (typeof Chart !== 'undefined') {
    initializeCharts();
  }

  // Confirmation dialogs
  const confirmButtons = document.querySelectorAll('[data-confirm]');
  confirmButtons.forEach(button => {
    button.addEventListener('click', (event) => {
      const message = button.getAttribute('data-confirm') || 'Are you sure you want to perform this action?';
      if (!confirm(message)) {
        event.preventDefault();
        event.stopPropagation();
      }
    });
  });

  // Toggle password visibility
  const passwordToggles = document.querySelectorAll('.password-toggle');
  passwordToggles.forEach(toggle => {
    toggle.addEventListener('click', () => {
      const input = toggle.previousElementSibling;
      if (input && input.type === 'password') {
        input.type = 'text';
        toggle.innerHTML = '<i class="fas fa-eye-slash"></i>';
      } else if (input) {
        input.type = 'password';
        toggle.innerHTML = '<i class="fas fa-eye"></i>';
      }
    });
  });

  // Bulk selection in tables
  const bulkCheckAll = document.querySelector('.bulk-check-all');
  if (bulkCheckAll) {
    bulkCheckAll.addEventListener('change', () => {
      const checkboxes = document.querySelectorAll('.bulk-check-item');
      checkboxes.forEach(checkbox => {
        checkbox.checked = bulkCheckAll.checked;
      });

      updateBulkActionButtons();
    });

    // Individual checkboxes
    const bulkCheckItems = document.querySelectorAll('.bulk-check-item');
    bulkCheckItems.forEach(checkbox => {
      checkbox.addEventListener('change', () => {
        updateBulkActionButtons();

        // Update "check all" state
        const allChecked = [...bulkCheckItems].every(item => item.checked);
        const someChecked = [...bulkCheckItems].some(item => item.checked);

        if (bulkCheckAll) {
          bulkCheckAll.checked = allChecked;
          bulkCheckAll.indeterminate = someChecked && !allChecked;
        }
      });
    });
  }

  // Update bulk action buttons state
  function updateBulkActionButtons() {
    const bulkActionButtons = document.querySelectorAll('.bulk-action');
    const checkedItems = document.querySelectorAll('.bulk-check-item:checked');

    bulkActionButtons.forEach(button => {
      if (checkedItems.length > 0) {
        button.removeAttribute('disabled');
      } else {
        button.setAttribute('disabled', 'disabled');
      }
    });
  }

  // Initialize sortable tables
  const sortableTables = document.querySelectorAll('.sortable');
  sortableTables.forEach(table => {
    const headers = table.querySelectorAll('th[data-sort]');
    headers.forEach(header => {
      header.addEventListener('click', () => {
        const column = header.getAttribute('data-sort');
        const direction = header.getAttribute('data-direction') === 'asc' ? 'desc' : 'asc';

        // Reset all headers
        headers.forEach(h => {
          h.setAttribute('data-direction', '');
          h.classList.remove('sort-asc', 'sort-desc');
        });

        // Set current header
        header.setAttribute('data-direction', direction);
        header.classList.add(direction === 'asc' ? 'sort-asc' : 'sort-desc');

        // Sort the table
        sortTable(table, column, direction);
      });
    });
  });

  // Function to sort table
  function sortTable(table, column, direction) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    const sortedRows = rows.sort((a, b) => {
      const aValue = a.querySelector(`td[data-column="${column}"]`)?.textContent.trim() || '';
      const bValue = b.querySelector(`td[data-column="${column}"]`)?.textContent.trim() || '';

      // Check if values are numbers
      const aNum = parseFloat(aValue);
      const bNum = parseFloat(bValue);

      if (!isNaN(aNum) && !isNaN(bNum)) {
        return direction === 'asc' ? aNum - bNum : bNum - aNum;
      }

      // Otherwise sort as strings
      return direction === 'asc'
        ? aValue.localeCompare(bValue)
        : bValue.localeCompare(aValue);
    });

    // Clear and re-append rows
    while (tbody.firstChild) {
      tbody.removeChild(tbody.firstChild);
    }

    sortedRows.forEach(row => {
      tbody.appendChild(row);
    });
  }
});

// Function to initialize charts
function initializeCharts() {
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

  // Expenses Chart
  const expensesChartEl = document.getElementById('expensesChart');
  if (expensesChartEl) {
    new Chart(expensesChartEl, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Expenses',
          data: [25, 20, 30, 22, 17, 29],
          backgroundColor: '#ef4444'
        }, {
          label: 'Revenue',
          data: [45, 40, 50, 42, 37, 49],
          backgroundColor: '#10b981'
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

  // Top Products Chart
  const productsChartEl = document.getElementById('productsChart');
  if (productsChartEl) {
    new Chart(productsChartEl, {
      type: 'bar',
      data: {
        labels: ['Vanilla', 'Chocolate', 'Strawberry', 'Mango', 'Mint'],
        datasets: [{
          label: 'Sales',
          data: [120, 100, 80, 60, 50],
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
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            grid: {
              drawBorder: false
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
}

// Function to format currency
function formatCurrency(amount, currency = 'PHP') {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: currency
  }).format(amount);
}

// Function to format date
function formatDate(dateString, format = 'medium') {
  const date = new Date(dateString);

  switch (format) {
    case 'short':
      return date.toLocaleDateString('en-PH');
    case 'medium':
      return date.toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    case 'long':
      return date.toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long'
      });
    case 'time':
      return date.toLocaleTimeString('en-PH', {
        hour: '2-digit',
        minute: '2-digit'
      });
    case 'datetime':
      return date.toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    default:
      return date.toLocaleDateString('en-PH');
  }
}

// Function to show notification
function showNotification(message, type = 'info', duration = 5000) {
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
    <div class="notification-content">
      <div class="notification-message">${message}</div>
      <button class="notification-close">&times;</button>
    </div>
  `;

  document.body.appendChild(notification);

  // Show notification with animation
  setTimeout(() => {
    notification.classList.add('show');
  }, 10);

  // Auto-close after duration
  const timeout = setTimeout(() => {
    closeNotification(notification);
  }, duration);

  // Close button
  const closeButton = notification.querySelector('.notification-close');
  closeButton.addEventListener('click', () => {
    clearTimeout(timeout);
    closeNotification(notification);
  });

  return notification;
}

// Function to close notification
function closeNotification(notification) {
  notification.classList.remove('show');
  setTimeout(() => {
    notification.remove();
  }, 300);
}

// Function to confirm action
function confirmAction(message, callback) {
  const confirmed = confirm(message);
  if (confirmed && typeof callback === 'function') {
    callback();
  }
  return confirmed;
}

// Function to handle AJAX requests
function ajaxRequest(url, method = 'GET', data = null, successCallback = null, errorCallback = null) {
  const xhr = new XMLHttpRequest();
  xhr.open(method, url, true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

  xhr.onload = function() {
    if (xhr.status >= 200 && xhr.status < 300) {
      let response;
      try {
        response = JSON.parse(xhr.responseText);
      } catch (e) {
        response = xhr.responseText;
      }

      if (typeof successCallback === 'function') {
        successCallback(response);
      }
    } else {
      if (typeof errorCallback === 'function') {
        errorCallback(xhr.status, xhr.statusText);
      }
    }
  };

  xhr.onerror = function() {
    if (typeof errorCallback === 'function') {
      errorCallback(xhr.status, xhr.statusText);
    }
  };

  if (data) {
    xhr.send(JSON.stringify(data));
  } else {
    xhr.send();
  }
}

// Function to debounce events
function debounce(func, wait = 300) {
  let timeout;
  return function(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      func.apply(this, args);
    }, wait);
  };
}
