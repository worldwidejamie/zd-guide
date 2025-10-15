<?php

/**
 * Functions for syncing data from Zendesk Guide.
 *
 * @package Wwj_Zdguide
 */

if (! defined('WPINC')) {
	die;
}

/**
 * Sync categories from Zendesk to the zd_category taxonomy.
 */
function wwj_zdguide_sync_categories()
{
	if (! isset($_GET['wwj_zdguide_sync_categories']) || ! wp_verify_nonce($_GET['_wpnonce'], 'wwj_zdguide_sync_categories')) {
		return;
	}

	$credentials = wwj_zdguide_get_api_credentials();
	if (! $credentials) {
		add_action('admin_notices', function () {
?>
			<div class="notice notice-error is-dismissible">
				<p><?php _e('Please fill in all API settings before syncing categories.', 'wwj-zdguide'); ?></p>
			</div>
		<?php
		});
		return;
	}

	$subdomain = $credentials['subdomain'];
	$email     = $credentials['email'];
	$api_token = $credentials['api_token'];

	$url = "https://{$subdomain}.zendesk.com/api/v2/help_center/categories.json";

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
				<p><?php _e('Failed to fetch categories from Zendesk.', 'wwj-zdguide'); ?></p>
				<p><?php echo esc_html($response->get_error_message()); ?></p>
			</div>
		<?php
		});
		return;
	}

	$response_code = wp_remote_retrieve_response_code($response);
	$body          = wp_remote_retrieve_body($response);
	$data          = json_decode($body);

	if ($response_code !== 200) {
		add_action('admin_notices', function () use ($response_code, $data) {
		?>
			<div class="notice notice-error is-dismissible">
				<p><?php printf(__('Failed to fetch categories from Zendesk. Status code: %d', 'wwj-zdguide'), $response_code); ?></p>
				<?php if (! empty($data->error)) : ?>
					<p><?php echo esc_html($data->error); ?></p>
				<?php endif; ?>
			</div>
		<?php
		});
		return;
	}

	if (empty($data->categories)) {
		add_action('admin_notices', function () {
		?>
			<div class="notice notice-warning is-dismissible">
				<p><?php _e('No categories found in Zendesk.', 'wwj-zdguide'); ?></p>
			</div>
		<?php
		});
		return;
	}

	$synced_count = 0;
	foreach ($data->categories as $category) {
		$term = get_term_by('slug', $category->id, 'zd_category');
		if (! $term) {
			$new_term = wp_insert_term(
				$category->name,
				'zd_category',
				array(
					'description' => $category->description,
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
					'description' => $category->description,
				)
			);
		}
	}

	add_action('admin_notices', function () use ($synced_count) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php printf(__('Successfully synced %d new categories.', 'wwj-zdguide'), $synced_count); ?></p>
		</div>
		<?php
	});
}
add_action('admin_init', 'wwj_zdguide_sync_categories');

/**
 * Sync sections from Zendesk to the zd_section taxonomy.
 */
function wwj_zdguide_sync_sections()
{
	if (! isset($_GET['wwj_zdguide_sync_sections']) || ! wp_verify_nonce($_GET['_wpnonce'], 'wwj_zdguide_sync_sections')) {
		return;
	}

	$credentials = wwj_zdguide_get_api_credentials();
	if (! $credentials) {
		add_action('admin_notices', function () {
		?>
			<div class="notice notice-error is-dismissible">
				<p><?php _e('Please fill in all API settings before syncing sections.', 'wwj-zdguide'); ?></p>
			</div>
		<?php
		});
		return;
	}

	$categories = get_terms(array(
		'taxonomy'   => 'zd_category',
		'hide_empty' => false,
		'meta_query' => array(
			array(
				'key'     => 'zendesk_category_id',
				'compare' => 'EXISTS',
			),
		),
	));

	if (empty($categories)) {
		add_action('admin_notices', function () {
		?>
			<div class="notice notice-warning is-dismissible">
				<p><?php _e('No categories to sync sections from. Please sync categories first.', 'wwj-zdguide'); ?></p>
			</div>
		<?php
		});
		return;
	}

	$subdomain = $credentials['subdomain'];
	$email     = $credentials['email'];
	$api_token = $credentials['api_token'];
	$synced_count = 0;

	foreach ($categories as $category) {
		$zendesk_category_id = get_term_meta($category->term_id, 'zendesk_category_id', true);
		$url = "https://{$subdomain}.zendesk.com/api/v2/help_center/categories/{$zendesk_category_id}/sections.json";

		$args = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode("{$email}/token:{$api_token}"),
			),
		);

		$response = wp_remote_get($url, $args);

		if (is_wp_error($response)) {
			continue; // Or handle error more gracefully
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body);

		if (! empty($data->sections)) {
			foreach ($data->sections as $section) {
				$term = get_term_by('slug', $section->id, 'zd_section');
				if (! $term) {
					$new_term = wp_insert_term(
						$section->name,
						'zd_section',
						array(
							'description' => $section->description,
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
							'description' => $section->description,
							'parent'      => $category->term_id,
						)
					);
				}
			}
		}
	}

	add_action('admin_notices', function () use ($synced_count) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php printf(__('Successfully synced %d new sections.', 'wwj-zdguide'), $synced_count); ?></p>
		</div>
		<?php
	});
}
add_action('admin_init', 'wwj_zdguide_sync_sections');

/**
 * Sync articles from Zendesk to the zd_article post type.
 */
function wwj_zdguide_sync_articles()
{
	if (! isset($_GET['wwj_zdguide_sync_articles']) || ! wp_verify_nonce($_GET['_wpnonce'], 'wwj_zdguide_sync_articles')) {
		return;
	}

	$credentials = wwj_zdguide_get_api_credentials();
	if (! $credentials) {
		add_action('admin_notices', function () {
		?>
			<div class="notice notice-error is-dismissible">
				<p><?php _e('Please fill in all API settings before syncing articles.', 'wwj-zdguide'); ?></p>
			</div>
		<?php
		});
		return;
	}

	$sections = get_terms(array(
		'taxonomy'   => 'zd_section',
		'hide_empty' => false,
		'meta_query' => array(
			array(
				'key'     => 'zendesk_section_id',
				'compare' => 'EXISTS',
			),
		),
	));

	if (empty($sections)) {
		add_action('admin_notices', function () {
		?>
			<div class="notice notice-warning is-dismissible">
				<p><?php _e('No sections to sync articles from. Please sync sections first.', 'wwj-zdguide'); ?></p>
			</div>
		<?php
		});
		return;
	}

	$subdomain = $credentials['subdomain'];
	$email     = $credentials['email'];
	$api_token = $credentials['api_token'];
	$synced_count = 0;

	foreach ($sections as $section) {
		$zendesk_section_id = get_term_meta($section->term_id, 'zendesk_section_id', true);
		$url = "https://{$subdomain}.zendesk.com/api/v2/help_center/sections/{$zendesk_section_id}/articles.json";

		$args = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode("{$email}/token:{$api_token}"),
			),
		);

		$response = wp_remote_get($url, $args);

		if (is_wp_error($response)) {
			continue; // Or handle error more gracefully
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body);

		if (! empty($data->articles)) {
			foreach ($data->articles as $article) {
				$existing_post = get_posts(array(
					'post_type'  => 'zd_article',
					'meta_key'   => 'zendesk_article_id',
					'meta_value' => $article->id,
				));

				$post_data = array(
					'post_title'   => $article->title,
					'post_content' => $article->body,
					'post_status'  => 'publish',
					'post_type'    => 'zd_article',
					'post_name'    => $article->id,
				);

				if (! empty($existing_post)) {
					$post_data['ID'] = $existing_post[0]->ID;
					wp_update_post($post_data);
					$post_id = $existing_post[0]->ID;
				} else {
					$post_id = wp_insert_post($post_data);
					if ($post_id > 0) {
						update_post_meta($post_id, 'zendesk_article_id', $article->id);
						$synced_count++;
					}
				}

				if ($post_id > 0) {
					// Assign category and section
					$section_term = get_term($section->term_id, 'zd_section');
					if ($section_term && ! is_wp_error($section_term)) {
						$category_term_id = $section_term->parent;
						wp_set_post_terms($post_id, array($section->term_id), 'zd_section', false);
						wp_set_post_terms($post_id, array($category_term_id), 'zd_category', false);
					}
				}
			}
		}
	}

	add_action('admin_notices', function () use ($synced_count) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php printf(__('Successfully synced %d new articles.', 'wwj-zdguide'), $synced_count); ?></p>
		</div>
<?php
	});
}
add_action('admin_init', 'wwj_zdguide_sync_articles');
