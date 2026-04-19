<?php

if (!defined('ABSPATH')) {
	exit;
}

class Awaid_Projects_CPT {

	public static function init(): void {
		add_action('init', [__CLASS__, 'register_post_type']);
	}

	public static function register_post_type(): void {
		$labels = [
			'name'                  => _x('Projects', 'post type general name', 'awaid-projects'),
			'singular_name'         => _x('Project', 'post type singular name', 'awaid-projects'),
			'menu_name'             => _x('Projects', 'admin menu', 'awaid-projects'),
			'name_admin_bar'        => _x('Project', 'add new on admin bar', 'awaid-projects'),
			'add_new'               => _x('Add New', 'project', 'awaid-projects'),
			'add_new_item'          => __('Add New Project', 'awaid-projects'),
			'new_item'              => __('New Project', 'awaid-projects'),
			'edit_item'             => __('Edit Project', 'awaid-projects'),
			'view_item'             => __('View Project', 'awaid-projects'),
			'all_items'             => __('All Projects', 'awaid-projects'),
			'search_items'          => __('Search Projects', 'awaid-projects'),
			'not_found'             => __('No projects found.', 'awaid-projects'),
			'not_found_in_trash'    => __('No projects found in Trash.', 'awaid-projects'),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => ['slug' => 'project'],
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-building',
			'supports'           => ['title', 'editor', 'thumbnail', 'excerpt'],
			'show_in_rest'       => true,
		];

		register_post_type('awaid_project', $args);
	}
}
