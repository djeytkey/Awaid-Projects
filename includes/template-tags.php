<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Human label for a unit status.
 */
function awaid_project_status_label(string $status): string {
	$labels = [
		'available' => __('متاح', 'awaid-projects'),
		'reserved'  => __('محجوز', 'awaid-projects'),
		'sold'      => __('مباع', 'awaid-projects'),
	];
	return $labels[ $status ] ?? $status;
}

/**
 * Formatted area range from meta parts.
 */
function awaid_project_area_range_display(array $d): string {
	$min = isset($d['area_min']) ? trim((string) $d['area_min']) : '';
	$max = isset($d['area_max']) ? trim((string) $d['area_max']) : '';
	if ($min !== '' && $max !== '') {
		/* translators: 1: min area, 2: max area */
		return sprintf(__('%1$s – %2$s م²', 'awaid-projects'), $min, $max);
	}
	if ($min !== '') {
		return sprintf(__('%s م²', 'awaid-projects'), $min);
	}
	if ($max !== '') {
		return sprintf(__('%s م²', 'awaid-projects'), $max);
	}
	return '';
}

/**
 * Formatted price range.
 */
function awaid_project_price_range_display(array $d): string {
	$min = isset($d['price_min']) ? trim((string) $d['price_min']) : '';
	$max = isset($d['price_max']) ? trim((string) $d['price_max']) : '';
	if ($min !== '' && $max !== '') {
		/* translators: 1: min price, 2: max price */
		return sprintf(__('من %1$s إلى %2$s', 'awaid-projects'), $min, $max);
	}
	if ($min !== '') {
		return $min;
	}
	if ($max !== '') {
		return $max;
	}
	return '';
}
