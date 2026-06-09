/**
 * GRACELAND CAMPUS PORTAL LANDING SYSTEM CONTROLLER
 */
document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.getElementById('navMenu');
    const dropdownBtn = document.getElementById('dropdownBtn');
    const portalDropdownMenu = document.getElementById('portalDropdownMenu');

    // 1. Mobile Top Header Nav Drawer Trigger
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            navMenu.classList.toggle('active');
            
            const icon = menuToggle.querySelector('i');
            if (navMenu.classList.contains('active')) {
                icon.className = 'fas fa-times';
            } else {
                icon.className = 'fas fa-bars';
            }
        });
    }

    // 2. Interactive Role Selection Dropdown Interceptor
    if (dropdownBtn && portalDropdownMenu) {
        dropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            portalDropdownMenu.classList.toggle('show');
        });
    }

    // 3. Document Click Escape Handler Matrix
    document.addEventListener('click', () => {
        if (portalDropdownMenu && portalDropdownMenu.classList.contains('show')) {
            portalDropdownMenu.classList.remove('show');
        }
        if (navMenu && navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
            if (menuToggle) {
                menuToggle.querySelector('i').className = 'fas fa-bars';
            }
        }
    });
});