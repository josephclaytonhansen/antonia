$(function () {
    const $pages = $('#flipbook').length ? $('#flipbook') : $(document);
    const $book = $pages.find('.book');
    const $heroframes = $pages.find('.hero-frame-left, .hero-frame-right');
    const scrolldownEl = document.getElementById('scrolldown');
    const spacer = document.querySelector('.page-scroll-spacer');
    let ticking = false;
    let prevRawProgress = 0;
    let isReleased = false;

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

    function computeProgress() {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const viewH = window.innerHeight || document.documentElement.clientHeight;

        if (spacer instanceof Element) {
            const spacerTop = spacer.offsetTop;
            const spacerHeight = spacer.offsetHeight || 1;

            const start = spacerTop - viewH;
            const end = spacerTop + spacerHeight;
            const denom = (end - start) || 1;
            const v = (scrollTop - start) / denom;
            return Math.max(0, Math.min(1, v));
        }

        const max = Math.max(1, document.documentElement.scrollHeight - viewH);
        return Math.max(0, Math.min(1, scrollTop / max));
    }

    function onScroll() {
        if (ticking) return;
        ticking = true;

        requestAnimationFrame(() => {
            try {
                const mainEl = document.querySelector('main');
                const rawNow = computeProgress();

                // TWO STATE SYSTEM
                if (!isReleased && rawNow >= 1.0) {
                    isReleased = true;
                    mainEl.classList.add('main--released');
                    updateFromProgress(1);
                } else if (isReleased && rawNow <= 0.98) {
                    isReleased = false;
                    mainEl.classList.remove('main--released');
                    prevRawProgress = rawNow;
                }

                // Update book when not released
                if (!isReleased) {
                    const effectiveRaw = Math.min(1, rawNow + overrun * (1 - rawNow));
                    const eased = easeOutCubic(effectiveRaw);
                    updateFromProgress(eased);
                    
                    if (scrolldownEl) {
                        scrolldownEl.style.opacity = String(0.5 * (1 - eased));
                    }
                    prevRawProgress = rawNow;
                } else {
                    updateFromProgress(1);
                    if (scrolldownEl) {
                        scrolldownEl.style.opacity = '0';
                    }
                }

                ticking = false;
            } catch (err) {
                ticking = false;
            }
        });
    }

    updateFromProgress(0);
    if (scrolldownEl) scrolldownEl.style.opacity = String(0.5);

    function disableCssHoverOpening() {
        if (window.matchMedia?.('(hover: none)').matches) return;

        try {
            $book.css('pointer-events', 'none');
            $book.find('.front, .page1, .page2, .page3, .page4, .page5, .page6, .back').css('pointer-events', 'auto');
        } catch (e) { }
    }
    disableCssHoverOpening();

    function ensureSpacer() {
        if (spacer) spacer.style.height = '300vh';
    }
    ensureSpacer();

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
});