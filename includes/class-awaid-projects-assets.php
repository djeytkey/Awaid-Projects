<?php

if (!defined('ABSPATH')) {
	exit;
}

class Awaid_Projects_Assets {

	public static function init(): void {
		add_action('admin_enqueue_scripts', [__CLASS__, 'admin_assets']);
		add_action('wp_enqueue_scripts', [__CLASS__, 'frontend_assets']);
	}

	public static function admin_assets(string $hook): void {
		if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
			return;
		}
		$screen = get_current_screen();
		if (!$screen || $screen->post_type !== 'awaid_project') {
			return;
		}

		wp_enqueue_style(
			'awaid-projects-admin',
			AWAID_PROJECTS_URL . 'assets/css/admin.css',
			[],
			AWAID_PROJECTS_VERSION
		);

		wp_enqueue_media();

		wp_enqueue_script(
			'awaid-projects-admin',
			AWAID_PROJECTS_URL . 'assets/js/admin.js',
			['jquery'],
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

		wp_enqueue_style(
			'awaid-projects-frontend',
			AWAID_PROJECTS_URL . 'assets/css/frontend.css',
			[],
			AWAID_PROJECTS_VERSION
		);

		wp_enqueue_script(
			'awaid-projects-frontend',
			AWAID_PROJECTS_URL . 'assets/js/frontend.js',
			[],
			AWAID_PROJECTS_VERSION,
			true
		);
	}
}
