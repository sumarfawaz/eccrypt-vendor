<?php

/**
 * Register Components Styles css
 */
function register_component_styles()
{
	$components = ['header', 'footer', 'home-banner', 'inner-banner'];

	foreach ($components as $component) {
		$css_file = get_template_directory() . "/assets/css/components/{$component}.css";

		if (file_exists($css_file)) {
			wp_register_style($component . '-css', get_template_directory_uri() . "/assets/css/components/{$component}.css");
		}
	}
}
add_action('wp_enqueue_scripts', 'register_component_styles', 5);

/**
 * Register Pages Styles css
 */
function register_pages_styles()
{
	$pages = ['front-page', 'contact-us','registration-page'];

	foreach ($pages as $page) {
		$css_file = get_template_directory() . "/assets/css/pages/{$page}.css";

		if (file_exists($css_file)) {
			wp_register_style($page . '-css', get_template_directory_uri() . "/assets/css/pages/{$page}.css");
		}
	}
}
add_action('wp_enqueue_scripts', 'register_pages_styles', 5);
