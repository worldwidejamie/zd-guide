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

		add_action('zd_category_edit_form', array($this, 'render_reference_ids_panel'), 10, 2);

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

	public function render_reference_ids_panel(\WP_Term $term, string $taxonomy): void
	{
		if ($taxonomy !== $this->taxonomy) {
			return;
		}

		$zendesk_cat_id = get_term_meta($term->term_id, 'zendesk_category_id', true);
		$zendesk_cat_id = $zendesk_cat_id !== '' ? $zendesk_cat_id : '—';
?>
		<div class="wwj-zdguide-admin-panel">
			<h2><?php esc_html_e('Reference IDs', 'wwj-zdguide'); ?></h2>
			<div class="wwj-zdguide-admin-row">
				<strong><?php esc_html_e('WordPress Term ID', 'wwj-zdguide'); ?></strong>
				<span><?php echo esc_html((string) $term->term_id); ?></span>
			</div>
			<div class="wwj-zdguide-admin-row">
				<strong><?php esc_html_e('Zendesk Category ID', 'wwj-zdguide'); ?></strong>
				<span><?php echo esc_html((string) $zendesk_cat_id); ?></span>
			</div>
		</div>
	<?php
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

		add_action('zd_section_edit_form', array($this, 'render_reference_ids_panel'), 10, 2);

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

	public function render_reference_ids_panel(\WP_Term $term, string $taxonomy): void
	{
		if ($taxonomy !== $this->taxonomy) {
			return;
		}

		$zendesk_sec_id = get_term_meta($term->term_id, 'zendesk_section_id', true);
		$zendesk_sec_id = $zendesk_sec_id !== '' ? $zendesk_sec_id : '—';
		$parent_term    = $term->parent ? get_term($term->parent, 'zd_category') : null;
		$parent_cat_id  = $parent_term instanceof \WP_Term ? $parent_term->term_id : null;
		$parent_zd_id   = $parent_term instanceof \WP_Term ? get_term_meta($parent_term->term_id, 'zendesk_category_id', true) : '';
		$parent_zd_id   = $parent_zd_id !== '' ? $parent_zd_id : '—';
	?>
		<div class="wwj-zdguide-admin-panel">
			<h2><?php esc_html_e('Reference IDs', 'wwj-zdguide'); ?></h2>
			<div class="wwj-zdguide-admin-row">
				<strong><?php esc_html_e('WordPress Term ID', 'wwj-zdguide'); ?></strong>
				<span><?php echo esc_html((string) $term->term_id); ?></span>
			</div>
			<div class="wwj-zdguide-admin-row">
				<strong><?php esc_html_e('Zendesk Section ID', 'wwj-zdguide'); ?></strong>
				<span><?php echo esc_html((string) $zendesk_sec_id); ?></span>
			</div>
			<?php if ($parent_term instanceof \WP_Term) : ?>
				<div class="wwj-zdguide-admin-row">
					<strong><?php esc_html_e('Parent Category Term ID', 'wwj-zdguide'); ?></strong>
					<span><?php echo esc_html((string) $parent_cat_id); ?></span>
				</div>
				<div class="wwj-zdguide-admin-row">
					<strong><?php esc_html_e('Parent Zendesk Category ID', 'wwj-zdguide'); ?></strong>
					<span><?php echo esc_html((string) $parent_zd_id); ?></span>
				</div>
			<?php endif; ?>
		</div>
<?php
	}
}
