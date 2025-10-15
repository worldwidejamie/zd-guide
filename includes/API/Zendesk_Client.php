<?php

/**
 * Zendesk API Client
 *
 * @package Wwj_Zdguide
 * @since   0.1.0
 */

namespace WwjZdguide\API;

use WP_Error;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Handles all Zendesk API communications.
 */
class Zendesk_Client
{
	/**
	 * Zendesk subdomain.
	 *
	 * @var string
	 */
	private string $subdomain;

	/**
	 * Zendesk email.
	 *
	 * @var string
	 */
	private string $email;

	/**
	 * Zendesk API token.
	 *
	 * @var string
	 */
	private string $api_token;

	/**
	 * Base URL for API requests.
	 *
	 * @var string
	 */
	private string $base_url;

	/**
	 * Constructor.
	 *
	 * @param string $subdomain Zendesk subdomain.
	 * @param string $email     Zendesk email.
	 * @param string $api_token Zendesk API token.
	 */
	public function __construct(string $subdomain, string $email, string $api_token)
	{
		$this->subdomain = $subdomain;
		$this->email     = $email;
		$this->api_token = $api_token;
		$this->base_url  = "https://{$subdomain}.zendesk.com/api/v2/help_center";
	}

	/**
	 * Make an API request.
	 *
	 * @param string $endpoint API endpoint.
	 * @param array  $params   Query parameters.
	 * @return array|WP_Error Response data or error.
	 */
	private function request(string $endpoint, array $params = array())
	{
		$url = $this->base_url . $endpoint;

		if (! empty($params)) {
			$url = add_query_arg($params, $url);
		}

		$args = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode("{$this->email}/token:{$this->api_token}"),
			),
			'timeout' => 30,
		);

		$response = wp_remote_get($url, $args);

		if (is_wp_error($response)) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);
		$data          = json_decode($body);

		if (200 !== $response_code) {
			$error_message = $data->error ?? __('Unknown API error', 'wwj-zdguide');
			return new WP_Error('api_error', $error_message, array('status' => $response_code));
		}

		return $data;
	}

	/**
	 * Test the API connection.
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function test_connection()
	{
		$result = $this->request('/articles');

		if (is_wp_error($result)) {
			return $result;
		}

		return true;
	}

	/**
	 * Fetch all categories.
	 *
	 * @return array|WP_Error Array of categories or error.
	 */
	public function get_categories()
	{
		$result = $this->request('/categories.json');

		if (is_wp_error($result)) {
			return $result;
		}

		return $result->categories ?? array();
	}

	/**
	 * Fetch sections for a category.
	 *
	 * @param int $category_id Category ID from Zendesk.
	 * @return array|WP_Error Array of sections or error.
	 */
	public function get_sections(int $category_id)
	{
		$result = $this->request("/categories/{$category_id}/sections.json");

		if (is_wp_error($result)) {
			return $result;
		}

		return $result->sections ?? array();
	}

	/**
	 * Fetch articles for a section.
	 *
	 * @param int $section_id Section ID from Zendesk.
	 * @return array|WP_Error Array of articles or error.
	 */
	public function get_articles(int $section_id)
	{
		$result = $this->request("/sections/{$section_id}/articles.json");

		if (is_wp_error($result)) {
			return $result;
		}

		return $result->articles ?? array();
	}
}
