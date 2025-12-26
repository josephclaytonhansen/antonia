$(function () {
    const $pages = $('#flipbook').length ? $('#flipbook') : $(document);
    const $book = $pages.find('.book');
    const $heroframes = $pages.find('.hero-frame-left, .hero-frame-right');
    const scrolldownEl = document.getElementById('scrolldown');
    const spacer = document.querySelector('.page-scroll-spacer');
    const hero = document.querySelector('.hero');
    const newContent = document.querySelector('.new-content-section');

    let ticking = false;

    const parseFraction = (v) => {
        if (v == null) return null;
        const s = String(v).trim();
        if (!s.length) return null;
        const isPercent = s.endsWith('%');
        const num = parseFloat(isPercent ? s.slice(0, -1) : s);
        if (isNaN(num)) return null;
        const result = isPercent || num > 1 ? num / 100 : num;
        return Math.max(0, Math.min(1, result));
    };

    const parseBoost = (v) => {
        const n = parseFloat(String(v).trim());
        return isNaN(n) ? null : n;
    };

    const getData = (keys, defaultValue = null) => {
        let ancestor = null;
        try {
            ancestor = $book[0]?.closest('[data-open-start],[data-start],[data-open-end],[data-end],[data-open-boost],[data-open-smoothing],[data-open-easing]');
        } catch (e) { }
        const elements = [spacer, ancestor, $book[0]];
        for (const key of Array.isArray(keys) ? keys : [keys]) {
            const camelKey = key.replace(/-(\w)/g, (_, c) => c.toUpperCase());
            for (const el of elements) {
                const value = el?.dataset?.[camelKey];
                if (value != null) return value;
            }
        }
        return defaultValue;
    };

    const maybeSingleBoost = parseBoost(getData('openBoost'));
    const leftParsed = parseBoost(getData(['openBoostLeft', 'openboostleft']));
    const rightParsed = parseBoost(getData(['openBoostRight', 'openboostright']));
    const configuredBoostLeft = leftParsed ?? maybeSingleBoost ?? 20;
    const configuredBoostRight = rightParsed ?? maybeSingleBoost ?? 20;

    const easeOutCubic = (x) => 1 - Math.pow(1 - x, 3);
    const easeInOutCubic = (x) => x < 0.5 ? 4 * x * x * x : 1 - Math.pow(-2 * x + 2, 3) / 2;

    const parsedOverrun = parseFloat(getData(['openOverrun', 'openoverrun']));
    const overrun = (!isNaN(parsedOverrun) && parsedOverrun >= 0) ? Math.min(0.2, parsedOverrun) : 0.03;

    const targets = {
        '.front': -160, '.page1': -150, '.page2': -30,
        '.page3': -140, '.page4': -40, '.page5': -130,
        '.page6': -50, '.back': -20
    };
    const rightSideSelectors = ['.front', '.page1', '.page3', '.page5'];

    function updateFromProgress(e) {
        e = Math.max(0, Math.min(1, e));
        try { $book[0]?.style.setProperty('--book-turn', e); } catch (err) { }

        const spineOpacity = Math.max(0, Math.min(1, (e - 0.05) / 0.95));
        $pages.find('.spine').css('opacity', spineOpacity);
        const backShift = Math.min(10, e * 18);
        const scale = 1 + 0.1 * e;
        const heroFrameScale = 1 - 0.1 * e;
        const shadowAlpha = 0.2 * e;

        if ($heroframes.length) {
            $heroframes.css('transform', `scale(${heroFrameScale})`);
        }

        Object.entries(targets).forEach(([sel, angle]) => {
            const $el = $book.find(sel);
            if ($el.length) {
                const boostToUse = rightSideSelectors.includes(sel) ? configuredBoostRight : configuredBoostLeft;
                const ang = (angle + boostToUse) * e;
                let transform = `rotateY(${ang}deg) scale(${scale})`;
                if (sel === '.back') {
                    transform += ` translateX(${-backShift}px)`;
                } else {
                    $el.css('box-shadow', `0 1em 3em 0 rgba(0,0,0,${shadowAlpha})`);
                }
                $el.css('transform', transform);
            }
        });
    }

    function onScroll() {
        if (ticking) return;
        ticking = true;

        requestAnimationFrame(() => {
            try {
                const scrollTop = window.scrollY || document.documentElement.scrollTop;
                const viewH = window.innerHeight || document.documentElement.clientHeight;

                // Stage 1: Book opening (0 to viewH*4)
                const stage1End = viewH * 4;
                // Stage 2: Scroll away (viewH*6 to viewH*8) - starts 2vh after book opens
                const stage2Start = viewH * 6;
                const stage2End = viewH * 8;

                if (scrollTop < stage1End) {
                    // STAGE 1: Book opening animation
                    // Map scroll to 0-1 range, but complete the animation earlier (at 70% of stage1End)
                    const bookProgress = Math.min(1, scrollTop / (stage1End * 0.7));
                    const effectiveRaw = Math.min(1, bookProgress + overrun * (1 - bookProgress));
                    const eased = easeOutCubic(effectiveRaw);
                    updateFromProgress(eased);

                    // Hero stays in place
                    if (hero) {
                        hero.style.transform = 'translateY(0)';
                        hero.style.opacity = '1';
                    }

                    const headerh1 = document.querySelector('main header h1') || document.querySelector('header h1') || document.querySelector('main h1');
                    if (headerh1) {
                        headerh1.style.fontSize = `min(10vw, 5rem)`;
                    }

                    // New content off-screen below
                    if (newContent) {
                        newContent.style.transform = 'translateY(100vh)';
                        newContent.style.opacity = '0';
                    }

                    if (scrolldownEl) {
                        scrolldownEl.style.opacity = String(0.5 * (1 - eased));
                    }

                } else if (scrollTop >= stage2Start && scrollTop < stage2End) {
                    // STAGE 2: Scroll away + new content reveal
                    const stage2Progress = (scrollTop - stage2Start) / (stage2End - stage2Start);
                    const eased = easeInOutCubic(stage2Progress);



                    // Keep book fully open
                    updateFromProgress(1);

                    const headerh1 = document.querySelector('main header h1') || document.querySelector('header h1') || document.querySelector('main h1');
                    if (headerh1) {
                        headerh1.style.fontSize = `min(6vw, 3rem)`;
                    }

                    // Move hero up and fade out
                    if (hero) {
                        const heroY = -100 * eased;
                        const heroOpacity = 1 - eased;
                        hero.style.transform = `translateY(${heroY}vh)`;
                        hero.style.opacity = String(heroOpacity);
                    }

                    // Move new content up from below
                    if (newContent) {
                        const contentY = 100 - (100 * eased);
                        const contentOpacity = eased;
                        newContent.style.transform = `translateY(${contentY}vh)`;
                        newContent.style.opacity = String(contentOpacity);
                    }

                    if (scrolldownEl) {
                        scrolldownEl.style.opacity = '0';
                    }

                } else if (scrollTop >= stage2End) {
                    // STAGE 3: New content fully visible
                    updateFromProgress(1);

                    if (hero) {
                        hero.style.transform = 'translateY(-100vh)';
                        hero.style.opacity = '0';
                    }

                    if (newContent) {
                        newContent.style.transform = 'translateY(0)';
                        newContent.style.opacity = '1';
                    }

                    if (scrolldownEl) {
                        scrolldownEl.style.opacity = '0';
                    }
                }

                ticking = false;
            } catch (err) {
                console.error('Scroll error:', err);
                ticking = false;
            }
        });
    }

    // Initial state
    updateFromProgress(0);
    if (scrolldownEl) scrolldownEl.style.opacity = String(0.5);
    if (hero) {
        hero.style.transform = 'translateY(0)';
        hero.style.opacity = '1';
    }
    if (newContent) {
        newContent.style.transform = 'translateY(100vh)';
        newContent.style.opacity = '0';
    }

    function disableCssHoverOpening() {
        if (window.matchMedia?.('(hover: none)').matches) return;
        try {
            $book.css('pointer-events', 'none');
            $book.find('.front, .page1, .page2, .page3, .page4, .page5, .page6, .back').css('pointer-events', 'auto');
        } catch (e) { }
    }
    disableCssHoverOpening();

    function ensureSpacer() {
        if (spacer) spacer.style.height = '900vh'; // Enough for all stages
    }
    ensureSpacer();

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    (function revealNewContent() {
        const el = document.getElementById('newContent') || document.querySelector('.new-content-section');
        if (el) {
            el.removeAttribute('hidden');
            el.style.setProperty('display', 'flex', 'important');
        }
    })();

    // Initial render
    onScroll();
});