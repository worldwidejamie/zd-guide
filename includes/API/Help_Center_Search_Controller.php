<?php

/**
 * Help Center Search REST Controller.
 *
 * @package Wwj_Zdguide
 */

namespace WwjZdguide\API;

use WP_Query;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Server;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Registers a REST endpoint for searching Zendesk Help Center articles.
 */
class Help_Center_Search_Controller extends WP_REST_Controller
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->namespace = 'wwj-zdguide/v1';
		$this->rest_base = 'search';

		add_action('rest_api_init', array($this, 'register_routes'));
	}

	/**
	 * Registers REST API routes.
	 *
	 * @return void
	 */
	public function register_routes(): void
	{
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'handle_search'),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
			)
		);
	}

	/**
	 * Retrieves collection params for the endpoint.
	 *
	 * @return array
	 */
	public function get_collection_params(): array
	{
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';

		$params['q'] = array(
			'description' => __('Search query string.', 'wwj-zdguide'),
			'type'        => 'string',
			'required'    => false,
		);

		$params['per_page'] = array(
			'description'      => __('Number of results to return.', 'wwj-zdguide'),
			'type'             => 'integer',
			'default'          => 5,
			'sanitize_callback' => 'absint',
		);

		$params['show_excerpt'] = array(
			'description'      => __('Whether to include excerpts in the response.', 'wwj-zdguide'),
			'type'             => 'boolean',
			'default'          => true,
			'sanitize_callback' => array($this, 'sanitize_boolean_param'),
		);

		return $params;
	}

	/**
	 * Sanitize boolean-like parameter to actual bool.
	 *
	 * @param mixed $value Raw value.
	 * @return bool
	 */
	public function sanitize_boolean_param($value): bool
	{
		return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
	}

	/**
	 * Handle the search request.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function handle_search(WP_REST_Request $request)
	{
		$search_term = trim((string) $request->get_param('q'));
		if ('' === $search_term) {
			return rest_ensure_response(array('results' => array()));
		}

		$per_page     = (int) $request->get_param('per_page');
		$per_page     = max(1, min($per_page > 0 ? $per_page : 5, 20));
		$show_excerpt = $this->sanitize_boolean_param($request->get_param('show_excerpt'));

		$args = array(
			'post_type'           => 'zd_article',
			'post_status'         => 'publish',
			's'                   => $search_term,
			'posts_per_page'      => $per_page,
			'orderby'             => 'relevance',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		);

		$results = $this->execute_search($args, $show_excerpt);

		return rest_ensure_response(array('results' => $results));
	}

	/**
	 * Execute the search query and format results.
	 *
	 * @param array $args WP_Query arguments.
	 * @return array
	 */
	private function execute_search(array $args, bool $show_excerpt): array
	{
		$query = new WP_Query($args);

		if (! $query->have_posts()) {
			return array();
		}

		$results = array();
		while ($query->have_posts()) {
			$query->the_post();

			$excerpt = '';
			if ($show_excerpt) {
				$raw_excerpt = get_the_excerpt();
				if (! $raw_excerpt) {
					$raw_excerpt = wp_trim_words(get_the_content(null, false, false), 32);
				}
				$excerpt = wp_strip_all_tags($raw_excerpt);
			}

			$results[] = array(
				'id'      => get_the_ID(),
				'title'   => html_entity_decode(get_the_title()),
				'excerpt' => $excerpt,
				'url'     => get_permalink(),
			);
		}

		wp_reset_postdata();

		return $results;
	}
}
