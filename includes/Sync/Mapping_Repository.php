<?php

/**
 * Zendesk to WordPress mapping repository.
 *
 * @package Wwj_Zdguide
 */

namespace WwjZdguide\Sync;

use wpdb;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Handles persistence of Zendesk → WordPress object mappings.
 */
class Mapping_Repository
{
	/**
	 * Mapping table name.
	 */
	private string $table_name;

	/**
	 * WordPress database connection.
	 */
	private wpdb $wpdb;

	/**
	 * Constructor.
	 */
	public function __construct(?wpdb $wpdb = null)
	{
		$this->wpdb       = $wpdb ?: $GLOBALS['wpdb'];
		$this->table_name = $this->wpdb->prefix . 'wwj_zdguide_map';

		if (! $this->table_exists()) {
			self::create_table($this->wpdb);
		}
	}

	/**
	 * Create the mapping table.
	 */
	public static function create_table(?wpdb $wpdb = null): void
	{
		$wpdb       = $wpdb ?: $GLOBALS['wpdb'];
		$table_name = $wpdb->prefix . 'wwj_zdguide_map';
		$collate    = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "CREATE TABLE {$table_name} (
			map_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			object_type VARCHAR(20) NOT NULL,
			taxonomy VARCHAR(50) NOT NULL DEFAULT '',
			object_id BIGINT(20) UNSIGNED NOT NULL,
			zendesk_id BIGINT(20) UNSIGNED NOT NULL,
			PRIMARY KEY  (map_id),
			UNIQUE KEY object_lookup (object_type, object_id),
			UNIQUE KEY zendesk_lookup (object_type, taxonomy, zendesk_id),
			KEY taxonomy_lookup (taxonomy, object_type)
		) {$collate};";

		dbDelta($sql);
	}

	/**
	 * Determine whether the mapping table exists.
	 */
	private function table_exists(): bool
	{
		$table = $this->table_name;
		$like  = $this->wpdb->esc_like($table);
		$results = $this->wpdb->get_var($this->wpdb->prepare('SHOW TABLES LIKE %s', $like));

		return $results === $table;
	}

	/**
	 * Store or update a term mapping.
	 */
	public function store_term_mapping(string $taxonomy, int $term_id, int $zendesk_id): void
	{
		$this->wpdb->replace(
			$this->table_name,
			array(
				'object_type' => 'term',
				'taxonomy'    => $taxonomy,
				'object_id'   => $term_id,
				'zendesk_id'  => $zendesk_id,
			),
			array('%s', '%s', '%d', '%d')
		);
	}

	/**
	 * Fetch a term ID by Zendesk identifier.
	 */
	public function get_term_id(string $taxonomy, int $zendesk_id): ?int
	{
		$sql = $this->wpdb->prepare(
			"SELECT object_id FROM {$this->table_name} WHERE object_type = %s AND taxonomy = %s AND zendesk_id = %d LIMIT 1",
			'term',
			$taxonomy,
			$zendesk_id
		);

		$term_id = $this->wpdb->get_var($sql);

		return $term_id ? (int) $term_id : null;
	}

	/**
	 * Fetch all term IDs for a given taxonomy with a Zendesk mapping.
	 *
	 * @return array<int>
	 */
	public function get_term_ids(string $taxonomy): array
	{
		$sql = $this->wpdb->prepare(
			"SELECT object_id FROM {$this->table_name} WHERE object_type = %s AND taxonomy = %s",
			'term',
			$taxonomy
		);

		$ids = $this->wpdb->get_col($sql);

		if (empty($ids)) {
			return array();
		}

		return array_map('intval', $ids);
	}

	/**
	 * Store or update a post mapping.
	 */
	public function store_post_mapping(int $post_id, int $zendesk_id): void
	{
		$this->wpdb->replace(
			$this->table_name,
			array(
				'object_type' => 'post',
				'taxonomy'    => '',
				'object_id'   => $post_id,
				'zendesk_id'  => $zendesk_id,
			),
			array('%s', '%s', '%d', '%d')
		);
	}

	/**
	 * Fetch a post ID by Zendesk identifier.
	 */
	public function get_post_id(int $zendesk_id): ?int
	{
		$sql = $this->wpdb->prepare(
			"SELECT object_id FROM {$this->table_name} WHERE object_type = %s AND zendesk_id = %d LIMIT 1",
			'post',
			$zendesk_id
		);

		$post_id = $this->wpdb->get_var($sql);

		return $post_id ? (int) $post_id : null;
	}
}
