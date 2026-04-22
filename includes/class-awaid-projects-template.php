<?php

if (!defined('ABSPATH')) {
	exit;
}

class Awaid_Projects_Template {

	public static function init(): void {
		add_filter('template_include', [__CLASS__, 'single_template'], 99);
		add_filter('body_class', [__CLASS__, 'body_class_single_project']);
	}

	/**
	 * @param string[] $classes
	 * @return string[]
	 */
	public static function body_class_single_project(array $classes): array {
		if (is_singular('awaid_project')) {
			$classes[] = 'awaid-single-project-full-width';
		}
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
