document.addEventListener('DOMContentLoaded', function() {
    const logoContainer = document.getElementById('logoContainer');
    
    function handleScroll() {
        if (window.scrollY > 10) {
            logoContainer.classList.add('scrolled');
        } else {
            logoContainer.classList.remove('scrolled');
        }
    }
    
    // Initial check on page load
    handleScroll();
    
    // Add scroll event listener
    window.addEventListener('scroll', handleScroll);
});