(function ($) {
	'use strict';

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
	}

	$(document).on('click', '[data-awaid-add-feature]', function (e) {
		e.preventDefault();
		addFromTemplate($('#awaid_features_table tbody'), 'tmpl-awaid-feature-row');
	});

	$(document).on('click', '[data-awaid-add-warranty]', function (e) {
		e.preventDefault();
		addFromTemplate($('#awaid_warranties_table tbody'), 'tmpl-awaid-warranty-row');
	});

	$(document).on('click', '[data-awaid-add-nearby]', function (e) {
		e.preventDefault();
		addFromTemplate($('#awaid_nearby_table tbody'), 'tmpl-awaid-nearby-row');
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

	$(document).on('click', '[data-awaid-add-unit-highlight]', function (e) {
		e.preventDefault();
		var $wrap = $(this).closest('.awaid-unit-block');
		var $tbl = $wrap.find('.awaid-unit-highlights');
		var $htb = $tbl.find('tbody');
		var unitIdx = $tbl.data('unit-index');
		if (!unitIdx) {
			var m = $wrap.find('input[name*="[code]"]').attr('name');
			unitIdx = m ? m.match(/units\]\[([^\]]+)\]/) : null;
			unitIdx = unitIdx ? unitIdx[1] : String(Date.now());
		}
		var j = nextIndex($htb);
		var row = $('#tmpl-awaid-unit-highlight-row').html();
		if (!row) {
			return;
		}
		row = row.replace(/__i__/g, unitIdx).replace(/__j__/g, j);
		$htb.append(row);
	});

	$(document).on('click', '.awaid-remove-row', function (e) {
		e.preventDefault();
		var $row = $(this).closest('tr');
		var $tb = $row.closest('tbody');
		$row.remove();
		if ($tb.find('tr').length === 0) {
			if ($tb.closest('#awaid_features_table').length) {
				addFromTemplate($tb, 'tmpl-awaid-feature-row');
			} else if ($tb.closest('#awaid_warranties_table').length) {
				addFromTemplate($tb, 'tmpl-awaid-warranty-row');
			} else if ($tb.closest('#awaid_nearby_table').length) {
				addFromTemplate($tb, 'tmpl-awaid-nearby-row');
			} else if ($tb.closest('.awaid-unit-highlights').length) {
				var $tbl = $tb.closest('.awaid-unit-highlights');
				var unitIdx = $tbl.data('unit-index');
				var j = nextIndex($tb);
				var html = $('#tmpl-awaid-unit-highlight-row').html();
				if (html) {
					$tb.append(html.replace(/__i__/g, unitIdx).replace(/__j__/g, j));
				}
			}
		}
	});

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
