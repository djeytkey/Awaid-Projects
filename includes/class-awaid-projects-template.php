<?php

if (!defined('ABSPATH')) {
	exit;
}

class Awaid_Projects_Template {

	public static function init(): void {
		add_filter('template_include', [__CLASS__, 'single_template'], 99);
		// After theme/core body_class (e.g. opal-header-absolute), adjust single project chrome.
		add_filter('body_class', [__CLASS__, 'body_class_single_project'], 99);
	}

	/**
	 * @param string[] $classes
	 * @return string[]
	 */
	public static function body_class_single_project(array $classes): array {
		if (!is_singular('awaid_project')) {
			return $classes;
		}

		$classes[] = 'awaid-single-project-full-width';

		// Rehomes header builder: solid bar + in-flow header instead of transparent overlay.
		$classes = array_values(array_diff($classes, ['opal-header-absolute']));

		return $classes;
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
