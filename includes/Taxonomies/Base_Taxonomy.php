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
			'name'              => _x('Categories', 'taxonomy general name', 'zd-guide'),
			'singular_name'     => _x('Category', 'taxonomy singular name', 'zd-guide'),
			'search_items'      => __('Search Categories', 'zd-guide'),
			'all_items'         => __('All Categories', 'zd-guide'),
			'parent_item'       => __('Parent Category', 'zd-guide'),
			'parent_item_colon' => __('Parent Category:', 'zd-guide'),
			'edit_item'         => __('Edit Category', 'zd-guide'),
			'update_item'       => __('Update Category', 'zd-guide'),
			'add_new_item'      => __('Add New Category', 'zd-guide'),
			'new_item_name'     => __('New Category Name', 'zd-guide'),
			'menu_name'         => __('Categories', 'zd-guide'),
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
			'rest_namespace'    => 'zd-guide/v1',
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
		<div class="zd-guide-admin-panel">
			<h2><?php esc_html_e('Reference IDs', 'zd-guide'); ?></h2>
			<div class="zd-guide-admin-row">
				<strong><?php esc_html_e('WordPress Term ID', 'zd-guide'); ?></strong>
				<span><?php echo esc_html((string) $term->term_id); ?></span>
			</div>
			<div class="zd-guide-admin-row">
				<strong><?php esc_html_e('Zendesk Category ID', 'zd-guide'); ?></strong>
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
			'name'              => _x('Sections', 'taxonomy general name', 'zd-guide'),
			'singular_name'     => _x('Section', 'taxonomy singular name', 'zd-guide'),
			'search_items'      => __('Search Sections', 'zd-guide'),
			'all_items'         => __('All Sections', 'zd-guide'),
			'parent_item'       => __('Parent Section', 'zd-guide'),
			'parent_item_colon' => __('Parent Section:', 'zd-guide'),
			'edit_item'         => __('Edit Section', 'zd-guide'),
			'update_item'       => __('Update Section', 'zd-guide'),
			'add_new_item'      => __('Add New Section', 'zd-guide'),
			'new_item_name'     => __('New Section Name', 'zd-guide'),
			'menu_name'         => __('Sections', 'zd-guide'),
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
			'rest_namespace'    => 'zd-guide/v1',
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
		$parent_cat_id  = (int) get_term_meta($term->term_id, 'zd_category_term_id', true);
		$parent_term    = $parent_cat_id > 0 ? get_term($parent_cat_id, 'zd_category') : null;
		$parent_zd_id   = $parent_term instanceof \WP_Term ? get_term_meta($parent_term->term_id, 'zendesk_category_id', true) : '';
		$parent_zd_id   = $parent_zd_id !== '' ? $parent_zd_id : '—';
	?>
		<div class="zd-guide-admin-panel">
			<h2><?php esc_html_e('Reference IDs', 'zd-guide'); ?></h2>
			<div class="zd-guide-admin-row">
				<strong><?php esc_html_e('WordPress Term ID', 'zd-guide'); ?></strong>
				<span><?php echo esc_html((string) $term->term_id); ?></span>
			</div>
			<div class="zd-guide-admin-row">
				<strong><?php esc_html_e('Zendesk Section ID', 'zd-guide'); ?></strong>
				<span><?php echo esc_html((string) $zendesk_sec_id); ?></span>
			</div>
			<?php if ($parent_term instanceof \WP_Term) : ?>
				<div class="zd-guide-admin-row">
					<strong><?php esc_html_e('Parent Category Term ID', 'zd-guide'); ?></strong>
					<span><?php echo esc_html((string) $parent_term->term_id); ?></span>
				</div>
				<div class="zd-guide-admin-row">
					<strong><?php esc_html_e('Parent Zendesk Category ID', 'zd-guide'); ?></strong>
					<span><?php echo esc_html((string) $parent_zd_id); ?></span>
				</div>
			<?php endif; ?>
		</div>
<?php
	}
}
