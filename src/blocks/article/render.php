<?php

/**
 * Server-side rendering for the Zendesk Article block.
 *
 * @package Wwj_Zdguide
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

if (! defined('ABSPATH')) {
	exit;
}

$wwj_zdguide_article_id  = isset($attributes['articleId']) ? absint($attributes['articleId']) : 0;
$wwj_zdguide_show_excerpt = isset($attributes['showExcerpt']) ? (bool) $attributes['showExcerpt'] : true;
$wwj_zdguide_show_meta   = isset($attributes['showMeta']) ? (bool) $attributes['showMeta'] : true;

if (! $wwj_zdguide_article_id) {
	return '<div class="zd-guide-block-placeholder">' . esc_html__('Please select a Zendesk article to display.', 'zd-guide') . '</div>';
}

$wwj_zdguide_article = get_post($wwj_zdguide_article_id);

if (! $wwj_zdguide_article || 'zd_article' !== $wwj_zdguide_article->post_type) {
	return '<div class="zd-guide-block-error">' . esc_html__('Selected article not found.', 'zd-guide') . '</div>';
}

$wwj_zdguide_zendesk_id = get_post_meta($wwj_zdguide_article_id, 'zendesk_article_id', true);
$wwj_zdguide_categories = wp_get_post_terms($wwj_zdguide_article_id, 'zd_category');
$wwj_zdguide_sections   = wp_get_post_terms($wwj_zdguide_article_id, 'zd_section');

$wwj_zdguide_wrapper_attributes = get_block_wrapper_attributes(array(
	'class' => 'zd-guide-article-block',
));
?>

<div <?php echo wp_kses_post($wwj_zdguide_wrapper_attributes); ?>>
	<?php if ($wwj_zdguide_show_meta && (! empty($wwj_zdguide_categories) || ! empty($wwj_zdguide_sections))) : ?>
		<div class="zd-guide-article-meta">
			<?php if (! empty($wwj_zdguide_categories)) : ?>
				<span class="zd-guide-category">
					<?php echo esc_html($wwj_zdguide_categories[0]->name); ?>
				</span>
			<?php endif; ?>
			<?php if (! empty($wwj_zdguide_sections)) : ?>
				<span class="zd-guide-section">
					<?php echo esc_html($wwj_zdguide_sections[0]->name); ?>
				</span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<h3 class="zd-guide-article-title">
		<a href="<?php echo esc_url(get_permalink($wwj_zdguide_article)); ?>">
			<?php echo esc_html($wwj_zdguide_article->post_title); ?>
		</a>
	</h3>

	<?php if ($wwj_zdguide_show_excerpt) : ?>
		<div class="zd-guide-article-excerpt">
			<?php echo wp_kses_post(wp_trim_words($wwj_zdguide_article->post_content, 30, '...')); ?>
		</div>
	<?php endif; ?>

	<a href="<?php echo esc_url(get_permalink($wwj_zdguide_article)); ?>" class="zd-guide-read-more">
		<?php esc_html_e('Read full article', 'zd-guide'); ?>
	</a>
</div>