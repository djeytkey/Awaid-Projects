<?php
/**
 * Plugin Name: Awaid Projects
 * Description: Custom post type for real estate projects with a single project layout.
 * Version: 1.0.4
 * Author: Awaid
 * Text Domain: awaid-projects
 */

if (!defined('ABSPATH')) {
	exit;
}

define('AWAID_PROJECTS_VERSION', '1.0.4');
define('AWAID_PROJECTS_PATH', plugin_dir_path(__FILE__));
define('AWAID_PROJECTS_URL', plugin_dir_url(__FILE__));

require_once AWAID_PROJECTS_PATH . 'includes/template-tags.php';
require_once AWAID_PROJECTS_PATH . 'includes/class-awaid-projects-cpt.php';
require_once AWAID_PROJECTS_PATH . 'includes/class-awaid-projects-meta.php';
require_once AWAID_PROJECTS_PATH . 'includes/class-awaid-projects-template.php';
require_once AWAID_PROJECTS_PATH . 'includes/class-awaid-projects-assets.php';

/**
 * Plugin bootstrap.
 */
function awaid_projects_boot(): void {
	Awaid_Projects_CPT::init();
	Awaid_Projects_Meta::init();
	Awaid_Projects_Template::init();
	Awaid_Projects_Assets::init();
}
add_action('plugins_loaded', 'awaid_projects_boot');

/**
 * Flush rewrite rules on activation.
 */
function awaid_projects_activate(): void {
	Awaid_Projects_CPT::register_post_type();
	flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'awaid_projects_activate');

/**
 * Flush rewrite rules on deactivation (cleanup).
 */
function awaid_projects_deactivate(): void {
	flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'awaid_projects_deactivate');
