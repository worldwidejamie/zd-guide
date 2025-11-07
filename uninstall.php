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
$wwj_zdguide_args = array(
	'post_type'      => 'zendesk_article',
	'posts_per_page' => -1,
	'post_status'    => 'any',
	'fields'         => 'ids', // Only get post IDs to improve performance.
);

$wwj_zdguide_article_posts = get_posts($wwj_zdguide_args);

if (! empty($wwj_zdguide_article_posts)) {
	foreach ($wwj_zdguide_article_posts as $wwj_zdguide_post_id) {
		// Use true to bypass trash and permanently delete.
		wp_delete_post($wwj_zdguide_post_id, true);
	}
}

// Delete all terms from the custom taxonomies.
$wwj_zdguide_taxonomies = array('zd_category', 'zd_section');

foreach ($wwj_zdguide_taxonomies as $wwj_zdguide_taxonomy) {
	$wwj_zdguide_terms = get_terms(
		array(
			'taxonomy'   => $wwj_zdguide_taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);

	if (! is_wp_error($wwj_zdguide_terms) && ! empty($wwj_zdguide_terms)) {
		foreach ($wwj_zdguide_terms as $wwj_zdguide_term_id) {
			wp_delete_term($wwj_zdguide_term_id, $wwj_zdguide_taxonomy);
		}
	}
}

// Drop the custom database table.
global $wpdb;
$wwj_zdguide_table_name = $wpdb->prefix . 'wwj_zdguide_map';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query($wpdb->prepare('DROP TABLE IF EXISTS %i', $wwj_zdguide_table_name));
