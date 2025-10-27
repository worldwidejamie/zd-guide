<?php

/**
 * Help Center Ticket Form REST Controller.
 *
 * @package Wwj_Zdguide
 */

namespace WwjZdguide\API;

use WP_Error;
use WP_Query;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if (! defined('ABSPATH')) {
	exit;
}


/**
 * Registers a REST endpoint for managing Help Center Ticket Forms
 */

class Help_Center_Ticket_Form_Controller extends WP_REST_Controller
{
	private Zendesk_Client $client;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->namespace = 'wwj-zdguide/v1';
		$this->rest_base = 'ticket_forms';

		add_action('rest_api_init', array($this, 'register_routes'));
	}

	public function register_routes(): void
	{
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'		=> 'GET',
				'callback'		=> array($this, 'get_items'),
				'permission_callback'	=> array($this, 'can_list_forms'),
			)
		);
	}

	public function can_list_forms(): bool
	{
		return current_user_can('manage_options');
	}

	/**
	 * Get list of forms
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_forms(WP_REST_Request $request): WP_REST_Response|WP_Error
	{
		$response = $request('/api/v2/ticket_forms');

		if (is_wp_error($response)) {
			return $response;
		}

		$data = json_decode(\wp_remote_retrieve_body($response), true);
		$forms = array();

		if (!empty($data['ticket_forms']) && is_array($data['ticket_forms'])):
			foreach ($data['ticket_forms'] as $form):
				$forms[] = array(
					'id' => (int) $form['id'],
					'name' => (string) $form['name']
				);
			endforeach;
		endif;

		return new WP_REST_Response($forms);
	}
}
