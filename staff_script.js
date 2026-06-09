/**
 * GRACELAND FACULTY DESKTOP WORKSPACE INTERACTION INTERCEPTOR
 */
document.addEventListener('DOMContentLoaded', () => {
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebarPanel = document.getElementById('sidebarPanel');

    // 1. Mobile Sidebar Toggle Drawer Overlay Execution
    if (sidebarToggleBtn && sidebarPanel) {
        sidebarToggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebarPanel.classList.toggle('open');
            
            const icon = sidebarToggleBtn.querySelector('i');
            if (sidebarPanel.classList.contains('open')) {
                icon.className = 'fas fa-xmark';
            } else {
                icon.className = 'fas fa-bars';
            }
        });
    }

    // 2. Click Outside Drawer Auto-Collapse Strategy
    document.addEventListener('click', (e) => {
        if (sidebarPanel && sidebarPanel.classList.contains('open')) {
            if (!sidebarPanel.contains(e.target) && e.target !== sidebarToggleBtn) {
                sidebarPanel.classList.remove('open');
                if (sidebarToggleBtn) {
                    sidebarToggleBtn.querySelector('i').className = 'fas fa-bars';
                }
            }
        }
    });
});