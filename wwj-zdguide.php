<?php

/**
 * Plugin Name:       WWJ Zendesk Guide
 * Plugin URI:        https://example.com/
 * Description:       Connects to the Zendesk Guide API to build a help center in WordPress.
 * Version:           0.1.0
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * Author:            Jamie Smith
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wwj-zdguide
 * Domain Path:       /languages
 *
 * @package         Wwj_Zdguide
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
	die;
}

/**
 * Define plugin constants.
 */
define('WWJ_ZDGUIDE_VERSION', '0.1.0');
define('WWJ_ZDGUIDE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WWJ_ZDGUIDE_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Add the admin menu page.
 */
function wwj_zdguide_add_admin_menu()
{
	add_options_page(
		__('Zendesk Guide Settings', 'wwj-zdguide'),
		__('Zendesk Guide', 'wwj-zdguide'),
		'manage_options',
		'wwj_zdguide_settings',
		'wwj_zdguide_settings_page'
	);
}
add_action('admin_menu', 'wwj_zdguide_add_admin_menu');

/**
 * Register the settings.
 */
function wwj_zdguide_register_settings()
{
	register_setting(
		'wwj_zdguide_settings_group',
		'wwj_zdguide_options',
		'wwj_zdguide_sanitize_options'
	);

	add_settings_section(
		'wwj_zdguide_settings_section',
		__('API Settings', 'wwj-zdguide'),
		'wwj_zdguide_settings_section_callback',
		'wwj_zdguide_settings'
	);

	add_settings_field(
		'wwj_zdguide_subdomain',
		__('Zendesk Subdomain', 'wwj-zdguide'),
		'wwj_zdguide_subdomain_render',
		'wwj_zdguide_settings',
		'wwj_zdguide_settings_section'
	);

	add_settings_field(
		'wwj_zdguide_email',
		__('Zendesk Email', 'wwj-zdguide'),
		'wwj_zdguide_email_render',
		'wwj_zdguide_settings',
		'wwj_zdguide_settings_section'
	);

	add_settings_field(
		'wwj_zdguide_api_token',
		__('Zendesk API Token', 'wwj-zdguide'),
		'wwj_zdguide_api_token_render',
		'wwj_zdguide_settings',
		'wwj_zdguide_settings_section'
	);
}
add_action('admin_init', 'wwj_zdguide_register_settings');

/**
 * Sanitize the options.
 *
 * @param array $input The input from the settings form.
 * @return array The sanitized input.
 */
function wwj_zdguide_sanitize_options($input)
{
	$new_input = array();
	if (isset($input['subdomain'])) {
		$new_input['subdomain'] = sanitize_text_field($input['subdomain']);
	}
	if (isset($input['email'])) {
		$new_input['email'] = sanitize_email($input['email']);
	}
	if (isset($input['api_token'])) {
		$new_input['api_token'] = sanitize_text_field($input['api_token']);
	}
	return $new_input;
}

/**
 * Render the settings section callback.
 */
function wwj_zdguide_settings_section_callback()
{
	echo __('Enter your Zendesk API credentials below.', 'wwj-zdguide');
}

/**
 * Render the subdomain field.
 */
function wwj_zdguide_subdomain_render()
{
	$options = get_option('wwj_zdguide_options');
?>
	<input type='text' name='wwj_zdguide_options[subdomain]' value='<?php echo esc_attr($options['subdomain'] ?? ''); ?>'>
<?php
}

/**
 * Render the email field.
 */
function wwj_zdguide_email_render()
{
	$options = get_option('wwj_zdguide_options');
?>
	<input type='email' name='wwj_zdguide_options[email]' value='<?php echo esc_attr($options['email'] ?? ''); ?>'>
<?php
}

/**
 * Render the API token field.
 */
function wwj_zdguide_api_token_render()
{
	$options = get_option('wwj_zdguide_options');
?>
	<input type='password' name='wwj_zdguide_options[api_token]' value='<?php echo esc_attr($options['api_token'] ?? ''); ?>'>
<?php
}

/**
 * Render the settings page.
 */
function wwj_zdguide_settings_page()
{
?>
	<form action='options.php' method='post'>
		<h2><?php echo __('Zendesk Guide Settings', 'wwj-zdguide'); ?></h2>
		<?php
		settings_fields('wwj_zdguide_settings_group');
		do_settings_sections('wwj_zdguide_settings');
		submit_button();
		?>
	</form>
	<hr>
	<h2><?php echo __('Manual Sync', 'wwj-zdguide'); ?></h2>
	<p><?php echo __('Click the buttons below to manually sync data from Zendesk.', 'wwj-zdguide'); ?></p>
	<a href="<?php echo esc_url(wp_nonce_url(admin_url('options-general.php?page=wwj_zdguide_settings&wwj_zdguide_test_connection=1'), 'wwj_zdguide_test_connection')); ?>" class="button button-secondary">
		<?php echo __('Test Connection', 'wwj-zdguide'); ?>
	</a>
	<a href="<?php echo esc_url(wp_nonce_url(admin_url('options-general.php?page=wwj_zdguide_settings&wwj_zdguide_sync_categories=1'), 'wwj_zdguide_sync_categories')); ?>" class="button button-primary">
		<?php echo __('Sync Categories', 'wwj-zdguide'); ?>
	</a>
	<a href="<?php echo esc_url(wp_nonce_url(admin_url('options-general.php?page=wwj_zdguide_settings&wwj_zdguide_sync_sections=1'), 'wwj_zdguide_sync_sections')); ?>" class="button button-primary">
		<?php echo __('Sync Sections', 'wwj-zdguide'); ?>
	</a>
	<a href="<?php echo esc_url(wp_nonce_url(admin_url('options-general.php?page=wwj_zdguide_settings&wwj_zdguide_sync_articles=1'), 'wwj_zdguide_sync_articles')); ?>" class="button button-primary">
		<?php echo __('Sync Articles', 'wwj-zdguide'); ?>
	</a>
	<?php
}

/**
 * Get the API credentials.
 *
 * @return array|false The API credentials or false if not set.
 */
function wwj_zdguide_get_api_credentials()
{
	$options = get_option('wwj_zdguide_options');
	if (empty($options['subdomain']) || empty($options['email']) || empty($options['api_token'])) {
		return false;
	}
	return $options;
}

/**
 * Test the API connection.
 */
function wwj_zdguide_test_api_connection()
{
	if (! isset($_GET['wwj_zdguide_test_connection']) || ! wp_verify_nonce($_GET['_wpnonce'], 'wwj_zdguide_test_connection')) {
		return;
	}

	$credentials = wwj_zdguide_get_api_credentials();
	if (! $credentials) {
		add_action('admin_notices', function () {
	?>
			<div class="notice notice-error is-dismissible">
				<p><?php _e('Please fill in all API settings before testing the connection.', 'wwj-zdguide'); ?></p>
			</div>
		<?php
		});
		return;
	}

	$subdomain = $credentials['subdomain'];
	$email     = $credentials['email'];
	$api_token = $credentials['api_token'];

	$url = "https://{$subdomain}.zendesk.com/api/v2/help_center/articles";

	$args = array(
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode("{$email}/token:{$api_token}"),
		),
	);

	$response = wp_remote_get($url, $args);
	if (is_wp_error($response)) {
		add_action('admin_notices', function () use ($response) {
		?>
			<div class="notice notice-error is-dismissible">
				<p><?php _e('API connection failed.', 'wwj-zdguide'); ?></p>
				<p><?php echo esc_html($response->get_error_message()); ?></p>
			</div>
		<?php
		});
		return;
	}

	$response_code = wp_remote_retrieve_response_code($response);

	if ($response_code === 200) {
		add_action('admin_notices', function () {
		?>
			<div class="notice notice-success is-dismissible">
				<p><?php _e('API connection successful!', 'wwj-zdguide'); ?></p>
			</div>
		<?php
		});
	} else {
		add_action('admin_notices', function () use ($response_code, $response) {
			$body = wp_remote_retrieve_body($response);
			$data = json_decode($body);
		?>
			<div class="notice notice-error is-dismissible">
				<p><?php printf(__('API connection failed with status code: %d', 'wwj-zdguide'), $response_code); ?></p>
				<?php if (! empty($data->error)) : ?>
					<p><?php echo esc_html(is_string($data->error) ? $data->error : ($data->error->title ?? 'Unknown error')); ?></p>
				<?php endif; ?>
			</div>
<?php
		});
	}
}
add_action('admin_init', 'wwj_zdguide_test_api_connection');

require_once WWJ_ZDGUIDE_PLUGIN_DIR . 'post-types.php';
require_once WWJ_ZDGUIDE_PLUGIN_DIR . 'sync.php';

/**
 * Flush rewrite rules on activation.
 */
function wwj_zdguide_activate()
{
	// Register post types and taxonomies
	wwj_zdguide_register_post_type();
	wwj_zdguide_register_taxonomies();

	// Flush rewrite rules
	flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wwj_zdguide_activate');
