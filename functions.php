<?php

/*
	Includes
*/
include_once('inc/theme-functions.php');
include_once('inc/widgets/custom-shortcodes.php');
include_once('inc/enqueue-plugins.php');
include_once('inc/register-styles.php');
include_once('inc/custom-post-types.php');
include_once('inc/all-post-type-duplicate.php');
include_once('inc/acf_register_block_type.php');

/*
	Register custom Navigation Walker
*/
function register_navwalker()
{
	require_once get_template_directory() . '/inc/class-wp-bootstrap-navwalker.php';
}
add_action('after_setup_theme', 'register_navwalker');

/*
	Bootstrap 5 Nav walter modifier
*/
add_filter('nav_menu_link_attributes', 'bootstrap5_dropdown_fix');
function bootstrap5_dropdown_fix($atts)
{
	if (array_key_exists('data-toggle', $atts)) {
		unset($atts['data-toggle']);
		$atts['data-bs-toggle'] = 'dropdown';
	}
	return $atts;
}

function custom_resources()
{

	wp_register_style('bootstrap-css', get_template_directory_uri() . '/plugins/bootstrap/bootstrap.min.css');
	wp_register_style('style-css', get_template_directory_uri() . '/assets/css/style.css');
	// wp_register_style('stylesheet', get_stylesheet_uri());

	wp_enqueue_style('bootstrap-css');
	wp_enqueue_style('style-css');
	// wp_enqueue_style('stylesheet');

	wp_register_script('bootstrap-js', get_template_directory_uri() . '/plugins/bootstrap/bootstrap.bundle.min.js', array('jquery'), '1.1', true);
	wp_register_script('site-js', get_template_directory_uri() . '/assets/js/site.js', array('jquery'), '1.1', true);

	wp_enqueue_script('jquery');
	wp_enqueue_script('bootstrap-js');
	wp_enqueue_script('site-js');
}
add_action('wp_enqueue_scripts', 'custom_resources');

//add style.php to head 
function dynamic_images_stylesheet()
{
	$custom_css = file_get_contents(get_template_directory() . '/assets/css/custom.php');
	wp_add_inline_style('style-css', $custom_css);
}
add_action('wp_head', 'dynamic_images_stylesheet');

if (!function_exists('custom_setup')) {
	function custom_setup()
	{
		// title tag
		add_theme_support('title-tag');

		// post thumbnails
		add_theme_support('post-thumbnails');


		// register nav menus
		register_nav_menus(array(
			'primary' => __('Main Menu', 'custom'),
			'footer' => __('Footer Menu', 'custom')
		));
	}
}

add_action('after_setup_theme', 'custom_setup');

/*
Lazy-loading Images
*/
function enqueue_lazyload_scripts()
{
	wp_enqueue_script('lazysizes', get_template_directory_uri() . '/plugins/lazyload/lazysizes.min.js', [], null, true);
}

add_action('wp_enqueue_scripts', 'enqueue_lazyload_scripts');

/*
Mobile Detect library via composer
*/
require __DIR__ . '/vendor/autoload.php';
use Detection\MobileDetect;
function detect_device_type()
{
	$detect = new MobileDetect();
	// Define global variables
	global $isMobile, $isTablet;
	$isMobile = $detect->isMobile();
	$isTablet = $detect->isTablet();
}
// Call the function at the start
detect_device_type();

/**
 * Get image alt text, fallback to image title if alt is not available
 */
function get_image_alt_or_title($image)
{
	if (empty($image) || !isset($image['ID'])) {
		return '';
	}

	$alt = get_post_meta($image['ID'], '_wp_attachment_image_alt', true);

	return $alt ? $alt : $image['title'];
}

/**
 * Convert Images to Webp and Compress
 */
function convert_image_to_webp($image_url)
{
	$upload_dir = wp_upload_dir();
	$image_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image_url);
	$webp_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $image_path);
	$webp_url = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $image_url);

	// Check if WebP already exists; if not, create it
	if (!file_exists($webp_path) && file_exists($image_path)) {
		$image = imagecreatefromstring(file_get_contents($image_path));
		if ($image) {
			imagewebp($image, $webp_path, 80); // Compress and convert
			imagedestroy($image);
		}
	}

	return file_exists($webp_path) ? $webp_url : $image_url; // Return WebP if available
}

/**
 * Duplicate Page option
 */
function custom_duplicate_page_link($actions, $post)
{
	if (current_user_can('edit_posts')) {
		$actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=custom_duplicate_page&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce') . '" title="Duplicate this item">Duplicate</a>';
	}
	return $actions;
}

add_filter('page_row_actions', 'custom_duplicate_page_link', 10, 2);

function custom_duplicate_page()
{
	if (!isset($_GET['post']) || !isset($_GET['duplicate_nonce']) || !wp_verify_nonce($_GET['duplicate_nonce'], basename(__FILE__))) {
		wp_die('Security check failed');
	}

	$post_id = absint($_GET['post']);
	$post = get_post($post_id);

	if (!$post || 'page' !== get_post_type($post)) {
		wp_die('Invalid post type');
	}

	$new_post = array(
		'post_title' => $post->post_title . ' (Copy)',
		'post_content' => $post->post_content,
		'post_status' => 'draft',
		'post_type' => 'page',
		'post_author' => get_current_user_id(),
	);

	$new_post_id = wp_insert_post($new_post);

	if ($new_post_id) {
		// Copy page metadata
		$meta_data = get_post_meta($post_id);
		foreach ($meta_data as $key => $value) {
			update_post_meta($new_post_id, $key, maybe_unserialize($value[0]));
		}
	}

	wp_redirect(admin_url('edit.php?post_type=page'));
	exit;
}

add_action('admin_action_custom_duplicate_page', 'custom_duplicate_page');
/**
 * End Duplicate Page option
 */


/**
 * Lighthouse Score fetching Audit
 */
function fetch_lighthouse_scores()
{
	$api_key = 'AIzaSyBGmCoySLqoRQpypA02ECZ5kR47zfBIMfY'; // Replace with your API key
	$url = home_url(); // Fetch your site's URL
	$api_url = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url={$url}&key={$api_key}";

	$response = wp_remote_get($api_url);
	if (is_wp_error($response)) {
		return "Error fetching data";
	}

	$body = wp_remote_retrieve_body($response);
	$data = json_decode($body, true);

	if (isset($data['lighthouseResult'])) {
		$categories = $data['lighthouseResult']['categories'];
		$performance = round($categories['performance']['score'] * 100);
		$accessibility = round($categories['accessibility']['score'] * 100);
		$best_practices = round($categories['best-practices']['score'] * 100);
		$seo = round($categories['seo']['score'] * 100);

		return "
            <div style='padding:10px; border: 1px solid #ddd; background:#fff; border-radius:5px;'>
                <h3 style='margin-bottom:10px;'>Lighthouse Scores</h3>
                <ul>
                    <li><strong>Performance:</strong> {$performance}%</li>
                    <li><strong>Accessibility:</strong> {$accessibility}%</li>
                    <li><strong>Best Practices:</strong> {$best_practices}%</li>
                    <li><strong>SEO:</strong> {$seo}%</li>
                </ul>
            </div>
        ";
	} else {
		return "Lighthouse data not found.";
	}
}

function add_lighthouse_dashboard_widget()
{
	wp_add_dashboard_widget('lighthouse_dashboard_widget', 'Lighthouse Audit', function () {
		echo fetch_lighthouse_scores();
	});
}
add_action('wp_dashboard_setup', 'add_lighthouse_dashboard_widget');

