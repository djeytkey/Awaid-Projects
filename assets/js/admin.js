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

	$(document).on('click', '[data-awaid-add-unit]', function (e) {
		e.preventDefault();
		addFromTemplate($('#awaid_units_table tbody'), 'tmpl-awaid-unit-row');
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
			} else if ($tb.closest('#awaid_units_table').length) {
				addFromTemplate($tb, 'tmpl-awaid-unit-row');
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
})(jQuery);
