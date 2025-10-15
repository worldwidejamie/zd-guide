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
			$term = get_term_by('slug', $category->id, 'zd_category');

			if (! $term) {
				$new_term = wp_insert_term(
					$category->name,
					'zd_category',
					array(
						'description' => $category->description ?? '',
						'slug'        => $category->id,
					)
				);

				if (! is_wp_error($new_term)) {
					add_term_meta($new_term['term_id'], 'zendesk_category_id', $category->id, true);
					$synced_count++;
				}
			} else {
				wp_update_term(
					$term->term_id,
					'zd_category',
					array(
						'name'        => $category->name,
						'description' => $category->description ?? '',
					)
				);
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
				$term = get_term_by('slug', $section->id, 'zd_section');

				if (! $term) {
					$new_term = wp_insert_term(
						$section->name,
						'zd_section',
						array(
							'description' => $section->description ?? '',
							'slug'        => $section->id,
							'parent'      => $category->term_id,
						)
					);

					if (! is_wp_error($new_term)) {
						add_term_meta($new_term['term_id'], 'zendesk_section_id', $section->id, true);
						$synced_count++;
					}
				} else {
					wp_update_term(
						$term->term_id,
						'zd_section',
						array(
							'name'        => $section->name,
							'description' => $section->description ?? '',
							'parent'      => $category->term_id,
						)
					);
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
}
