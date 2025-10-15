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

$article_id  = isset($attributes['articleId']) ? absint($attributes['articleId']) : 0;
$show_excerpt = isset($attributes['showExcerpt']) ? (bool) $attributes['showExcerpt'] : true;
$show_meta   = isset($attributes['showMeta']) ? (bool) $attributes['showMeta'] : true;

if (! $article_id) {
	return '<div class="wwj-zdguide-block-placeholder">' . esc_html__('Please select a Zendesk article to display.', 'wwj-zdguide') . '</div>';
}

$article = get_post($article_id);

if (! $article || 'zd_article' !== $article->post_type) {
	return '<div class="wwj-zdguide-block-error">' . esc_html__('Selected article not found.', 'wwj-zdguide') . '</div>';
}

$zendesk_id = get_post_meta($article_id, 'zendesk_article_id', true);
$categories = wp_get_post_terms($article_id, 'zd_category');
$sections   = wp_get_post_terms($article_id, 'zd_section');

$wrapper_attributes = get_block_wrapper_attributes(array(
	'class' => 'wwj-zdguide-article-block',
));
?>

<div <?php echo $wrapper_attributes; ?>>
	<?php if ($show_meta && (! empty($categories) || ! empty($sections))) : ?>
		<div class="wwj-zdguide-article-meta">
			<?php if (! empty($categories)) : ?>
				<span class="wwj-zdguide-category">
					<?php echo esc_html($categories[0]->name); ?>
				</span>
			<?php endif; ?>
			<?php if (! empty($sections)) : ?>
				<span class="wwj-zdguide-section">
					<?php echo esc_html($sections[0]->name); ?>
				</span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<h3 class="wwj-zdguide-article-title">
		<a href="<?php echo esc_url(get_permalink($article)); ?>">
			<?php echo esc_html($article->post_title); ?>
		</a>
	</h3>

	<?php if ($show_excerpt) : ?>
		<div class="wwj-zdguide-article-excerpt">
			<?php echo wp_kses_post(wp_trim_words($article->post_content, 30, '...')); ?>
		</div>
	<?php endif; ?>

	<a href="<?php echo esc_url(get_permalink($article)); ?>" class="wwj-zdguide-read-more">
		<?php esc_html_e('Read full article', 'wwj-zdguide'); ?>
	</a>
</div>
