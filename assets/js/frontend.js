(function () {
	'use strict';

	function initFilters(root) {
		var pills = root.querySelectorAll('.awaid-unit-filter');
		var cards = root.querySelectorAll('.awaid-unit-card');
		if (!pills.length || !cards.length) {
			return;
		}

		function apply(filter) {
			cards.forEach(function (card) {
				var st = card.getAttribute('data-status') || '';
				var show = filter === 'all' || st === filter;
				card.style.display = show ? '' : 'none';
			});
			pills.forEach(function (p) {
				var on = p.getAttribute('data-filter') === filter;
				p.classList.toggle('is-active', on);
				p.setAttribute('aria-pressed', on ? 'true' : 'false');
			});
		}

		pills.forEach(function (p) {
			p.addEventListener('click', function (e) {
				e.preventDefault();
				apply(p.getAttribute('data-filter') || 'all');
			});
		});
	}

	/**
	 * Swiper gallery (Riva-style: nav only, spaceBetween from data-margin).
	 */
	function initProjectSwiper(root) {
		var shell = root.querySelector('[data-awaid-swiper]');
		if (!shell || typeof Swiper === 'undefined') {
			return;
		}
		var el = shell.querySelector('.swiper');
		if (!el) {
			return;
		}
		var slides = el.querySelectorAll('.swiper-slide');
		if (!slides.length) {
			return;
		}
		var margin = parseInt(shell.getAttribute('data-margin'), 10);
		if (isNaN(margin)) {
			margin = 10;
		}
		var navNext = shell.querySelector('.swiper-button-next');
		var navPrev = shell.querySelector('.swiper-button-prev');
		var opts = {
			slidesPerView: 1,
			spaceBetween: margin,
			speed: 450,
			roundLengths: true,
			watchOverflow: true,
			autoHeight: true,
			observer: true,
			observeParents: true,
			observeSlideChildren: true,
		};
		/* Do not set Swiper `rtl`: it reverses slide flow vs. arrow glyphs and feels “wrong” on RTL pages.
		   Gallery is forced LTR via dir="ltr" on the shell (see template + CSS). */
		if (navNext && navPrev && slides.length > 1) {
			opts.navigation = {
				nextEl: navNext,
				prevEl: navPrev,
			};
		}
		var swiper = new Swiper(el, opts);

		var resizeTimer;
		function refit() {
			if (swiper && typeof swiper.update === 'function') {
				swiper.update();
			}
			if (swiper && swiper.params && swiper.params.autoHeight && typeof swiper.updateAutoHeight === 'function') {
				swiper.updateAutoHeight(0);
			}
		}

		el.querySelectorAll('.awaid-slide-img').forEach(function (img) {
			if (img.complete) {
				return;
			}
			img.addEventListener('load', refit, { passive: true });
		});
		window.addEventListener(
			'resize',
			function () {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(refit, 120);
			},
			{ passive: true }
		);

		requestAnimationFrame(refit);
	}

	function initLightbox(root) {
		var lb = root.querySelector('#awaid-project-lightbox');
		if (!lb) {
			return;
		}
		var img = lb.querySelector('[data-awaid-lightbox-img]');
		var btnPrev = lb.querySelector('[data-awaid-lightbox-prev]');
		var btnNext = lb.querySelector('[data-awaid-lightbox-next]');
		if (!img) {
			return;
		}

		var items = [];
		var idx = 0;

		function refreshItems() {
			items = Array.prototype.slice.call(root.querySelectorAll('[data-awaid-lightbox-open]'));
		}

		function show(at) {
			refreshItems();
			if (!items.length) {
				return;
			}
			idx = ((at % items.length) + items.length) % items.length;
			var el = items[idx];
			var src = el.getAttribute('data-src') || '';
			var alt = el.getAttribute('data-alt') || '';
			img.src = src;
			img.alt = alt;
			lb.removeAttribute('hidden');
			lb.classList.add('is-open');
			document.documentElement.classList.add('awaid-lightbox-on');
			var closeBtn = lb.querySelector('.awaid-lightbox__close');
			if (closeBtn) {
				closeBtn.focus();
			}
		}

		function hide() {
			lb.classList.remove('is-open');
			lb.setAttribute('hidden', '');
			document.documentElement.classList.remove('awaid-lightbox-on');
			img.removeAttribute('src');
			img.alt = '';
		}

		function step(delta) {
			refreshItems();
			if (items.length < 2) {
				return;
			}
			show(idx + delta);
		}

		root.addEventListener('click', function (e) {
			var t = e.target.closest('[data-awaid-lightbox-open]');
			if (!t || !root.contains(t)) {
				return;
			}
			e.preventDefault();
			refreshItems();
			idx = items.indexOf(t);
			show(idx >= 0 ? idx : 0);
		});

		lb.addEventListener('click', function (e) {
			if (e.target.closest('[data-awaid-lightbox-close]')) {
				e.preventDefault();
				hide();
			}
		});

		if (btnPrev) {
			btnPrev.addEventListener('click', function (e) {
				e.preventDefault();
				step(-1);
			});
		}
		if (btnNext) {
			btnNext.addEventListener('click', function (e) {
				e.preventDefault();
				step(1);
			});
		}

		document.addEventListener('keydown', function (e) {
			if (!lb.classList.contains('is-open')) {
				return;
			}
			if (e.key === 'Escape') {
				e.preventDefault();
				hide();
			} else if (e.key === 'ArrowLeft') {
				e.preventDefault();
				step(-1);
			} else if (e.key === 'ArrowRight') {
				e.preventDefault();
				step(1);
			}
		});
	}

	/**
	 * Desktop sidebar: pin with position:fixed while scrolling (CSS sticky often fails when
	 * any ancestor has overflow:hidden, e.g. Rehomes .site-content-contain).
	 */
	function initDesktopSidebarSticky(root) {
		var aside = root.querySelector('.awaid-split-sidebar');
		var card = aside && aside.querySelector('.awaid-sidebar--desktop');
		if (!aside || !card) {
			return;
		}

		var mq = window.matchMedia('(min-width: 992px)');
		var ph = null;
		var rafId = null;
		var footerEl = null;

		function findFooter() {
			if (footerEl && document.body.contains(footerEl)) {
				return footerEl;
			}
			footerEl = null;

			var col = document.getElementById('colophon');
			if (col && !col.closest('.awaid-project') && col.getBoundingClientRect().height > 8) {
				footerEl = col;
				return footerEl;
			}

			var selectors = [
				'footer.site-footer',
				'.site-footer',
				'footer.elementor-location-footer',
				'body > footer',
				'.site-footer-wrap',
				'#opal-footer',
				'.opal-footer',
				'footer'
			];
			var s;
			var i;
			var list;
			var el;
			for (s = 0; s < selectors.length; s++) {
				list = document.querySelectorAll(selectors[s]);
				for (i = 0; i < list.length; i++) {
					el = list[i];
					if (!el || el.closest('.awaid-project')) {
						continue;
					}
					if (el.getBoundingClientRect().height < 8) {
						continue;
					}
					if (root.compareDocumentPosition(el) & Node.DOCUMENT_POSITION_FOLLOWING) {
						footerEl = el;
						return footerEl;
					}
				}
			}
			for (s = 0; s < selectors.length; s++) {
				list = document.querySelectorAll(selectors[s]);
				for (i = 0; i < list.length; i++) {
					el = list[i];
					if (el && !el.closest('.awaid-project') && el.getBoundingClientRect().height >= 8) {
						footerEl = el;
						return footerEl;
					}
				}
			}
			return null;
		}

		function limitTop() {
			var bar = document.getElementById('wpadminbar');
			var bh = 0;
			if (bar && document.body.classList.contains('admin-bar')) {
				bh = bar.offsetHeight || 0;
			}
			return bh + 16;
		}

		function ensurePlaceholder(height) {
			if (!ph) {
				ph = document.createElement('div');
				ph.className = 'awaid-sidebar-sticky-placeholder';
				ph.setAttribute('aria-hidden', 'true');
				aside.insertBefore(ph, card);
			}
			ph.style.height = height + 'px';
			ph.style.flexShrink = '0';
		}

		function removePlaceholder() {
			if (ph && ph.parentNode) {
				ph.parentNode.removeChild(ph);
			}
			ph = null;
		}

		function reset() {
			card.style.cssText = '';
			removePlaceholder();
		}

		function tick() {
			rafId = null;
			if (!mq.matches) {
				reset();
				return;
			}
			if (aside.offsetParent === null) {
				reset();
				return;
			}

			var a = aside.getBoundingClientRect();
			var h = card.offsetHeight;
			if (h <= 0 || a.width <= 0) {
				reset();
				return;
			}

			var limit = limitTop();

			/* Above the pin line: natural layout */
			if (a.top >= limit) {
				reset();
				return;
			}

			/* Pin: prefer `limit`, but move up so the card stays inside the column bottom */
			var topPx = Math.max(0, Math.min(limit, a.bottom - h));

			var footer = findFooter();
			var footerGap = 32;
			if (footer) {
				var fr = footer.getBoundingClientRect();
				/* Bottom edge of card must stay at or above (footer top − gap), regardless of viewport */
				var ceiling = fr.top - footerGap;
				topPx = Math.min(topPx, ceiling - h);
				topPx = Math.max(0, topPx);
				/* If we still cannot fit above the footer, do not pin */
				if (topPx + h > ceiling + 1) {
					reset();
					return;
				}
				if (topPx < a.top - 4) {
					reset();
					return;
				}
			}

			ensurePlaceholder(h);
			card.style.position = 'fixed';
			card.style.top = topPx + 'px';
			card.style.left = a.left + 'px';
			card.style.width = a.width + 'px';
			card.style.boxSizing = 'border-box';
			/* Stay under typical footer stacking so we do not paint over the footer if geometry slips */
			card.style.zIndex = '2';
		}

		function reqTick() {
			if (rafId !== null) {
				return;
			}
			rafId = window.requestAnimationFrame(function () {
				rafId = null;
				tick();
			});
		}

		window.addEventListener('scroll', reqTick, { passive: true });
		window.addEventListener('resize', function () {
			footerEl = null;
			reqTick();
		});
		if (mq.addEventListener) {
			mq.addEventListener('change', reqTick);
		} else if (mq.addListener) {
			mq.addListener(reqTick);
		}
		reqTick();
	}

	document.addEventListener('DOMContentLoaded', function () {
		var root = document.querySelector('.awaid-project');
		if (root) {
			initFilters(root);
			initProjectSwiper(root);
			initLightbox(root);
			initDesktopSidebarSticky(root);
		}
	});
})();
