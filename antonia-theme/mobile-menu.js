/**
 * Mobile drawer – open/close behaviour.
 *
 * The drawer HTML is server-rendered in header.php; this script only manages
 * toggling, focus-trapping, ARIA attributes, and keyboard handling.
 */
!function () {
    const drawer = document.getElementById('mobileDrawer');
    const backdrop = document.getElementById('drawerBackdrop');
    const openBtn = document.getElementById('mobileMenuButton');

    if (!drawer || !backdrop || !openBtn) return;

    const closeBtn = drawer.querySelector('.drawer-close');
    const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), '
        + 'select:not([disabled]), textarea:not([disabled]), '
        + '[tabindex]:not([tabindex="-1"])';

    let lastFocus = null;

    function getFocusable() {
        return Array.from(drawer.querySelectorAll(FOCUSABLE))
            .filter(el => el.offsetParent !== null);
    }

    function trapFocus(e) {
        if (e.key !== 'Tab') return;
        const focusable = getFocusable();
        if (!focusable.length) { e.preventDefault(); return; }
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.shiftKey) {
            if (document.activeElement === first) { e.preventDefault(); last.focus(); }
        } else {
            if (document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    }

    function open() {
        lastFocus = document.activeElement;
        drawer.classList.add('open');
        backdrop.classList.add('visible');
        drawer.setAttribute('aria-hidden', 'false');
        drawer.setAttribute('role', 'dialog');
        drawer.setAttribute('aria-modal', 'true');
        openBtn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', trapFocus);
        const focusable = getFocusable();
        if (focusable.length) focusable[0].focus();
    }

    function close() {
        drawer.classList.remove('open');
        backdrop.classList.remove('visible');
        drawer.setAttribute('aria-hidden', 'true');
        drawer.removeAttribute('role');
        drawer.removeAttribute('aria-modal');
        openBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        document.removeEventListener('keydown', trapFocus);
        try { if (lastFocus && lastFocus.focus) lastFocus.focus(); } catch (err) { }
    }

    // Hamburger button
    openBtn.addEventListener('click', function (e) {
        e.preventDefault();
        drawer.classList.contains('open') ? close() : open();
    });

    // Close button inside drawer
    if (closeBtn) closeBtn.addEventListener('click', close);

    // Backdrop click
    backdrop.addEventListener('click', close);

    // ESC key
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && drawer.classList.contains('open')) close();
    });

    // If the viewport returns to desktop width, force-close and unlock scroll.
    window.addEventListener('resize', function () {
        if (window.innerWidth > 1100 && drawer.classList.contains('open')) {
            close();
        }
    });

    // Public API (used by any inline onclick if needed)
    window.toggleMobileMenu = function () {
        drawer.classList.contains('open') ? close() : open();
    };
}();
