$(function() {
    let $pages = $('#flipbook');
    if (!$pages || !$pages.length) {
        $pages = $(document);
    }

    const $book = $pages.find('.book');
    const $cover = $pages.find('.cover');
    const $back = $pages.find('.back');

    let ticking = false;
    const spacer = document.querySelector('.page-scroll-spacer');

    function parseFraction(v) {
        if (v == null) return null;
        const s = String(v).trim();
        if (!s.length) return null;
        if (s.endsWith('%')) {
            const n = parseFloat(s.slice(0, -1));
            return isNaN(n) ? null : Math.max(0, Math.min(1, n / 100));
        }
        const n = parseFloat(s);
        if (isNaN(n)) return null;
        if (n > 1) return Math.max(0, Math.min(1, n / 100));
        return Math.max(0, Math.min(1, n));
    }

    const rawStart = spacer && spacer.dataset && (spacer.dataset.openStart || spacer.dataset.start);
    const rawEnd = spacer && spacer.dataset && (spacer.dataset.openEnd || spacer.dataset.end);

    let ancestor = null;
    try {
        ancestor = $book && $book[0] ? $book[0].closest('[data-open-start],[data-start],[data-open-end],[data-end]') : null;
    } catch (e) {
        ancestor = null;
    }

    const rawStartBk = ancestor && ancestor.dataset ? (ancestor.dataset.openStart || ancestor.dataset.start) : null;
    const rawEndBk = ancestor && ancestor.dataset ? (ancestor.dataset.openEnd || ancestor.dataset.end) : null;

    const configuredStart = parseFraction(rawStart != null ? rawStart : rawStartBk);
    const configuredEnd = parseFraction(rawEnd != null ? rawEnd : rawEndBk);

    function parseBoost(v) {
        if (v == null) return null;
        const n = parseFloat(String(v).trim());
        return isNaN(n) ? null : n;
    }

    const rawBoost = spacer && spacer.dataset ? (spacer.dataset.openBoost || null) : null;
    const rawBoostAncestor = ancestor && ancestor.dataset ? (ancestor.dataset.openBoost || null) : null;
    const rawBoostBk = ($book && $book[0] && $book[0].dataset) ? ($book[0].dataset.openBoost || null) : null;

    const rawBoostLeft = spacer && spacer.dataset ? (spacer.dataset.openBoostLeft || spacer.dataset.openboostleft || null) : null;
    const rawBoostAncestorLeft = ancestor && ancestor.dataset ? (ancestor.dataset.openBoostLeft || ancestor.dataset.openboostleft || null) : null;
    const rawBoostBkLeft = ($book && $book[0] && $book[0].dataset) ? ($book[0].dataset.openBoostLeft || $book[0].dataset.openboostleft || null) : null;

    const rawBoostRight = spacer && spacer.dataset ? (spacer.dataset.openBoostRight || spacer.dataset.openboostright || null) : null;
    const rawBoostAncestorRight = ancestor && ancestor.dataset ? (ancestor.dataset.openBoostRight || ancestor.dataset.openboostright || null) : null;
    const rawBoostBkRight = ($book && $book[0] && $book[0].dataset) ? ($book[0].dataset.openBoostRight || $book[0].dataset.openboostright || null) : null;

    const maybeSingle = parseBoost(rawBoost != null ? rawBoost : (rawBoostAncestor != null ? rawBoostAncestor : rawBoostBk));

    const leftParsed = parseBoost(rawBoostLeft != null ? rawBoostLeft : (rawBoostAncestorLeft != null ? rawBoostAncestorLeft : rawBoostBkLeft));
    const rightParsed = parseBoost(rawBoostRight != null ? rawBoostRight : (rawBoostAncestorRight != null ? rawBoostAncestorRight : rawBoostBkRight));

    const configuredBoostLeft = leftParsed != null ? leftParsed : (maybeSingle != null ? maybeSingle : 20);
    const configuredBoostRight = rightParsed != null ? rightParsed : (maybeSingle != null ? maybeSingle : 20);

    function easeOutCubic(x) {
        return 1 - Math.pow(1 - x, 3);
    }

    function identity(x) {
        return x;
    }

    const rawEasing = spacer && spacer.dataset ? (spacer.dataset.openEasing || spacer.dataset.openeasing || null) : null;
    const rawAncestorEasing = ancestor && ancestor.dataset ? (ancestor.dataset.openEasing || ancestor.dataset.openeasing || null) : null;
    const rawBookEasing = ($book && $book[0] && $book[0].dataset) ? ($book[0].dataset.openEasing || $book[0].dataset.openeasing || null) : null;
    const chosenEasing = rawEasing != null ? rawEasing : (rawAncestorEasing != null ? rawAncestorEasing : rawBookEasing);
    const easingFn = (chosenEasing && String(chosenEasing).toLowerCase() === 'none') ? identity : easeOutCubic;


    function updateFromProgress(p) {
        p = Math.max(0, Math.min(1, p));

        const e = easingFn(p);
        try {
            $book[0].style.setProperty('--book-turn', e);
        } catch (err) {}

        const targets = {
            '.front': -160,
            '.page1': -150,
            '.page2': -30,
            '.page3': -140,
            '.page4': -40,
            '.page5': -130,
            '.page6': -50,
            '.back': -20
        };

        const spineOpacity = Math.max(0, Math.min(1, (e - 0.05) / 0.95));
        $pages.find('.spine').css('opacity', spineOpacity);
        const backShift = Math.min(10, e * 18);

        const scale = 1 + 0.1 * e;
        const shadowAlpha = 0.2 * e;
        Object.entries(targets).forEach(([sel, angle]) => {
            try {
                const $el = $book.find(sel);
                if ($el.length) {
                    const rightSide = ['.front', '.page1', '.page3', '.page5'];
                    const leftSide = ['.back', '.page2', '.page4', '.page6'];
                    const boostToUse = rightSide.includes(sel) ? configuredBoostRight : configuredBoostLeft;
                    const ang = (angle + boostToUse) * e;
                    if (sel === '.back') {
                        $el.css('transform', `rotateY(${ang}deg) scale(${scale}) translateX(${-backShift}px)`);
                    } else {
                        $el.css('transform', `rotateY(${ang}deg) scale(${scale})`);
                    }
                    if (sel !== '.back') {
                        $el.css('box-shadow', `0 1em 3em 0 rgba(0,0,0,${shadowAlpha})`);
                    }
                }
            } catch (e) {}
        });

    }

    updateFromProgress(0);

    function disableCssHoverOpening() {
        if (window.matchMedia && window.matchMedia('(hover: none)').matches) return;

        try {
            $book.css('pointer-events', 'none');

            $book.find('.front, .page1, .page2, .page3, .page4, .page5, .page6, .back')
                .css('pointer-events', 'auto');
        } catch (e) {}
    }
    disableCssHoverOpening();

    function computeProgress() {
        const scrollTop = window.scrollY || window.pageYOffset || document.documentElement.scrollTop;

        const max = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
        const docNorm = scrollTop / max;

        if (configuredStart != null && configuredEnd != null && configuredEnd > configuredStart) {
            const mapped = (docNorm - configuredStart) / (configuredEnd - configuredStart);
            return Math.max(0, Math.min(1, mapped));
        }

        if (spacer && spacer instanceof Element) {
            const spacerTop = spacer.offsetTop;
            const spacerHeight = spacer.offsetHeight || 1;
            const viewH = window.innerHeight || document.documentElement.clientHeight;

            const start = spacerTop - viewH;
            const end = spacerTop + spacerHeight;

            const denom = (end - start) || 1;
            const v = (scrollTop - start) / denom;
            return Math.max(0, Math.min(1, v));
        }

        return Math.max(0, Math.min(1, docNorm));
    }

    function onScroll() {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            const progress = computeProgress();
            updateFromProgress(progress);
            ticking = false;
        });
    }

    window.addEventListener('scroll', onScroll, {
        passive: true
    });

    window.addEventListener('wheel', onScroll, {
        passive: true
    });

    window.addEventListener('resize', () => {
        onScroll();
    });

    function ensureSpacer() {
        const spacer = document.querySelector('.page-scroll-spacer');
        if (!spacer) return;
        spacer.style.height = '300vh';
    }
    ensureSpacer();

});