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
			'name'                  => _x('المشاريع', 'post type general name', 'awaid-projects'),
			'singular_name'         => _x('المشروع', 'post type singular name', 'awaid-projects'),
			'menu_name'             => _x('المشاريع', 'admin menu', 'awaid-projects'),
			'name_admin_bar'        => _x('المشروع', 'add new on admin bar', 'awaid-projects'),
			'add_new'               => _x('إضافة جديد', 'project', 'awaid-projects'),
			'add_new_item'          => __('إضافة مشروع جديد', 'awaid-projects'),
			'new_item'              => __('مشروع جديد', 'awaid-projects'),
			'edit_item'             => __('تعديل المشروع', 'awaid-projects'),
			'view_item'             => __('عرض المشروع', 'awaid-projects'),
			'all_items'             => __('جميع المشاريع', 'awaid-projects'),
			'search_items'          => __('البحث عن المشاريع', 'awaid-projects'),
			'not_found'             => __('لم يتم العثور على أي مشاريع.', 'awaid-projects'),
			'not_found_in_trash'    => __('لم يتم العثور على أي مشاريع في سلة المهملات.', 'awaid-projects'),
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
