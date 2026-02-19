document.addEventListener('DOMContentLoaded', () => {
    const burgerBtn = document.getElementById('burger-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const body = document.body;
    const navLinks = document.querySelectorAll('.mobile-nav-link');

    if (burgerBtn && mobileMenu) {
        const toggleMenu = () => {
            const isActive = mobileMenu.classList.toggle('active');
            body.classList.toggle('mobile-menu-open', isActive);
        }

        burgerBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMenu();
        });

        // Close menu on link click
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                body.classList.remove('mobile-menu-open');
            });
        });

        // Close menu on click outside
        window.addEventListener('click', (e) => {
            if (mobileMenu.classList.contains('active') && !mobileMenu.contains(e.target) && e.target !== burgerBtn) {
                toggleMenu();
            }
        });
    }
});
