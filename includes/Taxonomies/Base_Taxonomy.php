<?php

/**
 * Taxonomy Registration Classes
 *
 * @package Wwj_Zdguide
 * @since   0.1.0
 */

namespace WwjZdguide\Taxonomies;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Base class for taxonomy registration.
 */
abstract class Base_Taxonomy
{
	/**
	 * Taxonomy slug.
	 *
	 * @var string
	 */
	protected string $taxonomy;

	/**
	 * Post types this taxonomy applies to.
	 *
	 * @var array
	 */
	protected array $post_types;

	/**
	 * Initialize the taxonomy.
	 */
	public function __construct()
	{
		add_action('init', array($this, 'register'));
	}

	/**
	 * Register the taxonomy.
	 *
	 * @return void
	 */
	abstract public function register(): void;

	/**
	 * Get the taxonomy slug.
	 *
	 * @return string
	 */
	public function get_taxonomy(): string
	{
		return $this->taxonomy;
	}
}

/**
 * Category taxonomy class.
 */
class Category extends Base_Taxonomy
{
	/**
	 * Category taxonomy constructor.
	 */
	public function __construct()
	{
		$this->taxonomy   = 'zd_category';
		$this->post_types = array('zd_article');
		parent::__construct();
	}

	/**
	 * Register the category taxonomy.
	 *
	 * @return void
	 */
	public function register(): void
	{
		$labels = array(
			'name'              => _x('Categories', 'taxonomy general name', 'wwj-zdguide'),
			'singular_name'     => _x('Category', 'taxonomy singular name', 'wwj-zdguide'),
			'search_items'      => __('Search Categories', 'wwj-zdguide'),
			'all_items'         => __('All Categories', 'wwj-zdguide'),
			'parent_item'       => __('Parent Category', 'wwj-zdguide'),
			'parent_item_colon' => __('Parent Category:', 'wwj-zdguide'),
			'edit_item'         => __('Edit Category', 'wwj-zdguide'),
			'update_item'       => __('Update Category', 'wwj-zdguide'),
			'add_new_item'      => __('Add New Category', 'wwj-zdguide'),
			'new_item_name'     => __('New Category Name', 'wwj-zdguide'),
			'menu_name'         => __('Categories', 'wwj-zdguide'),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array('slug' => 'help-center/categories'),
			'show_in_rest'      => true,
			'rest_base'         => 'zd-categories',
			'rest_namespace'    => 'wwj-zdguide/v1',
		);

		register_taxonomy($this->taxonomy, $this->post_types, $args);
	}
}

/**
 * Section taxonomy class.
 */
class Section extends Base_Taxonomy
{
	/**
	 * Section taxonomy constructor.
	 */
	public function __construct()
	{
		$this->taxonomy   = 'zd_section';
		$this->post_types = array('zd_article');
		parent::__construct();
	}

	/**
	 * Register the section taxonomy.
	 *
	 * @return void
	 */
	public function register(): void
	{
		$labels = array(
			'name'              => _x('Sections', 'taxonomy general name', 'wwj-zdguide'),
			'singular_name'     => _x('Section', 'taxonomy singular name', 'wwj-zdguide'),
			'search_items'      => __('Search Sections', 'wwj-zdguide'),
			'all_items'         => __('All Sections', 'wwj-zdguide'),
			'parent_item'       => __('Parent Section', 'wwj-zdguide'),
			'parent_item_colon' => __('Parent Section:', 'wwj-zdguide'),
			'edit_item'         => __('Edit Section', 'wwj-zdguide'),
			'update_item'       => __('Update Section', 'wwj-zdguide'),
			'add_new_item'      => __('Add New Section', 'wwj-zdguide'),
			'new_item_name'     => __('New Section Name', 'wwj-zdguide'),
			'menu_name'         => __('Sections', 'wwj-zdguide'),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array('slug' => 'help-center/sections'),
			'show_in_rest'      => true,
			'rest_base'         => 'zd-sections',
			'rest_namespace'    => 'wwj-zdguide/v1',
		);

		register_taxonomy($this->taxonomy, $this->post_types, $args);
	}
}
