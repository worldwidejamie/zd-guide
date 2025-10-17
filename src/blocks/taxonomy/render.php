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

$allowed_taxonomies = array('zd_category', 'zd_section');
$taxonomy           = isset($attributes['taxonomy']) && in_array($attributes['taxonomy'], $allowed_taxonomies, true)
	? $attributes['taxonomy']
	: 'zd_category';

$items_to_show    = isset($attributes['itemsToShow']) ? absint($attributes['itemsToShow']) : 6;
$items_to_show    = $items_to_show > 0 ? min($items_to_show, 50) : 6;
$show_counts      = ! empty($attributes['showCounts']);
$show_descriptions = ! empty($attributes['showDescriptions']);

$terms = get_terms(
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
		? __('No sections available. Please run a sync to import Zendesk sections.', 'wwj-zdguide')
		: __('No categories available. Please run a sync to import Zendesk categories.', 'wwj-zdguide');

	return '<div class="wwj-zdguide-taxonomy-block"><p class="wwj-zdguide-taxonomy-empty">' . esc_html($message) . '</p></div>';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'wwj-zdguide-taxonomy-block',
	)
);
?>
<div <?php echo $wrapper_attributes; ?>>
	<ul class="wwj-zdguide-taxonomy-list">
		<?php foreach ($terms as $term) :
			$link = get_term_link($term);
			if (is_wp_error($link)) {
				continue;
			}
		?>
			<li class="wwj-zdguide-taxonomy-item">
				<div class="wwj-zdguide-taxonomy-header">
					<a class="wwj-zdguide-taxonomy-name" href="<?php echo esc_url($link); ?>">
						<?php echo esc_html($term->name); ?>
					</a>
					<?php if ($show_counts) : ?>
						<span class="wwj-zdguide-taxonomy-count" aria-label="<?php echo esc_attr__('Article count', 'wwj-zdguide'); ?>">
							<?php echo esc_html((string) absint($term->count)); ?>
						</span>
					<?php endif; ?>
				</div>
				<?php if ($show_descriptions && ! empty($term->description)) : ?>
					<p class="wwj-zdguide-taxonomy-description">
						<?php echo esc_html(wp_strip_all_tags($term->description)); ?>
					</p>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php
