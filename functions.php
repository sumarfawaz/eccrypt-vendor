<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



/*
	Includes
*/
include_once('inc/theme-functions.php');
include_once('inc/widgets/custom-shortcodes.php');
include_once('inc/enqueue_plugins.php');
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



// Handle KYC Form
function handle_kyc_form()
{
	check_ajax_referer('kyc_form_nonce', 'security');

	$current_step = isset($_POST['current_step']) ? $_POST['current_step'] : null;
	$errors = [];

	// For steps except step 4, data comes as JSON string
	if ($current_step !== '4') {
		if (isset($_POST['formData'])) {
			$data = json_decode(stripslashes($_POST['formData']), true);
		} else {
			$data = [];
		}
	}

	switch ($current_step) {
		case '1':
			if (empty($data['registrant_name']))
				$errors[] = 'Registrant name is required.';
			if (empty($data['registrant_email']))
				$errors[] = 'Registrant email is required.';
			break;

		case '2':
			if (empty($data['payment_method']))
				$errors[] = 'Payment method is required.';
			break;

		case '3-bank':
			if (empty($data['bank_account_number']))
				$errors[] = 'Bank Account Number is required.';
			if (empty($data['account_holder_name']))
				$errors[] = 'Account Holder Name is required.';
			if (empty($data['bank']))
				$errors[] = 'Bank Name is required.';
			if (empty($data['bank_branch']))
				$errors[] = 'Bank Branch is required.';
			break;

		case '3-wallet':
			if (empty($data['wallet_address']))
				$errors[] = 'Wallet Address is required.';
			if (empty($data['wallet_network']))
				$errors[] = 'Wallet Network is required.';
			break;

		case '4':
			// Validate file upload for Agreement
			if (!isset($_FILES['agreement_file']) || $_FILES['agreement_file']['error'] !== UPLOAD_ERR_OK) {
				$errors[] = 'Agreement file is required and must be a valid PDF.';
			} else {
				$file = $_FILES['agreement_file'];
				$allowed_types = ['application/pdf'];
				if (!in_array($file['type'], $allowed_types)) {
					$errors[] = 'Only PDF files are allowed for the agreement.';
				}
			}
			break;

		case '5':
			$data = $_POST;  // Get raw POST data as associative array
			break;

		default:
			$errors[] = 'Unknown form step.';
	}

	if (!empty($errors)) {
		wp_send_json_error(['errors' => $errors]);
	}

	// File Uploading Logic 
	if ($current_step === '4' && isset($_FILES['agreement_file']) && $_FILES['agreement_file']['error'] === UPLOAD_ERR_OK) {
		$upload_dir = wp_upload_dir();

		// Define your custom subfolder inside uploads
		$custom_subfolder = 'vendor-kyc-files';

		// Make sure the directory exists, create if not
		$custom_dir_path = trailingslashit($upload_dir['basedir']) . $custom_subfolder;
		if (!file_exists($custom_dir_path)) {
			wp_mkdir_p($custom_dir_path);
		}

		// Sanitize the file name
		$file_name = sanitize_file_name(basename($_FILES['agreement_file']['name']));

		// Full path for saving
		$destination = trailingslashit($custom_dir_path) . $file_name;

		// Move uploaded file
		if (!move_uploaded_file($_FILES['agreement_file']['tmp_name'], $destination)) {
			wp_send_json_error(['errors' => ['Failed to upload agreement file.']]);
		}

		// URL for the uploaded file
		$file_url = trailingslashit($upload_dir['baseurl']) . $custom_subfolder . '/' . $file_name;

		wp_send_json_success([
			'message' => 'Agreement file uploaded successfully.',
			'agreement_file_url' => $file_url
		]);
	}



	// -----------------------
	// Save submission as CPT post (ONLY ON STEP 5)
	// -----------------------
	if ($current_step === '5') {
		$post_title = !empty($data['registrant_name']) ? sanitize_text_field($data['registrant_name']) : 'KYC Submission ' . current_time('mysql');
		$post_content = 'Vendor KYC submission from ' . $post_title;

		// Insert post
		$post_id = wp_insert_post([
			'post_title' => $post_title,
			'post_content' => $post_content,
			'post_status' => 'publish', // or 'pending'
			'post_type' => 'vendor_kyc',
		]);

		if (is_wp_error($post_id)) {
			wp_send_json_error(['errors' => ['Failed to save KYC submission.']]);
		}

		// Save all form data as post meta
		$exclude_keys = ['action', 'security', 'current_step', 'formData', 'vendor_token'];

		foreach ($data as $key => $value) {
			if (in_array($key, $exclude_keys))
				continue;
			$meta_key = sanitize_key($key);
			if (is_array($value)) {
				update_post_meta($post_id, $meta_key, maybe_serialize($value));
			} else {
				update_post_meta($post_id, $meta_key, sanitize_text_field($value));
			}
		}

		// Retrieve the agreement file URL
		$agreement_file_url = $data['agreement_file_url'] ?? '';

		if (empty($agreement_file_url)) {
			wp_send_json_error(['errors' => ['Agreement file URL is missing. Please re-upload.']]);
		}

		// Prepare API Payload
		$api_data = [
			'registrant_name' => sanitize_text_field($data['registrant_name'] ?? ''),
			'registrant_email' => sanitize_email($data['registrant_email'] ?? ''),
			'registrant_phone' => sanitize_text_field($data['registrant_phone'] ?? ''),
			'registrant_phone_2' => sanitize_text_field($data['registrant_phone_2'] ?? ''),
			'registrant_designation' => sanitize_text_field($data['registrant_designation'] ?? ''),
			'business_nature' => sanitize_text_field($data['business_nature'] ?? ''),
			'business_email' => sanitize_email($data['business_email'] ?? ''),
			'business_phone' => sanitize_text_field($data['business_phone'] ?? ''),
			'business_address' => sanitize_text_field($data['business_address'] ?? ''),
			'business_name' => sanitize_text_field($data['business_name'] ?? ''),
			'business_domain' => sanitize_text_field($data['business_domain'] ?? ''),
			'payment_method' => sanitize_text_field($data['payment_method'] ?? ''),
			'wallet_address' => sanitize_text_field($data['wallet_address'] ?? ''),
			'wallet_network' => sanitize_text_field($data['wallet_network'] ?? ''),
			'bank_account_number' => sanitize_text_field($data['bank_account_number'] ?? ''),
			'account_holder_name' => sanitize_text_field($data['account_holder_name'] ?? ''),
			'bank_name' => sanitize_text_field($data['bank'] ?? ''),
			'bank_branch' => sanitize_text_field($data['bank_branch'] ?? ''),
			'agreement_text' => esc_url_raw($agreement_file_url),
			'terms_accepted' => true,
			'agreed_to_tech_stack' => $data['tech_stack'] ?? []
		];

		// Normalize payment method to API format
		$payment_map = ['wallet' => 'wallet_transfer', 'bank' => 'bank_transfer'];
		if (isset($api_data['payment_method'], $payment_map[$api_data['payment_method']])) {
			$api_data['payment_method'] = $payment_map[$api_data['payment_method']];
		}

		// API Headers
		$headers = [
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
		];

		$token = sanitize_text_field($_POST['vendor_token'] ?? '');
		if (!empty($token)) {
			$headers['Authorization'] = 'Bearer ' . $token;
		}

		error_log('Vendor Token: ' . $token);
		error_log('API URL: http://192.168.8.189:8000/api/vendor/business-registration');
		error_log('API Headers: ' . print_r($headers, true));
		error_log('API Payload: ' . print_r($api_data, true));


		// API Call
		$response = wp_remote_post('http://192.168.8.189:8000/api/vendor/business-registration', [
			'headers' => $headers,
			'body' => wp_json_encode($api_data),
			'timeout' => 15,
		]);

		$code = wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);

		if (is_wp_error($response) || $code !== 200) {
			wp_send_json_error(['errors' => ['API Error: ' . $code, 'Response: ' . $body]]);
		}

		wp_send_json_success(['message' => 'KYC submission saved and sent to external API successfully!']);
	} else {
		// Just return success for earlier steps
		wp_send_json_success(['message' => 'Step ' . $current_step . ' submitted successfully!']);
	}
}










add_action('wp_ajax_submit_kyc_form', 'handle_kyc_form');
add_action('wp_ajax_nopriv_submit_kyc_form', 'handle_kyc_form');



// KYC Form Submit AJAX Enque
function enqueue_kyc_form_scripts()
{
	wp_enqueue_script('kyc-form', get_template_directory_uri() . '/assets/js/register-page.js', ['jquery'], null, true);

	wp_localize_script('kyc-form', 'kyc_ajax_object', [
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('kyc_form_nonce'),
	]);
}

add_action('wp_enqueue_scripts', 'enqueue_kyc_form_scripts');




// Register Main Menu function custom_theme_setup() {
function custom_theme_setup()
{
	register_nav_menus(array(
		'main-menu' => __('Primary Header', 'ec-crypt-theme'),
	));
}
add_action('after_setup_theme', 'custom_theme_setup');