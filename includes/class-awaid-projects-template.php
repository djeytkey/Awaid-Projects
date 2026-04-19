<?php

if (!defined('ABSPATH')) {
	exit;
}

class Awaid_Projects_Template {

	public static function init(): void {
		add_filter('template_include', [__CLASS__, 'single_template'], 99);
	}

	public static function single_template(string $template): string {
		if (!is_singular('awaid_project')) {
			return $template;
		}

		$plugin_template = AWAID_PROJECTS_PATH . 'templates/single-awaid_project.php';
		if (file_exists($plugin_template)) {
			return $plugin_template;
		}

		return $template;
	}
}
