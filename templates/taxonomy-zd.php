<?php

/**
 * Template for Zendesk Guide taxonomies (categories and sections).
 *
 * @package Wwj_Zdguide
 */

declare(strict_types=1);
if (! defined('ABSPATH')) {
	exit;
}

wp_enqueue_style(
	'wwj-zdguide-taxonomy-template',
	WWJ_ZDGUIDE_PLUGIN_URL . 'assets/css/taxonomy-template.css',
	array(),
	WWJ_ZDGUIDE_VERSION
);

get_header();

$term = get_queried_object();

if (! ($term instanceof WP_Term) || ! in_array($term->taxonomy, array('zd_category', 'zd_section'), true)) {
	get_template_part('taxonomy');
	get_footer();
	return;
}

$term_description = term_description($term);
?>
<main id="primary" class="wwj-zdguide-template" aria-labelledby="wwj-zdguide-term-title">
	<div class="wwj-zdguide-template__header">
		<h1 id="wwj-zdguide-term-title" class="wwj-zdguide-template__title">
			<?php echo esc_html(single_term_title('', false)); ?>
		</h1>
		<?php if (! empty($term_description)) : ?>
			<div class="wwj-zdguide-template__description">
				<?php echo wp_kses_post($term_description); ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ('zd_category' === $term->taxonomy) : ?>
		<?php
		$sections = get_terms(
			array(
				'taxonomy'   => 'zd_section',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if (is_wp_error($sections)) {
			$sections = array();
		} else {
			// Filter in PHP to avoid slow taxonomy meta queries flagged by plugin review.
			$sections = array_values(
				array_filter(
					$sections,
					static function (\WP_Term $section_term) use ($term): bool {
						$parent_id = (int) get_term_meta($section_term->term_id, 'zd_category_term_id', true);
						return $parent_id === (int) $term->term_id;
					}
				)
			);
		}
		?>

		<section class="wwj-zdguide-template__section" aria-label="<?php esc_attr_e('Sections', 'wwj-zdguide'); ?>">
			<h2 class="wwj-zdguide-template__section-title"><?php esc_html_e('Sections', 'wwj-zdguide'); ?></h2>

			<?php if (! empty($sections)) : ?>
				<ul class="wwj-zdguide-taxonomy-list">
					<?php foreach ($sections as $section_term) :
						$link = get_term_link($section_term);
						if (is_wp_error($link)) {
							continue;
						}
						$section_description = trim(wp_strip_all_tags($section_term->description));
					?>
						<li class="wwj-zdguide-taxonomy-item">
							<div class="wwj-zdguide-taxonomy-header">
								<a class="wwj-zdguide-taxonomy-name" href="<?php echo esc_url($link); ?>">
									<?php echo esc_html($section_term->name); ?>
								</a>
								<span class="wwj-zdguide-taxonomy-count">
									<?php echo esc_html(number_format_i18n($section_term->count)); ?>
								</span>
							</div>

							<?php if (! empty($section_description)) : ?>
								<p class="wwj-zdguide-taxonomy-description">
									<?php echo esc_html($section_description); ?>
								</p>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="wwj-zdguide-template__empty">
					<?php esc_html_e('No sections have been synced for this category yet.', 'wwj-zdguide'); ?>
				</p>
			<?php endif; ?>
		</section>
	<?php else : ?>
		<?php
		$article_ids = get_objects_in_term($term->term_id, 'zd_section');

		if (is_wp_error($article_ids) || empty($article_ids)) {
			$articles = array();
		} else {
			$articles = get_posts(
				array(
					'post_type'           => 'zd_article',
					'post_status'         => 'publish',
					'orderby'             => 'title',
					'order'               => 'ASC',
					'posts_per_page'      => -1,
					'ignore_sticky_posts' => true,
					'post__in'            => array_map('intval', $article_ids),
				)
			);
		}
		?>

		<section class="wwj-zdguide-template__section" aria-label="<?php esc_attr_e('Articles', 'wwj-zdguide'); ?>">
			<h2 class="wwj-zdguide-template__section-title"><?php esc_html_e('Articles', 'wwj-zdguide'); ?></h2>

			<?php if (! empty($articles)) : ?>
				<ul class="wwj-zdguide-taxonomy-list">
					<?php foreach ($articles as $article_post) :
						$article_link   = get_permalink($article_post);
						$article_title  = get_the_title($article_post);
						$article_excerpt = trim(wp_strip_all_tags(get_the_excerpt($article_post)));
					?>
						<li class="wwj-zdguide-taxonomy-item">
							<div class="wwj-zdguide-taxonomy-header">
								<a class="wwj-zdguide-taxonomy-name" href="<?php echo esc_url($article_link); ?>">
									<?php echo esc_html($article_title); ?>
								</a>
							</div>
							<?php if (! empty($article_excerpt)) : ?>
								<p class="wwj-zdguide-taxonomy-description"><?php echo esc_html($article_excerpt); ?></p>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="wwj-zdguide-template__empty">
					<?php esc_html_e('No articles are assigned to this section yet.', 'wwj-zdguide'); ?>
				</p>
			<?php endif; ?>
		</section>

	<?php endif; ?>
</main>

<?php get_footer();
