<?php

register_nav_menus([
	'primary' => __('Main Menu', 'yourtheme'),
	'mobile'  => __('Mobile Menu', 'yourtheme'),
	'toplinks' => __('Top Links Menu', 'yourtheme') // <-- new one
]);

// Options
if (function_exists('acf_add_options_page')) {
	acf_add_options_page(array(
		'page_title' => 'Theme Options',
		'menu_title' => 'Theme Options',
		'menu_slug' => 'theme-options',
		'capability' => 'edit_posts',
		'parent_slug' => '',
		'position' => false,
		'icon_url' => false,
		'redirect' => false,
	));
}


