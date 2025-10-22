<?php

/**
 * Template loader for Zendesk Guide taxonomies.
 *
 * @package Wwj_Zdguide\Templates
 * @since   0.1.0
 */

namespace WwjZdguide\Templates;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Handles routing taxonomy requests to the plugin templates.
 */
class Template_Loader
{
	/**
	 * Supported taxonomy slugs.
	 *
	 * @var array<string>
	 */
	private array $supported_taxonomies = array('zd_category', 'zd_section');

	/**
	 * Setup hooks.
	 */
	public function __construct()
	{
		add_filter('taxonomy_template', array($this, 'filter_taxonomy_template'));
	}

	/**
	 * Switch taxonomy templates for plugin-specific taxonomies.
	 *
	 * @param string $template Path to the currently resolved template.
	 * @return string
	 */
	public function filter_taxonomy_template(string $template): string
	{
		$term = get_queried_object();

		if (! ($term instanceof \WP_Term) || ! in_array($term->taxonomy, $this->supported_taxonomies, true)) {
			return $template;
		}

		$candidate = WWJ_ZDGUIDE_PLUGIN_DIR . 'templates/taxonomy-' . $term->taxonomy . '.php';
		$fallback  = WWJ_ZDGUIDE_PLUGIN_DIR . 'templates/taxonomy-zd.php';

		if (file_exists($candidate)) {
			return $candidate;
		}

		if (file_exists($fallback)) {
			return $fallback;
		}

		return $template;
	}
}
