<?php

/**
 * Admin Settings Class
 *
 * @package Wwj_Zdguide
 * @since   0.1.0
 */

namespace WwjZdguide\Admin;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Admin settings page handler.
 */
class Settings
{
	/**
	 * Option name for storing settings.
	 *
	 * @var string
	 */
	private string $option_name = 'wwj_zdguide_options';

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	private string $page_slug = 'wwj_zdguide_settings';

	/**
	 * Initialize settings.
	 */
	public function __construct()
	{
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
	}

	/**
	 * Add the admin menu page.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void
	{
		add_options_page(
			__('Zendesk Guide Settings', 'wwj-zdguide'),
			__('Zendesk Guide', 'wwj-zdguide'),
			'manage_options',
			$this->page_slug,
			array($this, 'render_settings_page')
		);
	}

	/**
	 * Register the settings.
	 *
	 * @return void
	 */
	public function register_settings(): void
	{
		register_setting(
			'wwj_zdguide_settings_group',
			$this->option_name,
			array(
				'sanitize_callback' => array($this, 'sanitize_options'),
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			'wwj_zdguide_settings_section',
			__('API Settings', 'wwj-zdguide'),
			array($this, 'render_settings_section'),
			$this->page_slug
		);

		add_settings_field(
			'wwj_zdguide_subdomain',
			__('Zendesk Subdomain', 'wwj-zdguide'),
			array($this, 'render_subdomain_field'),
			$this->page_slug,
			'wwj_zdguide_settings_section'
		);

		add_settings_field(
			'wwj_zdguide_email',
			__('Zendesk Email', 'wwj-zdguide'),
			array($this, 'render_email_field'),
			$this->page_slug,
			'wwj_zdguide_settings_section'
		);

		add_settings_field(
			'wwj_zdguide_api_token',
			__('Zendesk API Token', 'wwj-zdguide'),
			array($this, 'render_api_token_field'),
			$this->page_slug,
			'wwj_zdguide_settings_section'
		);
	}

	/**
	 * Sanitize the options.
	 *
	 * @param array $input The input from the settings form.
	 * @return array The sanitized input.
	 */
	public function sanitize_options(array $input): array
	{
		$sanitized = array();

		if (isset($input['subdomain'])) {
			$sanitized['subdomain'] = sanitize_text_field($input['subdomain']);
		}

		if (isset($input['email'])) {
			$sanitized['email'] = sanitize_email($input['email']);
		}

		if (isset($input['api_token'])) {
			$sanitized['api_token'] = sanitize_text_field($input['api_token']);
		}

		return $sanitized;
	}

	/**
	 * Render the settings section callback.
	 *
	 * @return void
	 */
	public function render_settings_section(): void
	{
		echo '<p>' . esc_html__('Enter your Zendesk API credentials below.', 'wwj-zdguide') . '</p>';
	}

	/**
	 * Render the subdomain field.
	 *
	 * @return void
	 */
	public function render_subdomain_field(): void
	{
		$options = get_option($this->option_name, array());
		$value   = $options['subdomain'] ?? '';
?>
		<input
			type="text"
			name="<?php echo esc_attr($this->option_name); ?>[subdomain]"
			value="<?php echo esc_attr($value); ?>"
			class="regular-text">
		<p class="description">
			<?php esc_html_e('Your Zendesk subdomain (e.g., "yourcompany" from yourcompany.zendesk.com)', 'wwj-zdguide'); ?>
		</p>
	<?php
	}

	/**
	 * Render the email field.
	 *
	 * @return void
	 */
	public function render_email_field(): void
	{
		$options = get_option($this->option_name, array());
		$value   = $options['email'] ?? '';
	?>
		<input
			type="email"
			name="<?php echo esc_attr($this->option_name); ?>[email]"
			value="<?php echo esc_attr($value); ?>"
			class="regular-text">
		<p class="description">
			<?php esc_html_e('Your Zendesk admin email address', 'wwj-zdguide'); ?>
		</p>
	<?php
	}

	/**
	 * Render the API token field.
	 *
	 * @return void
	 */
	public function render_api_token_field(): void
	{
		$options = get_option($this->option_name, array());
		$value   = $options['api_token'] ?? '';
	?>
		<input
			type="password"
			name="<?php echo esc_attr($this->option_name); ?>[api_token]"
			value="<?php echo esc_attr($value); ?>"
			class="regular-text">
		<p class="description">
			<?php esc_html_e('Your Zendesk API token', 'wwj-zdguide'); ?>
		</p>
	<?php
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page(): void
	{
		if (! current_user_can('manage_options')) {
			return;
		}
	?>
		<div class="wrap">
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

			<form action="options.php" method="post">
				<?php
				settings_fields('wwj_zdguide_settings_group');
				do_settings_sections($this->page_slug);
				submit_button();
				?>
			</form>

			<hr>

			<h2><?php esc_html_e('Manual Sync', 'wwj-zdguide'); ?></h2>
			<p><?php esc_html_e('Click the buttons below to manually sync data from Zendesk.', 'wwj-zdguide'); ?></p>

			<p>
				<a href="<?php echo esc_url(wp_nonce_url(admin_url('options-general.php?page=' . $this->page_slug . '&wwj_zdguide_test_connection=1'), 'wwj_zdguide_test_connection')); ?>"
					class="button button-secondary">
					<?php esc_html_e('Test Connection', 'wwj-zdguide'); ?>
				</a>

				<a href="<?php echo esc_url(wp_nonce_url(admin_url('options-general.php?page=' . $this->page_slug . '&wwj_zdguide_sync_categories=1'), 'wwj_zdguide_sync_categories')); ?>"
					class="button button-primary">
					<?php esc_html_e('Sync Categories', 'wwj-zdguide'); ?>
				</a>

				<a href="<?php echo esc_url(wp_nonce_url(admin_url('options-general.php?page=' . $this->page_slug . '&wwj_zdguide_sync_sections=1'), 'wwj_zdguide_sync_sections')); ?>"
					class="button button-primary">
					<?php esc_html_e('Sync Sections', 'wwj-zdguide'); ?>
				</a>

				<a href="<?php echo esc_url(wp_nonce_url(admin_url('options-general.php?page=' . $this->page_slug . '&wwj_zdguide_sync_articles=1'), 'wwj_zdguide_sync_articles')); ?>"
					class="button button-primary">
					<?php esc_html_e('Sync Articles', 'wwj-zdguide'); ?>
				</a>
			</p>
		</div>
<?php
	}

	/**
	 * Get API credentials.
	 *
	 * @return array|null Array of credentials or null if not complete.
	 */
	public function get_credentials(): ?array
	{
		$options = get_option($this->option_name, array());

		if (empty($options['subdomain']) || empty($options['email']) || empty($options['api_token'])) {
			return null;
		}

		return $options;
	}
}
