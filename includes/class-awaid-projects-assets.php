<?php

if (!defined('ABSPATH')) {
	exit;
}

class Awaid_Projects_Assets {

	public static function init(): void {
		add_action('admin_enqueue_scripts', [__CLASS__, 'admin_assets']);
		// Late so overrides load after theme + Elementor frontend.
		add_action('wp_enqueue_scripts', [__CLASS__, 'frontend_assets'], 100);
	}

	public static function admin_assets(string $hook): void {
		$screen = get_current_screen();
		if (!$screen) {
			return;
		}

		$is_project_editor = in_array($hook, ['post.php', 'post-new.php'], true) && $screen->post_type === 'awaid_project';
		$is_settings_page  = $screen->id === 'awaid_project_page_' . Awaid_Projects_Settings::get_settings_slug();
		if (!$is_project_editor && !$is_settings_page) {
			return;
		}

		wp_enqueue_style(
			'awaid-projects-admin',
			AWAID_PROJECTS_URL . 'assets/css/admin.css',
			[],
			AWAID_PROJECTS_VERSION
		);

		wp_enqueue_script(
			'awaid-projects-lucide',
			'https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js',
			[],
			AWAID_PROJECTS_VERSION,
			true
		);

		if ($is_project_editor) {
			wp_enqueue_media();
		}

		wp_enqueue_script(
			'awaid-projects-admin',
			AWAID_PROJECTS_URL . 'assets/js/admin.js',
			['jquery', 'awaid-projects-lucide'],
			AWAID_PROJECTS_VERSION,
			true
		);

		wp_localize_script(
			'awaid-projects-admin',
			'awaidProjectsAdmin',
			[
				'i18n' => [
					'selectFile'   => __('Choose brochure file', 'awaid-projects'),
					'useFile'      => __('Use this file', 'awaid-projects'),
					'noFile'       => __('No file selected.', 'awaid-projects'),
					'selectGallery'=> __('Choose gallery images', 'awaid-projects'),
					'useImages'    => __('Add to gallery', 'awaid-projects'),
					'removeImage'  => __('Remove', 'awaid-projects'),
				],
			]
		);
	}

	public static function frontend_assets(): void {
		if (!is_singular('awaid_project')) {
			return;
		}

		$style_deps = [];
		if (wp_style_is('rehomes-style', 'registered')) {
			$style_deps[] = 'rehomes-style';
		}
		if (wp_style_is('elementor-frontend', 'registered')) {
			$style_deps[] = 'elementor-frontend';
		}

		wp_register_style(
			'swiper',
			'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
			[],
			'11.1.15'
		);
		wp_register_style(
			'leaflet',
			'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css',
			[],
			'1.9.4'
		);
		wp_register_script(
			'swiper',
			'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
			[],
			'11.1.15',
			true
		);
		wp_register_script(
			'leaflet',
			'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js',
			[],
			'1.9.4',
			true
		);

		wp_enqueue_style('swiper');
		wp_enqueue_style('leaflet');
		wp_enqueue_script(
			'awaid-projects-lucide',
			'https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js',
			[],
			AWAID_PROJECTS_VERSION,
			true
		);
		wp_enqueue_style(
			'awaid-projects-frontend',
			AWAID_PROJECTS_URL . 'assets/css/frontend.css',
			array_merge(['swiper', 'leaflet'], $style_deps),
			AWAID_PROJECTS_VERSION
		);

		wp_enqueue_script(
			'awaid-projects-frontend',
			AWAID_PROJECTS_URL . 'assets/js/frontend.js',
			['swiper', 'leaflet', 'awaid-projects-lucide'],
			AWAID_PROJECTS_VERSION,
			true
		);
	}
}
