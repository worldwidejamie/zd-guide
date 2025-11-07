<?php

/**
 * Server-side rendering for the Zendesk Taxonomy Index block.
 *
 * @package Wwj_Zdguide
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

if (! defined('ABSPATH')) {
	exit;
}

$wwj_zdguide_allowed_taxonomies = array('zd_category', 'zd_section');
$wwj_zdguide_taxonomy           = isset($attributes['taxonomy']) && in_array($attributes['taxonomy'], $wwj_zdguide_allowed_taxonomies, true)
	? $attributes['taxonomy']
	: 'zd_category';

$wwj_zdguide_items_to_show    = isset($attributes['itemsToShow']) ? absint($attributes['itemsToShow']) : 6;
$wwj_zdguide_items_to_show    = $wwj_zdguide_items_to_show > 0 ? min($wwj_zdguide_items_to_show, 50) : 6;
$wwj_zdguide_show_counts      = ! empty($attributes['showCounts']);
$wwj_zdguide_show_descriptions = ! empty($attributes['showDescriptions']);

$wwj_zdguide_terms = get_terms(
	array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => false,
		'number'     => $items_to_show,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);

if (is_wp_error($terms) || empty($terms)) {
	$message = 'zd_section' === $taxonomy
		? __('No sections available. Please run a sync to import Zendesk sections.', 'zd-guide')
		: __('No categories available. Please run a sync to import Zendesk categories.', 'zd-guide');

	return '<div class="zd-guide-taxonomy-block"><p class="zd-guide-taxonomy-empty">' . esc_html($message) . '</p></div>';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'zd-guide-taxonomy-block',
	)
);
?>
<div <?php echo wp_kses_post($wrapper_attributes); ?>>
	<ul class="zd-guide-taxonomy-list">
		<?php foreach ($terms as $term) :
			$link = get_term_link($term);
			if (is_wp_error($link)) {
				continue;
			}
		?>
			<li class="zd-guide-taxonomy-item">
				<div class="zd-guide-taxonomy-header">
					<a class="zd-guide-taxonomy-name" href="<?php echo esc_url($link); ?>">
						<?php echo esc_html($term->name); ?>
					</a>
					<?php if ($show_counts) : ?>
						<span class="zd-guide-taxonomy-count" aria-label="<?php echo esc_attr__('Article count', 'zd-guide'); ?>">
							<?php echo esc_html((string) absint($term->count)); ?>
						</span>
					<?php endif; ?>
				</div>
				<?php if ($show_descriptions && ! empty($term->description)) : ?>
					<p class="zd-guide-taxonomy-description">
						<?php echo esc_html(wp_strip_all_tags($term->description)); ?>
					</p>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php
