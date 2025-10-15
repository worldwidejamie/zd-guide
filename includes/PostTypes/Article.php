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
			'name'                  => _x('Articles', 'Post type general name', 'wwj-zdguide'),
			'singular_name'         => _x('Article', 'Post type singular name', 'wwj-zdguide'),
			'menu_name'             => _x('Zendesk Guide', 'Admin Menu text', 'wwj-zdguide'),
			'name_admin_bar'        => _x('Article', 'Add New on Toolbar', 'wwj-zdguide'),
			'add_new'               => __('Add New', 'wwj-zdguide'),
			'add_new_item'          => __('Add New Article', 'wwj-zdguide'),
			'new_item'              => __('New Article', 'wwj-zdguide'),
			'edit_item'             => __('Edit Article', 'wwj-zdguide'),
			'view_item'             => __('View Article', 'wwj-zdguide'),
			'all_items'             => __('All Articles', 'wwj-zdguide'),
			'search_items'          => __('Search Articles', 'wwj-zdguide'),
			'parent_item_colon'     => __('Parent Articles:', 'wwj-zdguide'),
			'not_found'             => __('No articles found.', 'wwj-zdguide'),
			'not_found_in_trash'    => __('No articles found in Trash.', 'wwj-zdguide'),
			'featured_image'        => _x('Article Cover Image', 'Featured Image', 'wwj-zdguide'),
			'set_featured_image'    => _x('Set cover image', 'Set featured image', 'wwj-zdguide'),
			'remove_featured_image' => _x('Remove cover image', 'Remove featured image', 'wwj-zdguide'),
			'use_featured_image'    => _x('Use as cover image', 'Use as featured image', 'wwj-zdguide'),
			'archives'              => _x('Article archives', 'Post type archive', 'wwj-zdguide'),
			'insert_into_item'      => _x('Insert into article', 'Insert into post', 'wwj-zdguide'),
			'uploaded_to_this_item' => _x('Uploaded to this article', 'Uploaded to post', 'wwj-zdguide'),
			'filter_items_list'     => _x('Filter articles list', 'Filter items list', 'wwj-zdguide'),
			'items_list_navigation' => _x('Articles list navigation', 'Items list navigation', 'wwj-zdguide'),
			'items_list'            => _x('Articles list', 'Items list', 'wwj-zdguide'),
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
			'rest_namespace'     => 'wwj-zdguide/v1',
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
