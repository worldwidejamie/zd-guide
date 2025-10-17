<?php

/**
 * Main Plugin Class
 *
 * @package Wwj_Zdguide
 * @since   0.1.0
 */

namespace WwjZdguide;

use WwjZdguide\Admin\Settings;
use WwjZdguide\PostTypes\Article;
use WwjZdguide\Taxonomies\Category;
use WwjZdguide\Taxonomies\Section;
use WwjZdguide\Sync\Sync_Handler;

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
		require_once WWJ_ZDGUIDE_PLUGIN_DIR . 'includes/API/Zendesk_Client.php';
		require_once WWJ_ZDGUIDE_PLUGIN_DIR . 'includes/Sync/Sync_Handler.php';
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @return void
	 */
	private function init_hooks(): void
	{
		add_action('init', array($this, 'load_textdomain'));
		add_action('init', array($this, 'register_blocks'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
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
	}

	/**
	 * Load plugin text domain.
	 *
	 * @return void
	 */
	public function load_textdomain(): void
	{
		load_plugin_textdomain(
			'wwj-zdguide',
			false,
			dirname(plugin_basename(WWJ_ZDGUIDE_PLUGIN_DIR)) . '/languages/'
		);
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
			'wwj-zdguide-admin',
			WWJ_ZDGUIDE_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			$this->version
		);
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
