/**
 * Antonia Zanolli – Minimal Lightbox
 *
 * Automatically activates on any WordPress gallery link (`.wp-block-gallery a`,
 * `.gallery a`) that points to an image file. Also works on any <a> with the
 * class `lightbox-trigger`.
 *
 * Keyboard: Escape = close, ArrowLeft = prev, ArrowRight = next.
 * Accessibility: focus is trapped inside the overlay while open.
 */
!function () {
	'use strict';

	function linkGalleryImages() {
		const galleryImages = document.querySelectorAll(
			'.wp-block-gallery img, .blocks-gallery-grid img, .gallery img'
		);

		galleryImages.forEach(function (image) {
			if (image.closest('a')) return;

			const src = image.currentSrc || image.getAttribute('src');
			if (!src) return;

			const link = document.createElement('a');
			link.href = src;
			link.className = 'antonia-auto-lightbox-link';

			if (image.alt) {
				link.setAttribute('data-caption', image.alt);
			}

			image.parentNode.insertBefore(link, image);
			link.appendChild(image);
		});
	}

	// ── DOM creation ─────────────────────────────────────────────────────────

	const overlay = document.createElement('div');
	overlay.className = 'antonia-lightbox-overlay';
	overlay.setAttribute('role', 'dialog');
	overlay.setAttribute('aria-modal', 'true');
	overlay.setAttribute('aria-label', 'Image viewer');

	overlay.innerHTML =
		'<button class="antonia-lightbox-prev" aria-label="Previous image">&#8592;</button>' +
		'<div class="antonia-lightbox-inner">' +
			'<button class="antonia-lightbox-close" aria-label="Close image viewer">&times;</button>' +
			'<img src="" alt="" />' +
			'<p class="antonia-lightbox-caption"></p>' +
		'</div>' +
		'<button class="antonia-lightbox-next" aria-label="Next image">&#8594;</button>';

	document.body.appendChild(overlay);

	const img     = overlay.querySelector('img');
	const caption = overlay.querySelector('.antonia-lightbox-caption');
	const closeBtn = overlay.querySelector('.antonia-lightbox-close');
	const prevBtn  = overlay.querySelector('.antonia-lightbox-prev');
	const nextBtn  = overlay.querySelector('.antonia-lightbox-next');

	let items   = [];   // Array of { src, caption } collected from the gallery
	let current = 0;

	// ── Helpers ───────────────────────────────────────────────────────────────

	const IMAGE_RE = /\.(jpe?g|png|gif|webp|avif|svg)(\?.*)?$/i;

	function isImageUrl(href) {
		return href && IMAGE_RE.test(href.split('#')[0]);
	}

	function show(index) {
		current = (index + items.length) % items.length;
		img.src = '';                          // reset so load event fires reliably
		img.alt = items[current].caption || '';
		img.src = items[current].src;
		caption.textContent = items[current].caption || '';
		prevBtn.style.display = items.length > 1 ? '' : 'none';
		nextBtn.style.display = items.length > 1 ? '' : 'none';
	}

	function open(anchors, startIndex) {
		items = anchors.map(function (a) {
			return {
				src: a.href,
				caption: a.getAttribute('data-caption') ||
				         (a.querySelector('img') ? a.querySelector('img').alt : '') || ''
			};
		});
		show(startIndex);
		overlay.classList.add('is-open');
		document.body.style.overflow = 'hidden';
		closeBtn.focus();
	}

	function close() {
		overlay.classList.remove('is-open');
		document.body.style.overflow = '';
		img.src = '';
		if (overlay._returnFocus) {
			try { overlay._returnFocus.focus(); } catch (e) {}
			overlay._returnFocus = null;
		}
	}

	// ── Event wiring ─────────────────────────────────────────────────────────

	linkGalleryImages();

	closeBtn.addEventListener('click', close);
	prevBtn.addEventListener('click', function () { show(current - 1); });
	nextBtn.addEventListener('click', function () { show(current + 1); });

	overlay.addEventListener('click', function (e) {
		if (e.target === overlay) close();
	});

	document.addEventListener('keydown', function (e) {
		if (!overlay.classList.contains('is-open')) return;
		if (e.key === 'Escape')      { e.preventDefault(); close(); }
		if (e.key === 'ArrowLeft')   { e.preventDefault(); show(current - 1); }
		if (e.key === 'ArrowRight')  { e.preventDefault(); show(current + 1); }
	});

	// ── Delegate click handler ────────────────────────────────────────────────

	document.addEventListener('click', function (e) {
		const anchor = e.target.closest(
			'.wp-block-gallery a, .blocks-gallery-grid a, .gallery a, .wp-block-image a, a.lightbox-trigger, [data-lightbox] a'
		);
		if (!anchor || !isImageUrl(anchor.href)) return;

		e.preventDefault();

		// Collect sibling image links from the same gallery block / container
		const container = anchor.closest(
			'.wp-block-gallery, .blocks-gallery-grid, .gallery, .wp-block-image, [data-lightbox]'
		) || document;

		const allAnchors = Array.from(
			container.querySelectorAll('a')
		).filter(function (a) { return isImageUrl(a.href); });

		overlay._returnFocus = anchor;
		open(allAnchors, allAnchors.indexOf(anchor));
	});
}();
