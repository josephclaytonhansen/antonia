(function () {
    const { computePosition, offset, flip, shift } = window.FloatingUIDOM || window.FloatingUI || {};

    // Create drawer + backdrop if not present
    const existing = document.getElementById('mobileDrawer');
    if (existing) return;

    const drawer = document.createElement('aside');
    drawer.id = 'mobileDrawer';
    drawer.className = 'mobile-drawer drawer--right';
    drawer.setAttribute('aria-hidden', 'true');

    drawer.innerHTML = `
    <div class="drawer-header">
      <div class="drawer-title">Menu</div>
      <button class="drawer-close" aria-label="Close menu">×</button>
    </div>
    <nav class="drawer-content" role="navigation">
      <a href="#">Books</a>
      <a href="#">Blog</a>
      <a href="#">Art</a>
      <a href="#">Author</a>
      <a href="#">Contact</a>
      <a href="#">Press</a>
    </nav>
  `;

    const backdrop = document.createElement('div');
    backdrop.id = 'drawerBackdrop';
    backdrop.className = 'drawer-backdrop';

    document.body.appendChild(backdrop);
    document.body.appendChild(drawer);

    const button = document.getElementById('mobileMenuButton');
    const reference = button || document.querySelector('.mobile-menu-icon img') || document.querySelector('.mobile-menu-icon');
    let previousActiveElement = null;
    const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    function setPlacement() {
        // If Floating UI isn't available just default to right
        if (!computePosition || !flip) {
            drawer.classList.remove('drawer--left');
            drawer.classList.add('drawer--right');
            drawer.style.left = '';
            drawer.style.right = '0';
            return;
        }

        // Compute optimal placement relative to the reference
        computePosition(reference || document.body, drawer, {
            placement: 'right-start',
            middleware: [offset(8), flip(), shift()],
        }).then(({ placement }) => {
            if (placement && placement.startsWith('right')) {
                drawer.classList.remove('drawer--left');
                drawer.classList.add('drawer--right');
                drawer.style.right = '0';
                drawer.style.left = '';
            } else {
                drawer.classList.remove('drawer--right');
                drawer.classList.add('drawer--left');
                drawer.style.left = '0';
                drawer.style.right = '';
            }
        }).catch(() => {
            drawer.classList.remove('drawer--left');
            drawer.classList.add('drawer--right');
            drawer.style.right = '0';
            drawer.style.left = '';
        });
    }

    // Toggle functions
    function trapKey(e) {
        if (e.key !== 'Tab') return;
        const focusables = Array.from(drawer.querySelectorAll(focusableSelector)).filter(el => el.offsetParent !== null);
        if (focusables.length === 0) {
            e.preventDefault();
            return;
        }
        const first = focusables[0];
        const last = focusables[focusables.length - 1];
        if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        } else if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        }
    }

    function openDrawer() {
        setPlacement();
        previousActiveElement = document.activeElement;
        drawer.classList.add('open');
        backdrop.classList.add('visible');
        drawer.setAttribute('aria-hidden', 'false');
        drawer.setAttribute('role', 'dialog');
        drawer.setAttribute('aria-modal', 'true');
        if (button) button.setAttribute('aria-expanded', 'true');
        // focus first focusable element
        const focusables = drawer.querySelectorAll(focusableSelector);
        if (focusables.length) focusables[0].focus();
        document.addEventListener('keydown', trapKey);
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        drawer.classList.remove('open');
        backdrop.classList.remove('visible');
        drawer.setAttribute('aria-hidden', 'true');
        drawer.removeAttribute('role');
        drawer.removeAttribute('aria-modal');
        if (button) button.setAttribute('aria-expanded', 'false');
        document.removeEventListener('keydown', trapKey);
        document.body.style.overflow = '';
        // restore focus
        try { if (previousActiveElement && previousActiveElement.focus) previousActiveElement.focus(); } catch (e) { }
    }

    // Expose a global toggle to keep existing onclick handlers working
    window.toggleMobileMenu = function () {
        if (drawer.classList.contains('open')) closeDrawer();
        else openDrawer();
    };

    // Hook up the new button if present
    if (button) {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            window.toggleMobileMenu();
        });
    }

    // Close via close button or backdrop
    drawer.querySelector('.drawer-close').addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);

    // Close on Escape
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer.classList.contains('open')) closeDrawer();
    });

    // Recompute placement on resize/orientation change
    window.addEventListener('resize', () => {
        if (drawer.classList.contains('open')) setPlacement();
    });

    // Initial placement attempt
    setPlacement();
})();
