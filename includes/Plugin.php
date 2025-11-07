<?php

/**
 * Main Plugin Class
 *
 * @package Wwj_Zdguide
 * @since   0.1.0
 */

namespace WwjZdguide;

use WwjZdguide\Admin\Settings;
use WwjZdguide\API\Help_Center_Search_Controller;
use WwjZdguide\PostTypes\Article;
use WwjZdguide\Taxonomies\Category;
use WwjZdguide\Taxonomies\Section;
use WwjZdguide\Sync\Sync_Handler;
use WwjZdguide\Templates\Template_Loader;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Main plugin class.
 */
final class Plugin
{
	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private string $version = '0.1.0';

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Article post type instance.
	 *
	 * @var Article
	 */
	private Article $article_post_type;

	/**
	 * Category taxonomy instance.
	 *
	 * @var Category
	 */
	private Category $category_taxonomy;

	/**
	 * Section taxonomy instance.
	 *
	 * @var Section
	 */
	private Section $section_taxonomy;

	/**
	 * Sync handler instance.
	 *
	 * @var Sync_Handler
	 */
	private Sync_Handler $sync_handler;

	/**
	 * Template loader instance.
	 *
	 * @var Template_Loader
	 */
	private Template_Loader $template_loader;

	/**
	 * Help Center search REST controller.
	 *
	 * @var Help_Center_Search_Controller
	 */
	private Help_Center_Search_Controller $search_controller;

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct()
	{
		$this->load_dependencies();
		$this->init_hooks();
		$this->init_components();
	}

	/**
	 * Load required dependencies.
	 *
	 * @return void
	 */
	private function load_dependencies(): void
	{
		require_once WWJ_ZDGUIDE_PLUGIN_DIR . 'includes/Admin/Settings.php';
		require_once WWJ_ZDGUIDE_PLUGIN_DIR . 'includes/PostTypes/Article.php';
		require_once WWJ_ZDGUIDE_PLUGIN_DIR . 'includes/Taxonomies/Base_Taxonomy.php';
		require_once WWJ_ZDGUIDE_PLUGIN_DIR . 'includes/API/Help_Center_Search_Controller.php';
		require_once WWJ_ZDGUIDE_PLUGIN_DIR . 'includes/API/Zendesk_Client.php';
		require_once WWJ_ZDGUIDE_PLUGIN_DIR . 'includes/Sync/Mapping_Repository.php';
		require_once WWJ_ZDGUIDE_PLUGIN_DIR . 'includes/Sync/Sync_Handler.php';
		require_once WWJ_ZDGUIDE_PLUGIN_DIR . 'includes/Templates/Template_Loader.php';
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @return void
	 */
	private function init_hooks(): void
	{
		add_action('init', array($this, 'register_blocks'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
		add_action('admin_menu', array($this, 'add_uninstall_page'));
		add_filter('plugin_action_links_' . plugin_basename(WWJ_ZDGUIDE_PLUGIN_DIR . 'zd-guide.php'), array($this, 'filter_action_links'));
	}

	/**
	 * Initialize plugin components.
	 *
	 * @return void
	 */
	private function init_components(): void
	{
		$this->settings          = new Settings();
		$this->article_post_type = new Article();
		$this->category_taxonomy = new Category();
		$this->section_taxonomy  = new Section();
		$this->sync_handler      = new Sync_Handler($this->settings);
		$this->template_loader   = new Template_Loader();
		$this->search_controller = new Help_Center_Search_Controller();
	}

	/**
	 * Register blocks.
	 *
	 * @return void
	 */
	public function register_blocks(): void
	{
		if (! function_exists('register_block_type')) {
			return;
		}

		register_block_type(WWJ_ZDGUIDE_PLUGIN_DIR . 'build/blocks/article');
		register_block_type(WWJ_ZDGUIDE_PLUGIN_DIR . 'build/blocks/taxonomy');
		register_block_type(WWJ_ZDGUIDE_PLUGIN_DIR . 'build/blocks/help-center-search');
	}

	/**
	 * Enqueue admin-only assets.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets(string $hook_suffix): void
	{
		unset($hook_suffix); // Parameter kept for parity with WordPress callback signature.

		if (! function_exists('get_current_screen')) {
			return;
		}

		$screen = get_current_screen();
		if (! $screen || empty($screen->taxonomy)) {
			return;
		}

		$target_taxonomies = array('zd_category', 'zd_section');
		if (! in_array($screen->taxonomy, $target_taxonomies, true)) {
			return;
		}

		wp_enqueue_style(
			'zd-guide-admin',
			WWJ_ZDGUIDE_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			$this->version
		);
	}

	/**
	 * Adds a hidden admin page for the uninstall confirmation screen.
	 *
	 * @return void
	 */
	public function add_uninstall_page(): void
	{
		add_submenu_page(
			null, // Don't show in the menu.
			__('Uninstall Zendesk Guide Plugin', 'zd-guide'),
			__('Uninstall', 'zd-guide'),
			'delete_plugins',
			'zd-guide-uninstall',
			array($this, 'render_uninstall_page')
		);
	}

	/**
	 * Renders the custom uninstall confirmation page.
	 *
	 * @return void
	 */
	public function render_uninstall_page(): void
	{
?>
		<div class="wrap">
			<h1><?php echo esc_html__('Uninstall Zendesk Guide Plugin', 'zd-guide'); ?></h1>
			<div class="notice notice-error">
				<p>
					<strong><?php esc_html_e('Warning: This is a destructive action.', 'zd-guide'); ?></strong>
				</p>
				<p>
					<?php
					echo wp_kses_post(
						__(
							'Deleting this plugin will <strong>permanently remove all imported Zendesk articles, categories, and sections</strong> from your WordPress database. This data cannot be recovered unless you have a backup or re-sync from your Zendesk account.',
							'zd-guide'
						)
					);
					?>
				</p>
				<p>
					<?php esc_html_e('Please ensure you have a complete backup of your site or still have access to your original Zendesk Guide content before proceeding.', 'zd-guide'); ?>
				</p>
			</div>

			<p><?php esc_html_e('Are you sure you want to delete the WWJ ZD Guide plugin and all its data?', 'zd-guide'); ?></p>

			<a href="<?php echo esc_url(wp_nonce_url(admin_url('plugins.php?action=delete-selected&checked[]=' . plugin_basename(WWJ_ZDGUIDE_PLUGIN_DIR . 'zd-guide.php')), 'bulk-plugins')); ?>" class="button button-primary">
				<?php esc_html_e('Yes, Delete Plugin and Data', 'zd-guide'); ?>
			</a>
			<a href="<?php echo esc_url(admin_url('plugins.php')); ?>" class="button">
				<?php esc_html_e('No, Cancel and Return to Plugins', 'zd-guide'); ?>
			</a>
		</div>
<?php
	}

	/**
	 * Replaces the 'Delete' action link with a link to our custom confirmation page.
	 *
	 * @param array $links The existing action links.
	 * @return array The modified action links.
	 */
	public function filter_action_links(array $links): array
	{
		if (isset($links['delete'])) {
			$uninstall_url = admin_url('admin.php?page=zd-guide-uninstall');
			$links['delete'] = sprintf('<a href="%s" class="delete">%s</a>', esc_url($uninstall_url), __('Delete', 'zd-guide'));
		}

		return $links;
	}

	/**
	 * Plugin activation hook.
	 *
	 * @return void
	 */
	public static function activate(): void
	{
		// Register post types and taxonomies.
		$article  = new Article();
		$category = new Category();
		$section  = new Section();

		$article->register();
		$category->register();
		$section->register();

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation hook.
	 *
	 * @return void
	 */
	public static function deactivate(): void
	{
		flush_rewrite_rules();
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function get_version(): string
	{
		return $this->version;
	}
}
