<?php

/**
 * Post Type Registration Class
 *
 * @package Wwj_Zdguide
 * @since   0.1.0
 */

namespace WwjZdguide\PostTypes;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Class for registering the Zendesk Article post type.
 */
class Article
{
	/**
	 * Post type slug.
	 *
	 * @var string
	 */
	private string $post_type = 'zd_article';

	/**
	 * Initialize the post type.
	 */
	public function __construct()
	{
		add_action('init', array($this, 'register'));
	}

	/**
	 * Register the custom post type for articles.
	 *
	 * @return void
	 */
	public function register(): void
	{
		$labels = array(
			'name'                  => _x('Articles', 'Post type general name', 'zd-guide'),
			'singular_name'         => _x('Article', 'Post type singular name', 'zd-guide'),
			'menu_name'             => _x('Zendesk Guide', 'Admin Menu text', 'zd-guide'),
			'name_admin_bar'        => _x('Article', 'Add New on Toolbar', 'zd-guide'),
			'add_new'               => __('Add New', 'zd-guide'),
			'add_new_item'          => __('Add New Article', 'zd-guide'),
			'new_item'              => __('New Article', 'zd-guide'),
			'edit_item'             => __('Edit Article', 'zd-guide'),
			'view_item'             => __('View Article', 'zd-guide'),
			'all_items'             => __('All Articles', 'zd-guide'),
			'search_items'          => __('Search Articles', 'zd-guide'),
			'parent_item_colon'     => __('Parent Articles:', 'zd-guide'),
			'not_found'             => __('No articles found.', 'zd-guide'),
			'not_found_in_trash'    => __('No articles found in Trash.', 'zd-guide'),
			'featured_image'        => _x('Article Cover Image', 'Featured Image', 'zd-guide'),
			'set_featured_image'    => _x('Set cover image', 'Set featured image', 'zd-guide'),
			'remove_featured_image' => _x('Remove cover image', 'Remove featured image', 'zd-guide'),
			'use_featured_image'    => _x('Use as cover image', 'Use as featured image', 'zd-guide'),
			'archives'              => _x('Article archives', 'Post type archive', 'zd-guide'),
			'insert_into_item'      => _x('Insert into article', 'Insert into post', 'zd-guide'),
			'uploaded_to_this_item' => _x('Uploaded to this article', 'Uploaded to post', 'zd-guide'),
			'filter_items_list'     => _x('Filter articles list', 'Filter items list', 'zd-guide'),
			'items_list_navigation' => _x('Articles list navigation', 'Items list navigation', 'zd-guide'),
			'items_list'            => _x('Articles list', 'Items list', 'zd-guide'),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array('slug' => 'help-center'),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'),
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-book-alt',
			'rest_base'          => 'zd-articles',
			'rest_namespace'     => 'zd-guide/v1',
		);

		register_post_type($this->post_type, $args);
	}

	/**
	 * Get the post type slug.
	 *
	 * @return string
	 */
	public function get_post_type(): string
	{
		return $this->post_type;
	}
}
