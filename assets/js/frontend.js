(function () {
	'use strict';

	function initFilters(root) {
		var pills = root.querySelectorAll('.awaid-unit-filter');
		var cards = root.querySelectorAll('.awaid-unit-card');
		if (!pills.length || !cards.length) {
			return;
		}
		var items = Array.prototype.slice.call(cards).map(function (card) {
			return {
				card: card,
				box: card.closest('.awaid-unit-col') || card
			};
		});

		function apply(filter) {
			items.forEach(function (item) {
				var st = item.card.getAttribute('data-status') || '';
				var show = filter === 'all' || st === filter;
				item.box.style.display = show ? '' : 'none';
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

	var AWAID_KITCHEN_SVG =
		'<svg class="awaid-unit-modal__kitchen-ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="19" height="19" fill="none" aria-hidden="true">' +
		'<path d="M21 17C18.2386 17 16 14.7614 16 12C16 9.23858 18.2386 7 21 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>' +
		'<path d="M21 21C16.0294 21 12 16.9706 12 12C12 7.02944 16.0294 3 21 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>' +
		'<path d="M6 3L6 8M6 21L6 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>' +
		'<path d="M3.5 8H8.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>' +
		'<path d="M9 3L9 7.35224C9 12.216 3 12.2159 3 7.35207L3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>' +
		'</svg>';

	function awaidEsc(s) {
		return String(s == null ? '' : s)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/"/g, '&quot;');
	}

	function awaidNl2brEsc(t) {
		return String(t || '')
			.split('\n')
			.map(function (line) {
				return awaidEsc(line);
			})
			.join('<br>');
	}

	function initUnitModal(root) {
		var scriptEl = document.getElementById('awaid-units-modal-data');
		var modal = document.getElementById('awaid-unit-modal');
		if (!scriptEl || !modal) {
			return;
		}
		var payload;
		try {
			payload = JSON.parse(scriptEl.textContent || '{}');
		} catch (err) {
			return;
		}
		if (!payload.units || !payload.units.length) {
			return;
		}
		var byCode = {};
		payload.units.forEach(function (u) {
			byCode[u.code] = u;
		});
		var icons = payload.icons || {};
		var labels = payload.labels || {};

		var body = document.getElementById('awaid-unit-modal-body');
		if (!body) {
			return;
		}
		var unitModalSwiper = null;

		function closeUnitModal() {
			modal.setAttribute('hidden', '');
			modal.setAttribute('aria-hidden', 'true');
			if (unitModalSwiper) {
				try {
					unitModalSwiper.destroy(true, true);
				} catch (e) {
					/* noop */
				}
				unitModalSwiper = null;
			}
			body.innerHTML = '';
			document.body.style.overflow = '';
		}

		function buildUnitModalHtml(u) {
			var p = [];
			if (u.gallery && u.gallery.length) {
				p.push('<div class="awaid-unit-modal__swiper-shell" dir="ltr"><div class="swiper awaid-unit-modal__swiper"><div class="swiper-wrapper">');
				u.gallery.forEach(function (g) {
					p.push(
						'<div class="swiper-slide"><a class="awaid-unit-modal__slide-link item-link" href="' +
							awaidEsc(g.full) +
							'" data-awaid-lightbox-open data-src="' +
							awaidEsc(g.full) +
							'" data-alt="' +
							awaidEsc(g.alt) +
							'"><figure class="awaid-unit-modal__slide-fig" style="background-image:url(' +
							awaidEsc(g.large) +
							');"></figure></a></div>'
					);
				});
				p.push('</div>');
				if (u.gallery.length > 1) {
					p.push(
						'<div class="swiper-button-prev awaid-unit-modal__nav awaid-unit-modal__prev" role="button" tabindex="0"></div>' +
							'<div class="swiper-button-next awaid-unit-modal__nav awaid-unit-modal__next" role="button" tabindex="0"></div>' +
							'<div class="swiper-pagination awaid-unit-modal__pagination"></div>'
					);
				}
				p.push('</div></div>');
			}

			p.push('<div class="awaid-unit-modal__posthead">');
			p.push('<h3 class="awaid-unit-modal__post-title">' + awaidEsc(u.code) + ' ');
			if (u.type) {
				p.push('<span class="awaid-unit-modal__type-badge">' + awaidEsc(u.type) + '</span>');
			}
			p.push('</h3>');

			p.push('<div class="awaid-unit-modal__meta-box"><ul class="awaid-unit-modal__meta">');
			if (u.bathrooms) {
				p.push('<li>');
				if (icons.bathrooms) {
					p.push('<img src="' + awaidEsc(icons.bathrooms) + '" width="20" height="20" alt="" class="dark-image">');
				}
				p.push('<span>' + awaidEsc(u.bathrooms) + '</span></li>');
			}
			if (u.bedrooms) {
				p.push('<li>');
				if (icons.bedrooms) {
					p.push('<img src="' + awaidEsc(icons.bedrooms) + '" width="20" height="20" alt="" class="dark-image">');
				}
				p.push('<span>' + awaidEsc(u.bedrooms) + '</span></li>');
			}
			if (u.area) {
				p.push('<li>');
				if (icons.area) {
					p.push('<img src="' + awaidEsc(icons.area) + '" width="20" height="20" alt="" class="dark-image">');
				}
				p.push('<span>' + awaidEsc(u.area) + ' م²</span></li>');
			}
			if (u.kitchens) {
				p.push('<li>' + AWAID_KITCHEN_SVG + '<span>' + awaidEsc(u.kitchens) + '</span></li>');
			}
			if (u.floor) {
				p.push('<li><span class="awaid-unit-modal__meta-k">' + awaidEsc(labels.floor) + ':</span> <span>' + awaidEsc(u.floor) + '</span></li>');
			}
			p.push('</ul></div></div>');

			if (u.description) {
				p.push('<div class="awaid-unit-modal__desc">' + awaidNl2brEsc(u.description) + '</div>');
			}

			if (u.highlights && u.highlights.length) {
				p.push('<h4 class="awaid-unit-modal__feat-head">' + awaidEsc(labels.features) + '</h4><div class="awaid-unit-modal__highlights">');
				u.highlights.forEach(function (h) {
					if (!h || (!h.title && !h.text && !h.icon)) {
						return;
					}
					p.push('<div class="awaid-unit-modal__highlight">');
					if (h.icon) {
						p.push('<div class="awaid-unit-modal__highlight-ic"><img src="' + awaidEsc(h.icon) + '" width="50" height="50" alt=""></div>');
					}
					p.push('<div class="awaid-unit-modal__highlight-body">');
					if (h.title) {
						p.push('<h5 class="awaid-unit-modal__highlight-title">' + awaidEsc(h.title) + '</h5>');
					}
					if (h.text) {
						p.push('<p class="awaid-unit-modal__highlight-text">' + awaidNl2brEsc(h.text) + '</p>');
					}
					p.push('</div></div>');
				});
				p.push('</div>');
			}

			if (u.price) {
				p.push('<h3 class="awaid-unit-modal__price">');
				p.push('<span class="awaid-unit-modal__price-label">' + awaidEsc(labels.price) + '</span> ');
				p.push(awaidEsc(u.price) + ' ');
				if (icons.currency) {
					p.push('<img class="awaid-unit-modal__currency" src="' + awaidEsc(icons.currency) + '" width="14" height="14" alt="">');
				}
				p.push('</h3>');
			}

			p.push('<div class="awaid-unit-modal__actions">');
			if (u.whatsappUrl) {
				p.push(
					'<a class="awaid-btn awaid-btn--primary awaid-btn--block" href="' +
						awaidEsc(u.whatsappUrl) +
						'" target="_blank" rel="noopener">' +
						awaidEsc(labels.whatsapp) +
						'</a>'
				);
			}
			if (u.telUrl) {
				p.push('<a class="awaid-btn awaid-btn--secondary awaid-btn--block" href="' + awaidEsc(u.telUrl) + '">' + awaidEsc(labels.call) + '</a>');
			}
			p.push('</div>');

			return p.join('');
		}

		function openUnitModal(code) {
			var u = byCode[code];
			if (!u) {
				return;
			}
			closeUnitModal();
			body.innerHTML = buildUnitModalHtml(u);
			modal.removeAttribute('hidden');
			modal.setAttribute('aria-hidden', 'false');
			document.body.style.overflow = 'hidden';

			var swEl = body.querySelector('.awaid-unit-modal__swiper');
			if (swEl && typeof Swiper !== 'undefined') {
				var slides = swEl.querySelectorAll('.swiper-slide');
				var opts = {
					slidesPerView: 1,
					spaceBetween: 0,
					autoHeight: true,
					observer: true,
					observeParents: true,
					roundLengths: true,
					watchOverflow: true,
				};
				if (slides.length > 1) {
					opts.pagination = {
						el: body.querySelector('.awaid-unit-modal__pagination'),
						clickable: true,
					};
					opts.navigation = {
						nextEl: body.querySelector('.awaid-unit-modal__next'),
						prevEl: body.querySelector('.awaid-unit-modal__prev'),
					};
				}
				unitModalSwiper = new Swiper(swEl, opts);
			}

			var cbtn = modal.querySelector('.awaid-unit-modal__close');
			if (cbtn) {
				cbtn.focus();
			}
		}

		root.addEventListener('click', function (e) {
			var card = e.target.closest('.awaid-unit-card--interactive');
			if (!card || !root.contains(card)) {
				return;
			}
			if (e.target.closest('a,button,input,select,textarea')) {
				return;
			}
			var code = card.getAttribute('data-awaid-unit-code');
			if (code) {
				openUnitModal(code);
			}
		});

		root.addEventListener('keydown', function (e) {
			if (e.key !== 'Enter' && e.key !== ' ') {
				return;
			}
			var card = e.target.closest('.awaid-unit-card--interactive');
			if (!card || !root.contains(card) || e.target !== card) {
				return;
			}
			e.preventDefault();
			var code = card.getAttribute('data-awaid-unit-code');
			if (code) {
				openUnitModal(code);
			}
		});

		modal.addEventListener('click', function (e) {
			if (e.target.closest('[data-awaid-unit-modal-close]')) {
				e.preventDefault();
				closeUnitModal();
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !modal.hasAttribute('hidden')) {
				closeUnitModal();
			}
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		var root = document.querySelector('.awaid-project');
		if (root) {
			initFilters(root);
			initProjectSwiper(root);
			initLightbox(root);
			initUnitModal(root);
			initDesktopSidebarSticky(root);
		}
	});
})();
