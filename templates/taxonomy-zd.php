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
	'zd-guide-taxonomy-template',
	WWJ_ZDGUIDE_PLUGIN_URL . 'assets/css/taxonomy-template.css',
	array(),
	WWJ_ZDGUIDE_VERSION
);

get_header();

$wwj_zdguide_term = get_queried_object();

if (! ($wwj_zdguide_term instanceof WP_Term) || ! in_array($wwj_zdguide_term->taxonomy, array('zd_category', 'zd_section'), true)) {
	get_template_part('taxonomy');
	get_footer();
	return;
}

$wwj_zdguide_term_description = term_description($wwj_zdguide_term);
?>
<main id="primary" class="zd-guide-template" aria-labelledby="zd-guide-term-title">
	<div class="zd-guide-template__header">
		<h1 id="zd-guide-term-title" class="zd-guide-template__title">
			<?php echo esc_html(single_term_title('', false)); ?>
		</h1>
		<?php if (! empty($wwj_zdguide_term_description)) : ?>
			<div class="zd-guide-template__description">
				<?php echo wp_kses_post($wwj_zdguide_term_description); ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ('zd_category' === $wwj_zdguide_term->taxonomy) : ?>
		<?php
		$wwj_zdguide_sections = get_terms(
			array(
				'taxonomy'   => 'zd_section',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if (is_wp_error($wwj_zdguide_sections)) {
			$wwj_zdguide_sections = array();
		} else {
			// Filter in PHP to avoid slow taxonomy meta queries flagged by plugin review.
			$wwj_zdguide_sections = array_values(
				array_filter(
					$wwj_zdguide_sections,
					static function (\WP_Term $wwj_zdguide_section_term) use ($wwj_zdguide_term): bool {
						$wwj_zdguide_parent_id = (int) get_term_meta($wwj_zdguide_section_term->term_id, 'zd_category_term_id', true);
						return $wwj_zdguide_parent_id === (int) $wwj_zdguide_term->term_id;
					}
				)
			);
		}
		?>

		<section class="zd-guide-template__section" aria-label="<?php esc_attr_e('Sections', 'zd-guide'); ?>">
			<h2 class="zd-guide-template__section-title"><?php esc_html_e('Sections', 'zd-guide'); ?></h2>

			<?php if (! empty($wwj_zdguide_sections)) : ?>
				<ul class="zd-guide-taxonomy-list">
					<?php foreach ($wwj_zdguide_sections as $wwj_zdguide_section_term) :
						$wwj_zdguide_section_link = get_term_link($wwj_zdguide_section_term);
						if (is_wp_error($wwj_zdguide_section_link)) {
							continue;
						}
						$wwj_zdguide_section_description = trim(wp_strip_all_tags($wwj_zdguide_section_term->description));
					?>
						<li class="zd-guide-taxonomy-item">
							<div class="zd-guide-taxonomy-header">
								<a class="zd-guide-taxonomy-name" href="<?php echo esc_url($wwj_zdguide_section_link); ?>">
									<?php echo esc_html($wwj_zdguide_section_term->name); ?>
								</a>
								<span class="zd-guide-taxonomy-count">
									<?php echo esc_html(number_format_i18n($wwj_zdguide_section_term->count)); ?>
								</span>
							</div>

							<?php if (! empty($wwj_zdguide_section_description)) : ?>
								<p class="zd-guide-taxonomy-description">
									<?php echo esc_html($wwj_zdguide_section_description); ?>
								</p>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="zd-guide-template__empty">
					<?php esc_html_e('No sections have been synced for this category yet.', 'zd-guide'); ?>
				</p>
			<?php endif; ?>
		</section>
	<?php else : ?>
		<?php
		$wwj_zdguide_article_ids = get_objects_in_term($wwj_zdguide_term->term_id, 'zd_section');

		if (is_wp_error($wwj_zdguide_article_ids) || empty($wwj_zdguide_article_ids)) {
			$wwj_zdguide_articles = array();
		} else {
			$wwj_zdguide_articles = get_posts(
				array(
					'post_type'           => 'zd_article',
					'post_status'         => 'publish',
					'orderby'             => 'title',
					'order'               => 'ASC',
					'posts_per_page'      => -1,
					'ignore_sticky_posts' => true,
					'post__in'            => array_map('intval', $wwj_zdguide_article_ids),
				)
			);
		}
		?>

		<section class="zd-guide-template__section" aria-label="<?php esc_attr_e('Articles', 'zd-guide'); ?>">
			<h2 class="zd-guide-template__section-title"><?php esc_html_e('Articles', 'zd-guide'); ?></h2>

			<?php if (! empty($wwj_zdguide_articles)) : ?>
				<ul class="zd-guide-taxonomy-list">
					<?php foreach ($wwj_zdguide_articles as $wwj_zdguide_article_post) :
						$wwj_zdguide_article_link   = get_permalink($wwj_zdguide_article_post);
						$wwj_zdguide_article_title  = get_the_title($wwj_zdguide_article_post);
						$wwj_zdguide_article_excerpt = trim(wp_strip_all_tags(get_the_excerpt($wwj_zdguide_article_post)));
					?>
						<li class="zd-guide-taxonomy-item">
							<div class="zd-guide-taxonomy-header">
								<a class="zd-guide-taxonomy-name" href="<?php echo esc_url($wwj_zdguide_article_link); ?>">
									<?php echo esc_html($wwj_zdguide_article_title); ?>
								</a>
							</div>
							<?php if (! empty($wwj_zdguide_article_excerpt)) : ?>
								<p class="zd-guide-taxonomy-description"><?php echo esc_html($wwj_zdguide_article_excerpt); ?></p>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="zd-guide-template__empty">
					<?php esc_html_e('No articles are assigned to this section yet.', 'zd-guide'); ?>
				</p>
			<?php endif; ?>
		</section>

	<?php endif; ?>
</main>

<?php get_footer();
