<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @package   WWJ_ZD_Guide
 * @since     0.1.0
 */

// If uninstall not called from WordPress, then exit.
if (! defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

// Delete plugin options.
delete_option('wwj_zdguide_settings');
delete_site_option('wwj_zdguide_settings'); // For multisite.


// Get all posts of the custom post type 'zendesk_article'.
$args = array(
	'post_type'      => 'zendesk_article',
	'posts_per_page' => -1,
	'post_status'    => 'any',
	'fields'         => 'ids', // Only get post IDs to improve performance.
);

$article_posts = get_posts($args);

if (! empty($article_posts)) {
	foreach ($article_posts as $post_id) {
		// Use true to bypass trash and permanently delete.
		wp_delete_post($post_id, true);
	}
}

// Delete all terms from the custom taxonomies.
$taxonomies = array('zd_category', 'zd_section');

foreach ($taxonomies as $taxonomy) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);

	if (! is_wp_error($terms) && ! empty($terms)) {
		foreach ($terms as $term_id) {
			wp_delete_term($term_id, $taxonomy);
		}
	}
}

// Drop the custom database table.
global $wpdb;
$table_name = $wpdb->prefix . 'wwj_zdguide_map';
$wpdb->query("DROP TABLE IF EXISTS {$table_name}"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
