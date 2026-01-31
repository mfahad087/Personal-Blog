// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    // Ensure normal scrolling behavior
    document.body.style.overflowY = 'auto';
    document.documentElement.style.overflowY = 'auto';
    
    // Remove any potential scroll-blocking event listeners
    document.addEventListener('wheel', function(e) {
        // Allow normal wheel scrolling
        return true;
    }, { passive: true });
    
    document.addEventListener('touchmove', function(e) {
        // Allow normal touch scrolling
        return true;
    }, { passive: true });
    
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if(mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            console.log('Mobile menu toggled, active:', mobileMenu.classList.contains('active'));
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if(mobileMenu && !event.target.closest('.mobile-menu') && !event.target.closest('.mobile-menu-btn')) {
            mobileMenu.classList.remove('active');
        }
    });
    
    // Close mobile menu when clicking on a link
    document.addEventListener('click', function(event) {
        if(event.target.closest('.mobile-menu a')) {
            if(mobileMenu) {
                mobileMenu.classList.remove('active');
            }
        }
    });
    
    // Portfolio file download
    const portfolioBtn = document.querySelector('.portfolio-download');
    if(portfolioBtn) {
        portfolioBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'assets/portfolio.zip';
        });
    }
    
    // Contact form validation
    const contactForm = document.getElementById('contactForm');
    if(contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if(!name || !email || !message) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            if(!isValidEmail(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
        });
    }
});

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}