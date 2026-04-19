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

	document.addEventListener('DOMContentLoaded', function () {
		var root = document.querySelector('.awaid-project');
		if (root) {
			initFilters(root);
		}
	});
})();
