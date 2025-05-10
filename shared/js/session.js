/**
 * Session Management for Ayskrim Customer Pages
 * 
 * This script handles:
 * 1. Periodic session checks to ensure the user is still logged in
 * 2. Session timeout warning
 * 3. Auto-logout when session expires
 */

document.addEventListener("DOMContentLoaded", () => {
  // Get session timeout from meta tag
  const sessionTimeoutMeta = document.querySelector('meta[name="session-timeout"]');
  const sessionTimeout = sessionTimeoutMeta ? parseInt(sessionTimeoutMeta.content, 10) : 1800; // Default 30 minutes
  const warningTime = 60; // Show warning 60 seconds before expiration
  
  let warningDisplayed = false;
  let warningTimer = null;
  
  /**
   * Check if the user's session is still valid
   * @returns {Promise} Promise that resolves with the session status
   */
  function checkSession() {
    return fetch('/ayskrimWebsite/api/auth/checkSession.php', {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (!data.authenticated) {
        // Session expired or user not logged in, redirect to login page
        window.location.href = '/ayskrimWebsite/landingPage/home/home.php';
      }
      return data;
    })
    .catch(error => {
      console.error('Session check failed:', error);
      return { authenticated: false, error };
    });
  }
  
  /**
   * Show session timeout warning modal
   */
  function showTimeoutWarning() {
    if (warningDisplayed) return;
    
    warningDisplayed = true;
    
    // Create and show modal
    const modal = document.createElement('div');
    modal.id = 'session-timeout-modal';
    modal.innerHTML = `
      <div class="session-timeout-content">
        <h3>Session Timeout Warning</h3>
        <p>Your session will expire in less than a minute due to inactivity.</p>
        <button id="extend-session-btn">Keep Me Signed In</button>
      </div>
    `;
    
    // Style the modal
    const style = document.createElement('style');
    style.textContent = `
      #session-timeout-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
      }
      .session-timeout-content {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        max-width: 400px;
        text-align: center;
      }
      #extend-session-btn {
        background-color: #ff5686;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        margin-top: 15px;
      }
      #extend-session-btn:hover {
        background-color: #ff336d;
      }
    `;
    
    document.head.appendChild(style);
    document.body.appendChild(modal);
    
    // Add event listener to extend session button
    document.getElementById('extend-session-btn').addEventListener('click', () => {
      extendSession();
      closeWarning();
    });
    
    // Set timer to redirect after warning time passes
    warningTimer = setTimeout(() => {
      window.location.href = '/ayskrimWebsite/landingPage/home/home.php';
    }, warningTime * 1000);
  }
  
  /**
   * Close the timeout warning modal
   */
  function closeWarning() {
    const modal = document.getElementById('session-timeout-modal');
    if (modal) {
      modal.remove();
    }
    
    if (warningTimer) {
      clearTimeout(warningTimer);
      warningTimer = null;
    }
    
    warningDisplayed = false;
  }
  
  /**
   * Extend the user's session
   */
  function extendSession() {
    checkSession().then(data => {
      if (data.authenticated) {
        console.log('Session extended successfully');
      }
    });
  }
  
  // Set up user activity listeners to reset the timeout
  const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
  
  activityEvents.forEach(event => {
    document.addEventListener(event, () => {
      if (warningDisplayed) {
        extendSession();
        closeWarning();
      }
    }, false);
  });
  
  // Check session on page load
  checkSession();
  
  // Set interval to periodically check session (every 5 minutes)
  const sessionCheckInterval = Math.min(300000, (sessionTimeout - warningTime - 30) * 1000); // 5 minutes or less
  setInterval(() => {
    checkSession().then(data => {
      if (data.authenticated) {
        // Calculate time remaining in session
        const lastActivity = new Date(data.user.last_activity * 1000);
        const now = new Date();
        const timeElapsed = Math.floor((now - lastActivity) / 1000);
        const timeRemaining = sessionTimeout - timeElapsed;
        
        // Show warning if session is about to expire
        if (timeRemaining <= warningTime && !warningDisplayed) {
          showTimeoutWarning();
        }
      }
    });
  }, sessionCheckInterval);
}); 