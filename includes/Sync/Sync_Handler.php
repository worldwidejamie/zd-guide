<?php

/**
 * Sync Handler Class
 *
 * @package Wwj_Zdguide
 * @since   0.1.0
 */

namespace WwjZdguide\Sync;

use WwjZdguide\API\Zendesk_Client;
use WwjZdguide\Admin\Settings;
use WP_Error;
use WP_Term;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Handles syncing data from Zendesk.
 */
class Sync_Handler
{
	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Zendesk API client.
	 *
	 * @var Zendesk_Client|null
	 */
	private ?Zendesk_Client $client = null;

	/**
	 * Term lookup cache keyed by taxonomy + meta value.
	 *
	 * @var array<string, \WP_Term|null>
	 */
	private array $term_cache = array();

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Settings instance.
	 */
	public function __construct(Settings $settings)
	{
		$this->settings = $settings;

		add_action('admin_init', array($this, 'handle_test_connection'));
		add_action('admin_init', array($this, 'handle_sync_categories'));
		add_action('admin_init', array($this, 'handle_sync_sections'));
		add_action('admin_init', array($this, 'handle_sync_articles'));
	}

	/**
	 * Get the Zendesk API client.
	 *
	 * @return Zendesk_Client|null Client instance or null if credentials not set.
	 */
	private function get_client(): ?Zendesk_Client
	{
		if (null !== $this->client) {
			return $this->client;
		}

		$credentials = $this->settings->get_credentials();

		if (! $credentials) {
			return null;
		}

		$this->client = new Zendesk_Client(
			$credentials['subdomain'],
			$credentials['email'],
			$credentials['api_token']
		);

		return $this->client;
	}

	/**
	 * Add admin notice.
	 *
	 * @param string $message Notice message.
	 * @param string $type    Notice type (success, error, warning, info).
	 * @return void
	 */
	private function add_notice(string $message, string $type = 'success'): void
	{
		add_action(
			'admin_notices',
			function () use ($message, $type) {
?>
			<div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
				<p><?php echo wp_kses_post($message); ?></p>
			</div>
<?php
			}
		);
	}

	/**
	 * Handle test connection request.
	 *
	 * @return void
	 */
	public function handle_test_connection(): void
	{
		if (! isset($_GET['wwj_zdguide_test_connection']) || ! wp_verify_nonce($_GET['_wpnonce'], 'wwj_zdguide_test_connection')) {
			return;
		}

		$client = $this->get_client();

		if (! $client) {
			$this->add_notice(
				__('Please fill in all API settings before testing the connection.', 'wwj-zdguide'),
				'error'
			);
			return;
		}

		$result = $client->test_connection();

		if (is_wp_error($result)) {
			$this->add_notice(
				sprintf(
					/* translators: %s: error message */
					__('API connection failed: %s', 'wwj-zdguide'),
					$result->get_error_message()
				),
				'error'
			);
			return;
		}

		$this->add_notice(__('API connection successful!', 'wwj-zdguide'));
	}

	/**
	 * Handle sync categories request.
	 *
	 * @return void
	 */
	public function handle_sync_categories(): void
	{
		if (! isset($_GET['wwj_zdguide_sync_categories']) || ! wp_verify_nonce($_GET['_wpnonce'], 'wwj_zdguide_sync_categories')) {
			return;
		}

		$client = $this->get_client();

		if (! $client) {
			$this->add_notice(
				__('Please fill in all API settings before syncing categories.', 'wwj-zdguide'),
				'error'
			);
			return;
		}

		$categories = $client->get_categories();

		if (is_wp_error($categories)) {
			$this->add_notice(
				sprintf(
					/* translators: %s: error message */
					__('Failed to fetch categories: %s', 'wwj-zdguide'),
					$categories->get_error_message()
				),
				'error'
			);
			return;
		}

		if (empty($categories)) {
			$this->add_notice(
				__('No categories found in Zendesk.', 'wwj-zdguide'),
				'warning'
			);
			return;
		}

		$synced_count = 0;

		foreach ($categories as $category) {
			$term = $this->get_term_by_meta('zd_category', 'zendesk_category_id', $category->id);
			$slug = $this->generate_slug((string) $category->name, (string) $category->id);

			if (! $term) {
				$new_term = wp_insert_term(
					$category->name,
					'zd_category',
					array(
						'description' => $category->description ?? '',
						'slug'        => $slug,
					)
				);

				if (! is_wp_error($new_term)) {
					update_term_meta($new_term['term_id'], 'zendesk_category_id', $category->id);
					$this->prime_term_cache('zd_category', $category->id, get_term($new_term['term_id'], 'zd_category'));
					$synced_count++;
				}
			} else {
				$updated_args = array(
					'name'        => $category->name,
					'description' => $category->description ?? '',
				);

				$unique_slug = $slug;
				if (function_exists('wp_unique_term_slug')) {
					$unique_slug = wp_unique_term_slug($slug, $term);
				}

				if ($unique_slug !== $term->slug) {
					$updated_args['slug'] = $unique_slug;
				}

				wp_update_term($term->term_id, 'zd_category', $updated_args);
				update_term_meta($term->term_id, 'zendesk_category_id', $category->id);
				$this->prime_term_cache('zd_category', $category->id, get_term($term->term_id, 'zd_category'));
			}
		}

		$this->add_notice(
			sprintf(
				/* translators: %d: number of categories synced */
				__('Successfully synced %d new categories.', 'wwj-zdguide'),
				$synced_count
			)
		);
	}

	/**
	 * Handle sync sections request.
	 *
	 * @return void
	 */
	public function handle_sync_sections(): void
	{
		if (! isset($_GET['wwj_zdguide_sync_sections']) || ! wp_verify_nonce($_GET['_wpnonce'], 'wwj_zdguide_sync_sections')) {
			return;
		}

		$client = $this->get_client();

		if (! $client) {
			$this->add_notice(
				__('Please fill in all API settings before syncing sections.', 'wwj-zdguide'),
				'error'
			);
			return;
		}

		$categories = get_terms(
			array(
				'taxonomy'   => 'zd_category',
				'hide_empty' => false,
				'meta_query' => array(
					array(
						'key'     => 'zendesk_category_id',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		if (empty($categories)) {
			$this->add_notice(
				__('No categories to sync sections from. Please sync categories first.', 'wwj-zdguide'),
				'warning'
			);
			return;
		}

		$synced_count = 0;

		foreach ($categories as $category) {
			$zendesk_category_id = get_term_meta($category->term_id, 'zendesk_category_id', true);
			$sections            = $client->get_sections((int) $zendesk_category_id);

			if (is_wp_error($sections)) {
				continue;
			}

			foreach ($sections as $section) {
				$term = $this->get_term_by_meta('zd_section', 'zendesk_section_id', $section->id);
				$slug = $this->generate_slug((string) $section->name, (string) $section->id);

				if (! $term) {
					$new_term = wp_insert_term(
						$section->name,
						'zd_section',
						array(
							'description' => $section->description ?? '',
							'slug'        => $slug,
							'parent'      => $category->term_id,
						)
					);

					if (! is_wp_error($new_term)) {
						update_term_meta($new_term['term_id'], 'zendesk_section_id', $section->id);
						$this->prime_term_cache('zd_section', $section->id, get_term($new_term['term_id'], 'zd_section'));
						$synced_count++;
					}
				} else {
					$updated_args = array(
						'name'        => $section->name,
						'description' => $section->description ?? '',
						'parent'      => $category->term_id,
					);

					$unique_slug = $slug;
					if (function_exists('wp_unique_term_slug')) {
						$unique_slug = wp_unique_term_slug($slug, $term);
					}

					if ($unique_slug !== $term->slug) {
						$updated_args['slug'] = $unique_slug;
					}

					wp_update_term($term->term_id, 'zd_section', $updated_args);
					update_term_meta($term->term_id, 'zendesk_section_id', $section->id);
					$this->prime_term_cache('zd_section', $section->id, get_term($term->term_id, 'zd_section'));
				}
			}
		}

		$this->add_notice(
			sprintf(
				/* translators: %d: number of sections synced */
				__('Successfully synced %d new sections.', 'wwj-zdguide'),
				$synced_count
			)
		);
	}

	/**
	 * Handle sync articles request.
	 *
	 * @return void
	 */
	public function handle_sync_articles(): void
	{
		if (! isset($_GET['wwj_zdguide_sync_articles']) || ! wp_verify_nonce($_GET['_wpnonce'], 'wwj_zdguide_sync_articles')) {
			return;
		}

		$client = $this->get_client();

		if (! $client) {
			$this->add_notice(
				__('Please fill in all API settings before syncing articles.', 'wwj-zdguide'),
				'error'
			);
			return;
		}

		$sections = get_terms(
			array(
				'taxonomy'   => 'zd_section',
				'hide_empty' => false,
				'meta_query' => array(
					array(
						'key'     => 'zendesk_section_id',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		if (empty($sections)) {
			$this->add_notice(
				__('No sections to sync articles from. Please sync sections first.', 'wwj-zdguide'),
				'warning'
			);
			return;
		}

		$synced_count = 0;

		foreach ($sections as $section) {
			$zendesk_section_id = get_term_meta($section->term_id, 'zendesk_section_id', true);
			$articles           = $client->get_articles((int) $zendesk_section_id);

			if (is_wp_error($articles)) {
				continue;
			}

			foreach ($articles as $article) {
				$existing_posts = get_posts(
					array(
						'post_type'      => 'zd_article',
						'meta_key'       => 'zendesk_article_id',
						'meta_value'     => $article->id,
						'posts_per_page' => 1,
					)
				);

				$post_data = array(
					'post_title'   => $article->title,
					'post_content' => $article->body,
					'post_status'  => 'publish',
					'post_type'    => 'zd_article',
				);

				if (! empty($existing_posts)) {
					$post_data['ID'] = $existing_posts[0]->ID;
					wp_update_post($post_data);
					$post_id = $existing_posts[0]->ID;
				} else {
					$post_id = wp_insert_post($post_data);
					if ($post_id > 0) {
						$synced_count++;
					}
				}

				if ($post_id > 0) {
					update_post_meta($post_id, 'zendesk_article_id', $article->id);

					// Assign category and section.
					$section_term = get_term($section->term_id, 'zd_section');
					if ($section_term && ! is_wp_error($section_term)) {
						$category_term_id = $section_term->parent;
						wp_set_post_terms($post_id, array($section->term_id), 'zd_section', false);
						wp_set_post_terms($post_id, array($category_term_id), 'zd_category', false);
					}
				}
			}
		}

		$this->add_notice(
			sprintf(
				/* translators: %d: number of articles synced */
				__('Successfully synced %d new articles.', 'wwj-zdguide'),
				$synced_count
			)
		);
	}

	/**
	 * Retrieve a term by meta value with simple caching.
	 *
	 * @param string     $taxonomy  Taxonomy slug.
	 * @param string     $meta_key  Meta key to query.
	 * @param int|string $meta_value Meta value to match.
	 * @return WP_Term|null
	 */
	private function get_term_by_meta(string $taxonomy, string $meta_key, $meta_value): ?WP_Term
	{
		$cache_key = $this->get_term_cache_key($taxonomy, $meta_value);

		if (array_key_exists($cache_key, $this->term_cache)) {
			return $this->term_cache[$cache_key] instanceof WP_Term ? $this->term_cache[$cache_key] : null;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'number'     => 1,
				'meta_query' => array(
					array(
						'key'     => $meta_key,
						'value'   => $meta_value,
						'compare' => '=',
					),
				),
			)
		);

		if (is_wp_error($terms) || empty($terms)) {
			$this->term_cache[$cache_key] = null;
			return null;
		}

		$term = $terms[0];
		$this->term_cache[$cache_key] = $term;

		return $term;
	}

	/**
	 * Store a term in the local lookup cache.
	 *
	 * @param string        $taxonomy   Taxonomy slug.
	 * @param int|string    $meta_value Associated meta value.
	 * @param WP_Term|false $term       Term instance or false on failure.
	 * @return void
	 */
	private function prime_term_cache(string $taxonomy, $meta_value, $term): void
	{
		$cache_key = $this->get_term_cache_key($taxonomy, $meta_value);
		$this->term_cache[$cache_key] = $term instanceof WP_Term ? $term : null;
	}

	/**
	 * Build a cache key for term lookup.
	 *
	 * @param string     $taxonomy  Taxonomy slug.
	 * @param int|string $meta_value Meta value.
	 * @return string
	 */
	private function get_term_cache_key(string $taxonomy, $meta_value): string
	{
		return $taxonomy . '|' . md5((string) $meta_value);
	}

	/**
	 * Generate a human-friendly slug derived from a term name.
	 *
	 * @param string $name     Original term name.
	 * @param string $fallback Fallback string if name is empty after sanitization.
	 * @return string
	 */
	private function generate_slug(string $name, string $fallback): string
	{
		$slug = sanitize_title($name);

		if ('' === $slug && '' !== $fallback) {
			$slug = sanitize_title($fallback);
		}

		if ('' === $slug) {
			$slug = uniqid('zd-', false);
		}

		return $slug;
	}
}
