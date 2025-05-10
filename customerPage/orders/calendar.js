// Function to initialize calendar that can be called from the main orders.js file
function initCalendar() {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    } else {
        console.error('Lucide library not loaded properly');
    }
  
    // State variables
    let currentDate = new Date();
    let selectedDate = null;
    let view = 'calendar'; // Only calendar view is available now
    let statusFilter = 'all';
    let typeFilter = 'all';
    let searchQuery = '';
    let sortOrder = 'newest'; // Default sort order
    let dateRange = null;
    let selectedOrder = null;
    let allDatesWithOrders = [];
    let sampleOrders = [];
  
    // DOM elements
    const loadingContainer = document.getElementById('loading-container');
    const calendarView = document.getElementById('calendar-view');
    const gridView = document.getElementById('grid-view');
    // Calendar button removed
    const calendarGrid = document.getElementById('calendar-grid');
    const ordersGrid = document.getElementById('orders-grid');
    // Search elements removed
    const statusFilterSelect = document.getElementById('status-filter');
    const typeFilterSelect = document.getElementById('type-filter');
    const monthSelect = document.getElementById('month-select');
    const yearSelect = document.getElementById('year-select');
    const prevMonthBtn = document.getElementById('prev-month-btn');
    const nextMonthBtn = document.getElementById('next-month-btn');
    const prevYearBtn = document.getElementById('prev-year-btn');
    const nextYearBtn = document.getElementById('next-year-btn');
    const currentTimeDisplay = document.getElementById('time-display');
    const currentTimeBtn = document.getElementById('current-time');
    const selectedDateContainer = document.getElementById('selected-date-container');
    const selectedDateValue = document.getElementById('selected-date-value');
    const clearSelectedDateBtn = document.getElementById('clear-selected-date');
    const noItemsMessage = document.getElementById('no-items-message');
    const noItemsDescription = document.getElementById('no-items-description');
    const clearAllFiltersBtn = document.getElementById('clear-all-filters');
    const dateRangeBtn = document.getElementById('date-range-btn');
    const dateRangeText = document.getElementById('date-range-text');
    const dateRangePopup = document.getElementById('date-range-popup');
    const clearDateRangeBtn = document.getElementById('clear-date-range');
    const applyDateRangeBtn = document.getElementById('apply-date-range');
    const dateRangeFilterIndicator = document.getElementById('date-range-filter-indicator');
    const clearDateFilterBtn = document.getElementById('clear-date-filter');
    const sortBtn = document.getElementById('sort-btn');
    const sortDropdown = document.getElementById('sort-dropdown');
    const sortOptions = document.querySelectorAll('.sort-option');
    const orderModal = document.getElementById('order-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalDatetime = document.getElementById('modal-datetime');
    const modalStatus = document.getElementById('modal-status');
    const modalType = document.getElementById('modal-type');
    const modalTypeIcon = document.getElementById('modal-type-icon');
    const modalItems = document.getElementById('modal-items');
    const modalAmount = document.getElementById('modal-amount');
    const modalImage = document.getElementById('modal-image');
    const modalCloseBtn = document.getElementById('modal-close-btn');
    const closeModalBtn = document.getElementById('close-modal');
    const modalActionBtn = document.getElementById('modal-action-btn');
  
    // Helper functions
    function formatDate(date, format) {
      const day = date.getDate();
      const month = date.getMonth();
      const year = date.getFullYear();
      const hours = date.getHours();
      const minutes = date.getMinutes();
      const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ];
      const monthNamesShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      
      if (format === 'MMMM d, yyyy') {
        return `${monthNames[month]} ${day}, ${year}`;
      } else if (format === 'MMM d, yyyy') {
        return `${monthNamesShort[month]} ${day}, ${year}`;
      } else if (format === 'h:mm a') {
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const hour = hours % 12 || 12;
        return `${hour}:${minutes.toString().padStart(2, '0')} ${ampm}`;
      } else if (format === 'PPP') {
        return `${monthNames[month]} ${day}, ${year}`;
      } else if (format === 'p') {
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const hour = hours % 12 || 12;
        return `${hour}:${minutes.toString().padStart(2, '0')} ${ampm}`;
      }
      
      return `${month + 1}/${day}/${year}`;
    }
  
    function isSameDay(date1, date2) {
      return date1.getDate() === date2.getDate() &&
             date1.getMonth() === date2.getMonth() &&
             date1.getFullYear() === date2.getFullYear();
    }
  
    function isSameMonth(date1, date2) {
      return date1.getFullYear() === date2.getFullYear() &&
             date1.getMonth() === date2.getMonth();
    }
  
    function isPast(date) {
      const today = new Date();
      // Set hours to 0 for comparing just the date part
      const normalizedDate = new Date(date);
      normalizedDate.setHours(0, 0, 0, 0);
      
      const normalizedToday = new Date(today);
      normalizedToday.setHours(0, 0, 0, 0);
      
      return normalizedDate < normalizedToday;
    }
  
    function isToday(date) {
      const today = new Date();
      return date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear();
    }
  
    function startOfMonth(date) {
      const result = new Date(date);
      result.setDate(1);
      result.setHours(0, 0, 0, 0);
      return result;
    }
  
    function endOfMonth(date) {
      const result = new Date(date);
      result.setMonth(result.getMonth() + 1);
      result.setDate(0);
      result.setHours(23, 59, 59, 999);
      return result;
    }
  
    function startOfWeek(date) {
      const result = new Date(date);
      const day = result.getDay();
      result.setDate(result.getDate() - day);
      result.setHours(0, 0, 0, 0);
      return result;
    }
  
    function endOfWeek(date) {
      const result = new Date(date);
      const day = result.getDay();
      result.setDate(result.getDate() + (6 - day));
      result.setHours(23, 59, 59, 999);
      return result;
    }
  
    function eachDayOfInterval(start, end) {
      const days = [];
      const current = new Date(start);
      
      while (current <= end) {
        days.push(new Date(current));
        current.setDate(current.getDate() + 1);
      }
      
      return days;
    }
  
    function parseISO(dateString) {
      return new Date(dateString);
    }
  
    function startOfDay(date) {
      const result = new Date(date);
      result.setHours(0, 0, 0, 0);
      return result;
    }
  
    function isWithinInterval(date, interval) {
      return date >= interval.start && date <= interval.end;
    }
  
    function addMonths(date, amount) {
      const result = new Date(date);
      result.setMonth(result.getMonth() + amount);
      return result;
    }
  
    function subMonths(date, amount) {
      return addMonths(date, -amount);
    }
  
    function addYears(date, amount) {
      const result = new Date(date);
      result.setFullYear(result.getFullYear() + amount);
      return result;
    }
  
    function subYears(date, amount) {
      return addYears(date, -amount);
    }
  
    // Check if a date is in the future or today
    function isFutureOrToday(date) {
      const today = new Date();
      const normalizedDate = new Date(date);
      normalizedDate.setHours(0, 0, 0, 0);
      
      const normalizedToday = new Date(today);
      normalizedToday.setHours(0, 0, 0, 0);
      
      return normalizedDate >= normalizedToday;
    }
  
    // Check if a date has orders or bookings
    function hasOrderOrBooking(date, ordersMap) {
      const dateKey = date.toISOString().split('T')[0];
      return ordersMap[dateKey] && ordersMap[dateKey].length > 0;
    }
  
    // Get the orders data from PHP
    if (typeof window.sampleOrders !== 'undefined') {
      sampleOrders = window.sampleOrders;
    }
    
    // Extract all dates with orders
    function extractDatesWithOrders() {
      allDatesWithOrders = sampleOrders.map(order => {
        const date = parseISO(order.date);
        return startOfDay(date).toISOString();
      });
    }
    
    // Group orders by date
    function groupOrdersByDate(orders) {
      const grouped = {};
      
      orders.forEach(order => {
        const date = parseISO(order.date);
        const dateKey = date.toISOString().split('T')[0];
        
        if (!grouped[dateKey]) {
          grouped[dateKey] = [];
        }
        
        grouped[dateKey].push(order);
      });
      
      return grouped;
    }
    
    // Event handlers
    function initEventHandlers() {
      // Handle back to list view button
      const backToListBtn = document.querySelector('.back-to-list-btn');
      if (backToListBtn) {
        backToListBtn.addEventListener('click', function() {
          // Find the parent container
          const container = this.closest('.upcoming-bookings-container, .past-bookings-container, .past-orders-container');
          if (container) {
            // Get the original content (need to implement in the main orders.js)
            if (typeof restoreOriginalView === 'function') {
              restoreOriginalView(container);
            } else {
              // Fallback: refresh the page
              window.location.reload();
            }
          }
        });
      }
    }
    
    // Filter orders based on current filters
    function filterOrders() {
      return sampleOrders.filter(order => {
        // Filter by status
        if (statusFilter !== 'all' && order.status !== statusFilter) {
          return false;
        }
        
        // Filter by type
        if (typeFilter !== 'all' && order.type !== typeFilter) {
          return false;
        }
        
        // Filter by selected date
        if (selectedDate) {
          const orderDate = parseISO(order.date);
          if (!isSameDay(orderDate, selectedDate)) {
            return false;
          }
        }
        
        // Filter by date range
        if (dateRange) {
          const orderDate = parseISO(order.date);
          if (!isWithinInterval(orderDate, dateRange)) {
            return false;
          }
        }
        
        return true;
      });
    }
    
    // Sort orders based on current sort order
    function sortOrders(orders) {
      return [...orders].sort((a, b) => {
        if (sortOrder === 'newest') {
          return parseISO(b.date) - parseISO(a.date);
        } else if (sortOrder === 'oldest') {
          return parseISO(a.date) - parseISO(b.date);
        } else if (sortOrder === 'amount') {
          const amountA = parseFloat(a.amount.replace('$', ''));
          const amountB = parseFloat(b.amount.replace('$', ''));
          return amountB - amountA;
        }
        return 0;
      });
    }
    
    // Update time display
    function updateTimeDisplay() {
      const now = new Date();
      currentTimeDisplay.textContent = formatDate(now, 'h:mm a');
    }
    
    // Initialize year select with extended range from 1999 to 2050
    function initYearSelect() {
      const currentYear = new Date().getFullYear();
      const startYear = 1999;
      const endYear = 2050;
      
      for (let year = startYear; year <= endYear; year++) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearSelect.appendChild(option);
      }
      
      // Make the current year visible in the dropdown
      yearSelect.value = currentYear;
      
      // Add scrollable functionality through CSS classes
      yearSelect.classList.add('scrollable-select');
    }
    
    // Function to handle date selection
    function handleDateSelect(date) {
      // Set the selected date and update UI
      selectedDate = date;
      selectedDateValue.textContent = formatDate(date, 'MMMM d, yyyy');
      selectedDateContainer.style.display = 'flex';
      
      // Highlight the selected date in the calendar
      const allDays = document.querySelectorAll('.calendar-day:not(.calendar-day-placeholder)');
      allDays.forEach(day => {
        day.classList.remove('calendar-day-selected');
      });
      
      // Find the day element that matches the selected date and highlight it
      const dateString = date.toISOString().split('T')[0];
      const selectedDayElement = document.querySelector(`.calendar-day[data-date="${dateString}"]`);
      if (selectedDayElement) {
        selectedDayElement.classList.add('calendar-day-selected');
      }
      
      // Filter orders or bookings for the selected date
      const filteredOrders = sampleOrders.filter(order => {
        const orderDate = new Date(order.date).toISOString().split('T')[0];
        return orderDate === dateString;
      });
      
      if (filteredOrders.length > 0) {
        renderGridView(filteredOrders);
      } else {
        noItemsMessage.style.display = 'flex';
        noItemsTitle.textContent = 'No orders or bookings';
        noItemsDescription.textContent = 'There are no items scheduled for this date';
        ordersGrid.style.display = 'none';
      }
    }
    
    // Clear date selection
    function clearDateSelect() {
      // Clear the selected date
      selectedDate = null;
      selectedDateContainer.style.display = 'none';
      
      // Remove the highlight from any previously selected day
      const allDays = document.querySelectorAll('.calendar-day');
      allDays.forEach(day => {
        day.classList.remove('calendar-day-selected');
      });
      
      // Reset the view to show all orders/bookings
      updateView();
    }
    
    // Update view based on current state
    function updateView() {
      // Update month and year selects
      monthSelect.value = currentDate.getMonth().toString();
      yearSelect.value = currentDate.getFullYear().toString();
      
      // Update month/year header display
      const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ];
      const currentMonthYearHeader = document.querySelector('.current-month-year');
      if (currentMonthYearHeader) {
        currentMonthYearHeader.textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
      }
      
      // Calendar view is the only view now
      calendarView.style.display = 'flex';
      gridView.style.display = 'none';
      renderCalendar();
    }
    
    // Render calendar view
    function renderCalendar() {
      // Clear previous calendar
      calendarGrid.innerHTML = '';
      
      // Record the current time once - used for comparing dates throughout this function
      const now = new Date();
      const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      
      // Calculate the first day of the month
      const firstDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
      
      // Calculate the last day of the month
      const lastDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
      
      // First day of week (0 = Sunday, 1 = Monday, etc)
      const firstDayOfWeek = firstDayOfMonth.getDay();
      
      // Create an array to hold all the cells for the calendar grid
      const calendarCells = [];
      
      // Create empty placeholder cells for days before the 1st of the month
      for (let i = 0; i < firstDayOfWeek; i++) {
        calendarCells.push({
          type: 'placeholder',
          date: null
        });
      }
      
      // Create cells for actual days in the month
      for (let i = 1; i <= lastDayOfMonth.getDate(); i++) {
        calendarCells.push({
          type: 'day',
          date: new Date(currentDate.getFullYear(), currentDate.getMonth(), i)
        });
      }
      
      // Calculate how many additional cells we need to complete the last row
      const additionalCells = (7 - (calendarCells.length % 7)) % 7;
      
      // Add empty placeholder cells to complete the last row if needed
      for (let i = 0; i < additionalCells; i++) {
        calendarCells.push({
          type: 'placeholder',
          date: null
        });
      }
      
      // Group orders by date for calendar view - do this once per render
      const ordersByDate = {};
      sampleOrders.forEach(order => {
        const orderDate = parseISO(order.date);
        const dateKey = orderDate.toISOString().split('T')[0];
        
        if (!ordersByDate[dateKey]) {
          ordersByDate[dateKey] = [];
        }
        
        ordersByDate[dateKey].push(order);
      });
      
      // Get the exact current date/time once for consistent comparison
      const currentTimeNow = new Date();
      const todayDate = new Date(currentTimeNow);
      todayDate.setHours(0, 0, 0, 0);
      
      // Render each cell in the calendar grid
      calendarCells.forEach(cellData => {
        const dayElement = document.createElement('div');
        
        // Handle placeholder cells (empty spaces before 1st day and after last day)
        if (cellData.type === 'placeholder') {
          dayElement.className = 'calendar-day calendar-day-placeholder';
          calendarGrid.appendChild(dayElement);
          return;
        }
        
        // From here, we know it's a regular day cell with a valid date
        const day = cellData.date;
        
        // Compare with today for accurate past/future detection
        const compareDate = new Date(day);
        compareDate.setHours(0, 0, 0, 0);
        
        // A date is in the past if it's strictly before today
        const isPastDate = compareDate < todayDate;
        
        // Get any bookings/orders for this date
        const dateKey = day.toISOString().split('T')[0];
        const ordersOnDay = ordersByDate[dateKey] || [];
        const hasOrdersOnDay = ordersOnDay.length > 0;
        
        // Check if this is today
        const isCurrentDay = isToday(day);
        
        // A day is active and selectable if:
        // 1. It has orders/bookings (regardless of date), OR
        // 2. It is today or in the future
        const isActive = hasOrdersOnDay || !isPastDate;
        
        // Build the class list for the day cell
        let dayClasses = ['calendar-day'];
        
        // Add today class if it's today
        if (isCurrentDay) {
          dayClasses.push('calendar-day-today');
        }
        
        // Handle dates with orders (always active and highlighted)
        if (hasOrdersOnDay) {
          dayClasses.push('calendar-day-active', 'has-events');
        } 
        // Handle dates without orders
        else {
          // If it's a past date without orders
          if (isPastDate) {
            dayClasses.push('calendar-day-past');
            // Only make it inactive if it's a past date without orders
            dayClasses.push('calendar-day-inactive');
          } 
          // Future dates should always be active
          else {
            dayClasses.push('calendar-day-active');
          }
        }
        
        dayElement.className = dayClasses.join(' ');
        
        // Add data attributes for debugging and potential future features
        dayElement.setAttribute('data-date', dateKey);
        dayElement.setAttribute('data-has-orders', hasOrdersOnDay);
        dayElement.setAttribute('data-past', isPastDate);
        
        // Day number and today label
        const dayNumber = document.createElement('div');
        dayNumber.className = 'day-number';
        dayNumber.textContent = day.getDate();
        dayElement.appendChild(dayNumber);
        
        // Add 'Today' label for current day
        if (isCurrentDay) {
          const todayLabel = document.createElement('div');
          todayLabel.className = 'today-label';
          todayLabel.textContent = 'Today';
          dayElement.appendChild(todayLabel);
        }
        
        // Add events/orders to the date cell if they exist
        if (hasOrdersOnDay) {
          const eventsContainer = document.createElement('div');
          eventsContainer.className = 'events-container';
          
          // Limit to the first 3 orders to prevent overcrowding
          const displayedOrders = ordersOnDay.slice(0, 3);
          
          displayedOrders.forEach(order => {
            const eventElement = document.createElement('div');
            
            // Apply proper border color based on order type
            if (order.type === 'order') {
              eventElement.className = 'calendar-event calendar-event-order';
              eventElement.style.borderLeftColor = 'var(--pink-500)';
            } else {
              eventElement.className = 'calendar-event calendar-event-booking';
              eventElement.style.borderLeftColor = 'var(--blue-500)';
            }
            
            // Configure background based on date (lighter for past dates)
            if (isPastDate && !isCurrentDay) {
              eventElement.style.backgroundColor = 'var(--pink-50)';
              eventElement.style.borderColor = 'var(--pink-100)';
            }
            
            // Event icon
            const iconElement = document.createElement('div');
            iconElement.className = 'event-icon';
            
            const icon = document.createElement('i');
            if (order.type === 'order') {
              icon.setAttribute('data-lucide', 'shopping-bag');
              icon.className = 'icon-pink';
            } else {
              // Use the calendar icon for bookings as shown in reference image
              icon.setAttribute('data-lucide', 'calendar');
              icon.className = 'icon-pink';
            }
            
            iconElement.appendChild(icon);
            eventElement.appendChild(iconElement);
            
            // Event title
            const titleElement = document.createElement('div');
            titleElement.className = 'event-title';
            titleElement.textContent = order.title;
            eventElement.appendChild(titleElement);
            
            // Event click handler
            eventElement.addEventListener('click', (e) => {
              e.stopPropagation();
              showOrderDetails(order);
            });
            
            eventsContainer.appendChild(eventElement);
          });
          
          // Add count indicator if there are more orders than shown
          if (ordersOnDay.length > 3) {
            const moreIndicator = document.createElement('div');
            moreIndicator.className = 'more-events-indicator';
            moreIndicator.textContent = `+${ordersOnDay.length - 3} more`;
            eventsContainer.appendChild(moreIndicator);
          }
          
          dayElement.appendChild(eventsContainer);
        }
        
        // Click handler logic:
        // Always make dates with orders/bookings clickable
        // For dates without orders, only make current and future dates clickable
        if (isActive) {
          dayElement.addEventListener('click', () => {
            handleDateSelect(day);
          });
          
          // Add tooltip for better UX
          if (hasOrdersOnDay) {
            const orderCount = ordersOnDay.length;
            const orderText = orderCount === 1 ? '1 item' : `${orderCount} items`;
            dayElement.setAttribute('title', `${orderText} on this date`);
          }
        }
        
        calendarGrid.appendChild(dayElement);
      });
      
      // Update month and year selects to reflect the current displayed month/year
      monthSelect.value = currentDate.getMonth().toString();
      yearSelect.value = currentDate.getFullYear().toString();
      
      // Show current month/year in a more visible way
      const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ];
      const currentMonthYearHeader = document.querySelector('.current-month-year');
      if (currentMonthYearHeader) {
        currentMonthYearHeader.textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
      }
      
      // Update the current time display
      updateCurrentTimeDisplay();
      
      // Initialize Lucide icons for the newly added elements
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    }
    
    // Function to update the current time display
    function updateCurrentTimeDisplay() {
      const currentTimeElement = document.querySelector('.current-time-text');
      if (currentTimeElement) {
        const now = new Date();
        const options = { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' };
        currentTimeElement.textContent = now.toLocaleDateString('en-US', options);
      }
    }
    
    // Render grid view
    function renderGridView() {
      const filteredOrders = filterOrders();
      const sortedOrders = sortOrders(filteredOrders);
      
      if (sortedOrders.length === 0) {
        ordersGrid.style.display = 'none';
        noItemsMessage.style.display = 'flex';
        
        if (searchQuery) {
          noItemsDescription.textContent = 'Try a different search term';
        } else if (selectedDate) {
          noItemsDescription.textContent = 'No orders or bookings on the selected date';
        } else {
          noItemsDescription.textContent = 'You don\'t have any items matching the selected filters';
        }
      } else {
        ordersGrid.style.display = 'grid';
        noItemsMessage.style.display = 'none';
        
        ordersGrid.innerHTML = '';
        
        sortedOrders.forEach((order, index) => {
          const orderCard = document.createElement('div');
          orderCard.className = `order-card ${order.type === 'order' ? 'order-card-order' : 'order-card-booking'} fade-in`;
          orderCard.style.animationDelay = `${index * 0.05}s`;
          
          // Order header
          const orderHeader = document.createElement('div');
          orderHeader.className = `order-header ${order.type === 'order' ? '' : 'order-header-booking'}`;
          
          const titleRow = document.createElement('div');
          titleRow.className = 'order-title-row';
          
          const titleContainer = document.createElement('div');
          titleContainer.className = 'order-title-container';
          
          const typeIcon = document.createElement('i');
          if (order.type === 'order') {
            typeIcon.setAttribute('data-lucide', 'shopping-bag');
            typeIcon.className = 'icon-pink';
          } else {
            if (order.status === 'completed') {
              typeIcon.setAttribute('data-lucide', 'calendar-check');
            } else {
              typeIcon.setAttribute('data-lucide', 'calendar-clock');
            }
            typeIcon.className = 'icon-pink';
          }
          
          const title = document.createElement('div');
          title.className = 'order-title';
          title.textContent = order.title;
          
          titleContainer.appendChild(typeIcon);
          titleContainer.appendChild(title);
          
          const statusBadge = document.createElement('div');
          statusBadge.className = `status-badge ${order.status === 'completed' ? 'status-completed' : 'status-upcoming'}`;
          statusBadge.textContent = order.status;
          
          titleRow.appendChild(titleContainer);
          titleRow.appendChild(statusBadge);
          
          const orderDate = document.createElement('div');
          orderDate.className = 'order-date';
          const orderDateTime = parseISO(order.date);
          orderDate.textContent = `${formatDate(orderDateTime, 'PPP')} at ${formatDate(orderDateTime, 'p')}`;
          
          orderHeader.appendChild(titleRow);
          orderHeader.appendChild(orderDate);
          
          // Order content
          const orderContent = document.createElement('div');
          orderContent.className = 'order-content';
          
          const orderItems = document.createElement('div');
          orderItems.className = 'order-items';
          
          const orderImage = document.createElement('div');
          orderImage.className = 'order-image';
          
          const img = document.createElement('img');
          img.src = order.image || '/placeholder.svg';
          img.alt = order.title;
          
          orderImage.appendChild(img);
          
          const itemsList = document.createElement('ul');
          itemsList.className = 'items-list';
          
          order.items.forEach(item => {
            const listItem = document.createElement('li');
            
            const bullet = document.createElement('span');
            bullet.className = 'item-bullet';
            
            const itemText = document.createTextNode(item);
            
            listItem.appendChild(bullet);
            listItem.appendChild(itemText);
            itemsList.appendChild(listItem);
          });
          
          orderItems.appendChild(orderImage);
          orderItems.appendChild(itemsList);
          orderContent.appendChild(orderItems);
          
          // Order footer
          const orderFooter = document.createElement('div');
          orderFooter.className = 'order-footer';
          
          const orderAmount = document.createElement('span');
          orderAmount.className = 'order-amount';
          orderAmount.textContent = order.amount;
          
          const viewDetailsBtn = document.createElement('button');
          viewDetailsBtn.className = 'btn btn-outline btn-sm';
          viewDetailsBtn.textContent = 'View Details';
          
          orderFooter.appendChild(orderAmount);
          orderFooter.appendChild(viewDetailsBtn);
          
          // Assemble order card
          orderCard.appendChild(orderHeader);
          orderCard.appendChild(orderContent);
          orderCard.appendChild(orderFooter);
          
          // Add click handler
          orderCard.addEventListener('click', () => {
            showOrderDetails(order);
          });
          
          ordersGrid.appendChild(orderCard);
        });
        
        // Initialize Lucide icons for the newly added elements
        lucide.createIcons();
      }
    }
    
    // Show order details
    function showOrderDetails(order) {
      selectedOrder = order;
      
      // Set modal content
      modalTitle.textContent = order.title;
      
      const orderDate = parseISO(order.date);
      modalDatetime.textContent = `${formatDate(orderDate, 'PPP')} at ${formatDate(orderDate, 'p')}`;
      
      modalStatus.className = `status-badge ${order.status === 'completed' ? 'status-completed' : 'status-upcoming'}`;
      modalStatus.textContent = order.status;
      
      modalType.textContent = order.type;
      
      modalTypeIcon.innerHTML = '';
      const typeIcon = document.createElement('i');
      if (order.type === 'order') {
        typeIcon.setAttribute('data-lucide', 'shopping-bag');
      } else {
        if (order.status === 'completed') {
          typeIcon.setAttribute('data-lucide', 'calendar-check');
        } else {
          typeIcon.setAttribute('data-lucide', 'calendar-clock');
        }
      }
      typeIcon.className = 'icon-pink';
      modalTypeIcon.appendChild(typeIcon);
      
      modalItems.innerHTML = '';
      order.items.forEach(item => {
        const listItem = document.createElement('li');
        
        const bullet = document.createElement('div');
        bullet.className = 'item-bullet';
        
        const itemText = document.createTextNode(item);
        
        listItem.appendChild(bullet);
        listItem.appendChild(itemText);
        modalItems.appendChild(listItem);
      });
      
      modalAmount.textContent = order.amount;
      modalImage.src = order.image || '/placeholder.svg';
      
      // Set action button text
      if (order.type === 'order') {
        if (order.status === 'completed') {
          modalActionBtn.textContent = 'Reorder';
        } else {
          modalActionBtn.textContent = 'Track Order';
        }
      } else {
        if (order.status === 'completed') {
          modalActionBtn.textContent = 'Book Again';
        } else {
          modalActionBtn.textContent = 'Manage Booking';
        }
      }
      
      // Show modal
      orderModal.style.display = 'flex';
      
      // Initialize Lucide icons for the newly added elements
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    }
    
    // Close order details
    function closeOrderDetails() {
      orderModal.style.display = 'none';
    }
    
    // Clear all filters
    function clearAllFilters() {
      // Search query removed
      
      statusFilter = 'all';
      statusFilterSelect.value = 'all';
      
      typeFilter = 'all';
      typeFilterSelect.value = 'all';
      
      dateRange = null;
      dateRangeText.textContent = 'Filter by date range';
      dateRangeFilterIndicator.style.display = 'none';
      
      selectedDate = null;
      selectedDateContainer.style.display = 'none';
      
      updateView();
    }
  
    // Initialize the application
    function init() {
      // Extract dates with orders
      extractDatesWithOrders();
      
      // Initialize year select
      initYearSelect();
      
      // Set initial month and year
      monthSelect.value = currentDate.getMonth().toString();
      yearSelect.value = currentDate.getFullYear().toString();
      
      // Update time display
      updateTimeDisplay();
      setInterval(updateTimeDisplay, 60000); // Update every minute
      
      // Ensure loading container is hidden and view is updated
      setTimeout(() => {
        if (loadingContainer) {
          loadingContainer.style.display = 'none';
        }
        updateView();
        // Re-initialize icons after view is updated
        if (typeof lucide !== 'undefined') {
          lucide.createIcons();
        }
      }, 1000);
      
      // Event listeners - calendar button and search removed
      
      // Clear selected date button event listener
      clearSelectedDateBtn.addEventListener('click', clearDateSelect);
      
      statusFilterSelect.addEventListener('change', () => {
        statusFilter = statusFilterSelect.value;
        updateView();
      });
      
      typeFilterSelect.addEventListener('change', () => {
        typeFilter = typeFilterSelect.value;
        updateView();
      });
      
      monthSelect.addEventListener('change', () => {
        // Create a new date object to avoid potential date issues when changing months
        currentDate = new Date(currentDate.getFullYear(), parseInt(monthSelect.value), 1);
        updateView();
      });
      
      yearSelect.addEventListener('change', () => {
        // Create a new date object to avoid potential date issues when changing years
        currentDate = new Date(parseInt(yearSelect.value), currentDate.getMonth(), 1);
        updateView();
      });
      
      prevMonthBtn.addEventListener('click', () => {
        currentDate = subMonths(currentDate, 1);
        updateView();
      });
      
      nextMonthBtn.addEventListener('click', () => {
        currentDate = addMonths(currentDate, 1);
        updateView();
      });
      
      prevYearBtn.addEventListener('click', () => {
        currentDate = subYears(currentDate, 1);
        updateView();
      });
      
      nextYearBtn.addEventListener('click', () => {
        currentDate = addYears(currentDate, 1);
        updateView();
      });
      
      currentTimeBtn.addEventListener('click', () => {
        currentDate = new Date();
        updateView();
      });
      
      clearSelectedDateBtn.addEventListener('click', clearDateSelection);
      
      clearAllFiltersBtn.addEventListener('click', clearAllFilters);
      
      // Date range picker
      dateRangeBtn.addEventListener('click', () => {
        dateRangePopup.style.display = dateRangePopup.style.display === 'block' ? 'none' : 'block';
      });
      
      clearDateRangeBtn.addEventListener('click', () => {
        dateRange = null;
        dateRangeText.textContent = 'Filter by date range';
        dateRangeFilterIndicator.style.display = 'none';
        dateRangePopup.style.display = 'none';
        updateView();
      });
      
      clearDateFilterBtn.addEventListener('click', () => {
        dateRange = null;
        dateRangeText.textContent = 'Filter by date range';
        dateRangeFilterIndicator.style.display = 'none';
        updateView();
      });
      
      // Sort functionality is now handled automatically
      
      // Modal close buttons
      modalCloseBtn.addEventListener('click', closeOrderDetails);
      closeModalBtn.addEventListener('click', closeOrderDetails);
      
      // Close dropdowns when clicking outside
      document.addEventListener('click', (e) => {
        if (!dateRangeBtn.contains(e.target) && !dateRangePopup.contains(e.target)) {
          dateRangePopup.style.display = 'none';
        }
      });
    }
  
    // Start the application
    init();
}

// Also initialize when the document is loaded directly (not via dynamic loading)
document.addEventListener('DOMContentLoaded', function() {
    initCalendar();
});