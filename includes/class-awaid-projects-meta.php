<?php

if (!defined('ABSPATH')) {
	exit;
}

class Awaid_Projects_Meta {

	private const NONCE_ACTION = 'awaid_project_save';
	private const NONCE_NAME   = 'awaid_project_nonce';

	public static function init(): void {
		add_action('add_meta_boxes', [__CLASS__, 'register_boxes']);
		add_action('save_post_awaid_project', [__CLASS__, 'save'], 10, 2);
	}

	public static function register_boxes(): void {
		add_meta_box(
			'awaid_project_details',
			__('تفاصيل المشروع', 'awaid-projects'),
			[__CLASS__, 'render_box'],
			'awaid_project',
			'normal',
			'high'
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_defaults(): array {
		return [
			'location'       => '',
			'developer'      => '',
			'area_min'       => '',
			'area_max'       => '',
			'bedrooms'       => '',
			'bathrooms'      => '',
			'floors'         => '',
			'license'        => '',
			'listing_date'   => '',
			'price_min'      => '',
			'price_max'      => '',
			'brochure_id'    => 0,
			'map_url'        => '',
			'gallery_ids'    => [],
			'features'       => [],
			'warranties'     => [],
			'nearby'         => [],
			'units'          => [],
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_all(int $post_id): array {
		$defaults = self::get_defaults();
		$stored    = get_post_meta($post_id, '_awaid_project_data', true);
		if (!is_array($stored)) {
			$stored = [];
		}
		return array_merge($defaults, $stored);
	}

	public static function render_box(WP_Post $post): void {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
		$d = self::get_all((int) $post->ID);
		$catalog = Awaid_Projects_Settings::get_catalog();
		$selected_feature_ids = Awaid_Projects_Settings::extract_selected_ids('features', $d['features'] ?? []);
		$selected_warranty_ids = Awaid_Projects_Settings::extract_selected_ids('warranties', $d['warranties'] ?? []);
		$selected_nearby_ids = Awaid_Projects_Settings::extract_selected_ids('nearby', $d['nearby'] ?? []);

		$brochure_id = (int) ($d['brochure_id'] ?? 0);
		$brochure_url = $brochure_id ? wp_get_attachment_url($brochure_id) : '';

		include AWAID_PROJECTS_PATH . 'templates/admin-meta-box.php';
	}

	/**
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public static function save(int $post_id, WP_Post $post): void {
		if (!isset($_POST[self::NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$raw = isset($_POST['awaid_project']) && is_array($_POST['awaid_project'])
			? wp_unslash($_POST['awaid_project'])
			: [];

		$data = self::sanitize_input($raw);

		update_post_meta($post_id, '_awaid_project_data', $data);
	}

	/**
	 * @param array<string, mixed> $raw
	 * @return array<string, mixed>
	 */
	private static function sanitize_input(array $raw): array {
		$out = self::get_defaults();

		$out['location']     = isset($raw['location']) ? sanitize_text_field((string) $raw['location']) : '';
		$out['developer']    = isset($raw['developer']) ? sanitize_text_field((string) $raw['developer']) : '';
		$out['area_min']     = isset($raw['area_min']) ? sanitize_text_field((string) $raw['area_min']) : '';
		$out['area_max']     = isset($raw['area_max']) ? sanitize_text_field((string) $raw['area_max']) : '';
		$out['bedrooms']     = isset($raw['bedrooms']) ? sanitize_text_field((string) $raw['bedrooms']) : '';
		$out['bathrooms']    = isset($raw['bathrooms']) ? sanitize_text_field((string) $raw['bathrooms']) : '';
		$out['floors']       = isset($raw['floors']) ? sanitize_text_field((string) $raw['floors']) : '';
		$out['license']      = isset($raw['license']) ? sanitize_text_field((string) $raw['license']) : '';
		$out['listing_date'] = isset($raw['listing_date']) ? sanitize_text_field((string) $raw['listing_date']) : '';
		$out['price_min']    = isset($raw['price_min']) ? sanitize_text_field((string) $raw['price_min']) : '';
		$out['price_max']    = isset($raw['price_max']) ? sanitize_text_field((string) $raw['price_max']) : '';
		$out['map_url']      = isset($raw['map_url']) ? esc_url_raw((string) $raw['map_url']) : '';
		$out['brochure_id']  = isset($raw['brochure_id']) ? absint($raw['brochure_id']) : 0;

		$gallery_csv = isset($raw['gallery_csv']) ? (string) $raw['gallery_csv'] : '';
		$gallery_ids = array_filter(array_map('absint', preg_split('/\s*,\s*/', $gallery_csv)));
		$out['gallery_ids'] = [];
		foreach (array_unique($gallery_ids) as $gid) {
			if ($gid && wp_attachment_is_image($gid)) {
				$out['gallery_ids'][] = $gid;
			}
		}

		$features = isset($raw['features']) && is_array($raw['features']) ? $raw['features'] : [];
		$feature_ids = isset($raw['feature_ids']) && is_array($raw['feature_ids']) ? $raw['feature_ids'] : $features;
		$out['features'] = Awaid_Projects_Settings::extract_selected_ids('features', $feature_ids);

		$warranties = isset($raw['warranties']) && is_array($raw['warranties']) ? $raw['warranties'] : [];
		$warranty_ids = isset($raw['warranty_ids']) && is_array($raw['warranty_ids']) ? $raw['warranty_ids'] : $warranties;
		$out['warranties'] = Awaid_Projects_Settings::extract_selected_ids('warranties', $warranty_ids);

		$nearby = isset($raw['nearby']) && is_array($raw['nearby']) ? $raw['nearby'] : [];
		$nearby_ids = isset($raw['nearby_ids']) && is_array($raw['nearby_ids']) ? $raw['nearby_ids'] : $nearby;
		$out['nearby'] = Awaid_Projects_Settings::extract_selected_ids('nearby', $nearby_ids);

		$units = isset($raw['units']) && is_array($raw['units']) ? $raw['units'] : [];
		$out['units'] = [];
		$allowed_status = ['available', 'reserved', 'sold'];
		foreach ($units as $row) {
			$code = isset($row['code']) ? sanitize_text_field((string) $row['code']) : '';
			if ($code === '') {
				continue;
			}
			$status = isset($row['status']) ? sanitize_key((string) $row['status']) : 'available';
			if (!in_array($status, $allowed_status, true)) {
				$status = 'available';
			}

			$ug_csv = isset($row['gallery_csv']) ? (string) $row['gallery_csv'] : '';
			$ug_ids  = array_filter(array_map('absint', preg_split('/\s*,\s*/', $ug_csv)));
			$gallery_ids_unit = [];
			foreach (array_unique($ug_ids) as $ugid) {
				if ($ugid && wp_attachment_is_image($ugid)) {
					$gallery_ids_unit[] = $ugid;
				}
			}

			$highlights = Awaid_Projects_Settings::extract_selected_ids(
				'features',
				isset($row['highlights']) ? $row['highlights'] : []
			);

			$out['units'][] = [
				'code'         => $code,
				'type'         => isset($row['type']) ? sanitize_text_field((string) $row['type']) : '',
				'price'        => isset($row['price']) ? sanitize_text_field((string) $row['price']) : '',
				'area'         => isset($row['area']) ? sanitize_text_field((string) $row['area']) : '',
				'bedrooms'     => isset($row['bedrooms']) ? sanitize_text_field((string) $row['bedrooms']) : '',
				'bathrooms'    => isset($row['bathrooms']) ? sanitize_text_field((string) $row['bathrooms']) : '',
				'status'       => $status,
				'gallery_ids'  => $gallery_ids_unit,
				'description'  => isset($row['description']) ? sanitize_textarea_field((string) $row['description']) : '',
				'floor'        => isset($row['floor']) ? sanitize_text_field((string) $row['floor']) : '',
				'kitchens'     => isset($row['kitchens']) ? sanitize_text_field((string) $row['kitchens']) : '',
				'whatsapp'     => isset($row['whatsapp']) ? sanitize_text_field((string) $row['whatsapp']) : '',
				'phone'        => isset($row['phone']) ? sanitize_text_field((string) $row['phone']) : '',
				'highlights'   => $highlights,
			];
		}

		return $out;
	}
}
