<?php

/**
 * Block registration and functionality.
 *
 * @package Wwj_Zdguide
 */

if (! defined('WPINC')) {
	die;
}

/**
 * Register the Zendesk Article block.
 */
function wwj_zdguide_register_blocks()
{
	wp_register_script(
		'wwj-zdguide-block-editor',
		WWJ_ZDGUIDE_PLUGIN_URL . 'assets/js/block-editor.js',
		array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-components', 'wp-data'),
		WWJ_ZDGUIDE_VERSION,
		true
	);

	wp_register_style(
		'wwj-zdguide-block-editor',
		WWJ_ZDGUIDE_PLUGIN_URL . 'assets/css/block-editor.css',
		array('wp-edit-blocks'),
		WWJ_ZDGUIDE_VERSION
	);

	wp_register_style(
		'wwj-zdguide-block-frontend',
		WWJ_ZDGUIDE_PLUGIN_URL . 'assets/css/block-frontend.css',
		array(),
		WWJ_ZDGUIDE_VERSION
	);

	register_block_type('wwj-zdguide/article', array(
		'editor_script'   => 'wwj-zdguide-block-editor',
		'editor_style'    => 'wwj-zdguide-block-editor',
		'style'           => 'wwj-zdguide-block-frontend',
		'render_callback' => 'wwj_zdguide_render_article_block',
		'attributes'      => array(
			'articleId' => array(
				'type'    => 'number',
				'default' => 0,
			),
		),
	));

	// Localize script with articles data
	$articles = get_posts(array(
		'post_type'      => 'zd_article',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	));

	$articles_data = array();
	foreach ($articles as $article) {
		$articles_data[] = array(
			'id'    => $article->ID,
			'title' => $article->post_title,
		);
	}

	wp_localize_script('wwj-zdguide-block-editor', 'wwjZdguideBlock', array(
		'articles' => $articles_data,
	));
}
add_action('init', 'wwj_zdguide_register_blocks');

/**
 * Render the article block on the frontend.
 *
 * @param array $attributes Block attributes.
 * @return string Block HTML output.
 */
function wwj_zdguide_render_article_block($attributes)
{
	$article_id = isset($attributes['articleId']) ? intval($attributes['articleId']) : 0;

	if (! $article_id) {
		return '<div class="wwj-zdguide-block-placeholder">' . __('Please select a Zendesk article to display.', 'wwj-zdguide') . '</div>';
	}

	$article = get_post($article_id);

	if (! $article || $article->post_type !== 'zd_article') {
		return '<div class="wwj-zdguide-block-error">' . __('Selected article not found.', 'wwj-zdguide') . '</div>';
	}

	$zendesk_id = get_post_meta($article_id, 'zendesk_article_id', true);
	$categories = wp_get_post_terms($article_id, 'zd_category');
	$sections   = wp_get_post_terms($article_id, 'zd_section');

	ob_start();
?>
	<div class="wwj-zdguide-article-block">
		<div class="wwj-zdguide-article-meta">
			<?php if (! empty($categories)) : ?>
				<span class="wwj-zdguide-category"><?php echo esc_html($categories[0]->name); ?></span>
			<?php endif; ?>
			<?php if (! empty($sections)) : ?>
				<span class="wwj-zdguide-section"><?php echo esc_html($sections[0]->name); ?></span>
			<?php endif; ?>
		</div>
		<h3 class="wwj-zdguide-article-title">
			<a href="<?php echo esc_url(get_permalink($article)); ?>">
				<?php echo esc_html($article->post_title); ?>
			</a>
		</h3>
		<div class="wwj-zdguide-article-excerpt">
			<?php echo wp_kses_post(wp_trim_words($article->post_content, 30, '...')); ?>
		</div>
		<a href="<?php echo esc_url(get_permalink($article)); ?>" class="wwj-zdguide-read-more">
			<?php _e('Read full article', 'wwj-zdguide'); ?>
		</a>
	</div>
<?php
	return ob_get_clean();
}
