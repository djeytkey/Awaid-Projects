(function ($) {
	'use strict';

	function renderLucide() {
		if (window.lucide && typeof window.lucide.createIcons === 'function') {
			window.lucide.createIcons({
				attrs: {
					'stroke-width': 1.9
				}
			});
		}
	}

	function escHtml(s) {
		return String(s == null ? '' : s)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function toKebabCase(name) {
		return String(name || '')
			.replace(/([a-z0-9])([A-Z])/g, '$1-$2')
			.replace(/([A-Z])([A-Z][a-z])/g, '$1-$2')
			.replace(/_/g, '-')
			.toLowerCase();
	}

	function getFallbackLucideIcons() {
		var el = document.getElementById('awaid-lucide-fallback-icons');
		if (!el) {
			return [];
		}
		try {
			var parsed = JSON.parse(el.textContent || '[]');
			return Array.isArray(parsed) ? parsed : [];
		} catch (e) {
			return [];
		}
	}

	function getAllLucideIconNames() {
		var names = [];
		if (window.lucide && window.lucide.icons && typeof window.lucide.icons === 'object') {
			names = Object.keys(window.lucide.icons)
				.map(toKebabCase)
				.filter(function (n) {
					return /^[a-z0-9-]+$/.test(n);
				});
		}
		if (!names.length) {
			names = getFallbackLucideIcons();
		}
		var uniq = {};
		names.forEach(function (n) {
			if (!n) {
				return;
			}
			uniq[n] = true;
		});
		return Object.keys(uniq).sort();
	}

	function nextIndex($tbody) {
		return String(Date.now());
	}

	function addFromTemplate($tbody, templateId) {
		var html = $('#' + templateId).html();
		if (!html) {
			return;
		}
		html = html.replace(/__i__/g, nextIndex($tbody));
		$tbody.append(html);
		renderLucide();
	}

	$(document).on('input', '.awaid-catalog-search', function () {
		var q = String($(this).val() || '').toLowerCase().trim();
		var target = $(this).data('target');
		if (!target) {
			return;
		}
		$(target).find('.awaid-catalog-option').each(function () {
			var txt = String($(this).text() || '').toLowerCase();
			$(this).toggle(q === '' || txt.indexOf(q) !== -1);
		});
	});

	$(document).on('click', '[data-awaid-catalog-toggle]', function (e) {
		e.preventDefault();
		var target = String($(this).data('target') || '');
		var action = String($(this).data('action') || '');
		if (!target || (action !== 'select' && action !== 'unselect')) {
			return;
		}
		var checked = action === 'select';
		$(target).find('input[type="checkbox"]').prop('checked', checked).trigger('change');
	});

	function addUnitBlock() {
		var idx = nextIndex($('#awaid_units_table'));
		var html = $('#tmpl-awaid-unit-block').html();
		if (!html) {
			return;
		}
		html = html.replace(/__i__/g, idx);
		$('#awaid_units_table').append(html);
	}

	$(document).on('click', '[data-awaid-add-unit]', function (e) {
		e.preventDefault();
		addUnitBlock();
	});

	$(document).on('click', '.awaid-unit-remove', function (e) {
		e.preventDefault();
		var $block = $(this).closest('tbody.awaid-unit-block');
		if (!$block.length) {
			$block = $(this).closest('tbody');
		}
		$block.remove();
		if ($('#awaid_units_table').find('tbody.awaid-unit-block').length === 0) {
			addUnitBlock();
		}
	});

	$(document).on('click', '.awaid-remove-row', function (e) {
		e.preventDefault();
		var $row = $(this).closest('tr');
		var $tb = $row.closest('tbody');
		$row.remove();
		if ($tb.find('tr').length === 0) {
			if ($tb.closest('#awaid_settings_features').length) {
				addFromTemplate($tb, 'tmpl-awaid-settings-row-feature');
			} else if ($tb.closest('#awaid_settings_warranties').length) {
				addFromTemplate($tb, 'tmpl-awaid-settings-row-warranty');
			} else if ($tb.closest('#awaid_settings_nearby').length) {
				addFromTemplate($tb, 'tmpl-awaid-settings-row-nearby');
			}
		}
	});

	$(document).on('click', '[data-awaid-settings-add]', function (e) {
		e.preventDefault();
		var type = String($(this).data('awaid-settings-add') || '');
		if (!type) {
			return;
		}
		var map = {
			features: 'tmpl-awaid-settings-row-feature',
			warranties: 'tmpl-awaid-settings-row-warranty',
			nearby: 'tmpl-awaid-settings-row-nearby'
		};
		var tmpl = map[type];
		if (!tmpl) {
			return;
		}
		var $tbody = $('#awaid_settings_' + type + ' tbody');
		if (!$tbody.length) {
			return;
		}
		addFromTemplate($tbody, tmpl);
	});

	function updateIconPreview($input) {
		var icon = String($input.val() || '').trim();
		var $row = $input.closest('tr');
		var $icon = $row.find('.awaid-icon-preview');
		if (!$icon.length) {
			return;
		}
		if (icon) {
			$icon.attr('data-lucide', icon);
		} else {
			$icon.removeAttr('data-lucide');
		}
		renderLucide();
	}

	$(document).on('input', '.awaid-icon-class-field', function () {
		updateIconPreview($(this));
	});

	$('.awaid-icon-class-field').each(function () {
		updateIconPreview($(this));
	});

	var $iconModal = $('#awaid-icon-modal');
	var $iconGrid = $('#awaid-icon-modal-grid');
	var $iconSearch = $('#awaid-icon-modal-search');
	var $currentIconInput = null;

	function rebuildLucidePicker() {
		var names = getAllLucideIconNames();
		if (!names.length) {
			return;
		}
		if ($iconGrid.length) {
			var html = names
				.map(function (icon) {
					return (
						'<button type="button" class="awaid-icon-choice" data-icon="' +
						escHtml(icon) +
						'" title="' +
						escHtml(icon) +
						'" aria-label="' +
						escHtml(icon) +
						'"><i data-lucide="' +
						escHtml(icon) +
						'" class="awaid-lucide-icon" aria-hidden="true"></i></button>'
					);
				})
				.join('');
			$iconGrid.html(html);
		}
		var $list = $('#awaid-lucide-icons-list');
		if ($list.length) {
			var opts = names
				.map(function (icon) {
					return '<option value="' + escHtml(icon) + '"></option>';
				})
				.join('');
			$list.html(opts);
		}
		renderLucide();
	}

	function openIconModal($input) {
		if (!$iconModal.length) {
			return;
		}
		if (!$iconGrid.find('.awaid-icon-choice').length) {
			rebuildLucidePicker();
		}
		$currentIconInput = $input;
		$iconModal.prop('hidden', false);
		$('body').addClass('awaid-icon-modal-open');
		if ($iconSearch.length) {
			$iconSearch.val('');
		}
		if ($iconGrid.length) {
			$iconGrid.find('.awaid-icon-choice').show();
		}
		setTimeout(function () {
			if ($iconSearch.length) {
				$iconSearch.trigger('focus');
			}
		}, 10);
	}

	function closeIconModal() {
		if (!$iconModal.length) {
			return;
		}
		$iconModal.prop('hidden', true);
		$('body').removeClass('awaid-icon-modal-open');
		$currentIconInput = null;
	}

	$(document).on('click', '.awaid-icon-picker-open', function (e) {
		e.preventDefault();
		var $input = $(this).closest('.awaid-icon-input-wrap').find('.awaid-icon-class-field');
		if (!$input.length) {
			return;
		}
		openIconModal($input);
	});

	$(document).on('click', '[data-awaid-icon-modal-close]', function (e) {
		e.preventDefault();
		closeIconModal();
	});

	$(document).on('keydown', function (e) {
		if (e.key === 'Escape' && $iconModal.length && !$iconModal.prop('hidden')) {
			closeIconModal();
		}
	});

	$(document).on('input', '#awaid-icon-modal-search', function () {
		var q = String($(this).val() || '').toLowerCase().trim();
		$iconGrid.find('.awaid-icon-choice').each(function () {
			var icon = String($(this).data('icon') || '').toLowerCase();
			$(this).toggle(q === '' || icon.indexOf(q) !== -1);
		});
	});

	$(document).on('click', '.awaid-icon-choice', function (e) {
		e.preventDefault();
		if (!$currentIconInput || !$currentIconInput.length) {
			return;
		}
		var cls = String($(this).data('icon') || '').trim();
		$currentIconInput.val(cls).trigger('input');
		closeIconModal();
	});

	rebuildLucidePicker();
	renderLucide();

	var frame;
	$('#awaid_brochure_pick').on('click', function (e) {
		e.preventDefault();
		if (frame) {
			frame.open();
			return;
		}
		frame = wp.media({
			title: awaidProjectsAdmin.i18n.selectFile,
			button: { text: awaidProjectsAdmin.i18n.useFile },
			multiple: false,
			library: { type: ['application/pdf', 'image'] }
		});
		frame.on('select', function () {
			var att = frame.state().get('selection').first().toJSON();
			$('#awaid_brochure_id').val(att.id);
			$('#awaid_brochure_label').text(att.filename || att.title || att.url);
		});
		frame.open();
	});

	$('#awaid_brochure_clear').on('click', function (e) {
		e.preventDefault();
		$('#awaid_brochure_id').val('0');
		$('#awaid_brochure_label').text(awaidProjectsAdmin.i18n.noFile);
	});

	var $galleryCsv = $('#awaid_gallery_csv');
	var $galleryList = $('#awaid_gallery_list');
	var galleryFrame;

	function parseGalleryIds() {
		var raw = ($galleryCsv.val() || '').split(',');
		var out = [];
		raw.forEach(function (x) {
			var n = parseInt(x, 10);
			if (n && out.indexOf(n) === -1) {
				out.push(n);
			}
		});
		return out;
	}

	function syncGalleryCsv(ids) {
		$galleryCsv.val(ids.join(','));
	}

	function appendGalleryItem(id, thumbUrl) {
		var esc = function (s) {
			return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
		};
		var html =
			'<li class="awaid-gallery-item" data-id="' +
			id +
			'">' +
			'<span class="awaid-gallery-thumb"><img src="' +
			esc(thumbUrl) +
			'" alt="" loading="lazy" decoding="async"></span>' +
			'<button type="button" class="button button-small awaid-gallery-remove" data-id="' +
			id +
			'">' +
			esc(awaidProjectsAdmin.i18n.removeImage) +
			'</button></li>';
		$galleryList.append(html);
	}

	if ($galleryCsv.length && $galleryList.length) {
		$('#awaid_gallery_add').on('click', function (e) {
			e.preventDefault();
			if (galleryFrame) {
				galleryFrame.open();
				return;
			}
			galleryFrame = wp.media({
				title: awaidProjectsAdmin.i18n.selectGallery,
				button: { text: awaidProjectsAdmin.i18n.useImages },
				multiple: true,
				library: { type: 'image' }
			});
			galleryFrame.on('select', function () {
				var ids = parseGalleryIds();
				galleryFrame.state().get('selection').each(function (att) {
					var j = att.toJSON();
					var id = parseInt(j.id, 10);
					if (!id || ids.indexOf(id) !== -1) {
						return;
					}
					ids.push(id);
					var t =
						(j.sizes && j.sizes.thumbnail && j.sizes.thumbnail.url) ||
						(j.sizes && j.sizes.medium && j.sizes.medium.url) ||
						j.url;
					appendGalleryItem(id, t);
				});
				syncGalleryCsv(ids);
			});
			galleryFrame.open();
		});

		$(document).on('click', '.awaid-gallery-remove', function (e) {
			e.preventDefault();
			var id = parseInt($(this).data('id'), 10);
			if (!id) {
				return;
			}
			var ids = parseGalleryIds().filter(function (x) {
				return x !== id;
			});
			syncGalleryCsv(ids);
			$galleryList.find('.awaid-gallery-item[data-id="' + id + '"]').remove();
		});
	}

	function parseUnitGalleryIds($csv) {
		var raw = ($csv.val() || '').split(',');
		var out = [];
		raw.forEach(function (x) {
			var n = parseInt(x, 10);
			if (n && out.indexOf(n) === -1) {
				out.push(n);
			}
		});
		return out;
	}

	function syncUnitGalleryCsv($csv, ids) {
		$csv.val(ids.join(','));
	}

	function appendUnitGalleryThumb($list, id, thumbUrl) {
		var esc = function (s) {
			return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
		};
		var html =
			'<li class="awaid-gallery-item" data-id="' +
			id +
			'"><span class="awaid-gallery-thumb"><img src="' +
			esc(thumbUrl) +
			'" alt="" loading="lazy" decoding="async"></span><button type="button" class="button button-small awaid-unit-gallery-remove" data-id="' +
			id +
			'">' +
			esc(awaidProjectsAdmin.i18n.removeImage) +
			'</button></li>';
		$list.append(html);
	}

	$(document).on('click', '.awaid-unit-gallery-add', function (e) {
		e.preventDefault();
		var $block = $(this).closest('.awaid-unit-block');
		var $csv = $block.find('.awaid-unit-gallery-csv');
		var $list = $block.find('.awaid-unit-gallery-list');

		var frameSingle = wp.media({
			title: awaidProjectsAdmin.i18n.selectGallery,
			button: { text: awaidProjectsAdmin.i18n.useImages },
			multiple: false,
			library: { type: 'image' }
		});

		frameSingle.on('select', function () {
			var att = frameSingle.state().get('selection').first();
			if (!att) {
				return;
			}
			var j = att.toJSON();
			var id = parseInt(j.id, 10);
			if (!id) {
				return;
			}
			var t =
				(j.sizes && j.sizes.thumbnail && j.sizes.thumbnail.url) ||
				(j.sizes && j.sizes.medium && j.sizes.medium.url) ||
				j.url;
			syncUnitGalleryCsv($csv, [id]);
			$list.empty();
			appendUnitGalleryThumb($list, id, t);
		});

		frameSingle.open();
	});

	$(document).on('click', '.awaid-unit-gallery-remove', function (e) {
		e.preventDefault();
		var id = parseInt($(this).data('id'), 10);
		if (!id) {
			return;
		}
		var $block = $(this).closest('.awaid-unit-block');
		var $csv = $block.find('.awaid-unit-gallery-csv');
		var $list = $block.find('.awaid-unit-gallery-list');
		var ids = parseUnitGalleryIds($csv).filter(function (x) {
			return x !== id;
		});
		syncUnitGalleryCsv($csv, ids);
		$list.find('.awaid-gallery-item[data-id="' + id + '"]').remove();
	});
})(jQuery);
