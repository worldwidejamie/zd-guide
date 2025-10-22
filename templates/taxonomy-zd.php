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
				'meta_query' => array(
					array(
						'key'   => 'zd_category_term_id',
						'value' => $term->term_id,
					),
				),
			)
		);
		$sections = is_array($sections) ? $sections : array();
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
		$article_query = new WP_Query(
			array(
				'post_type'      => 'zd_article',
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
				'posts_per_page' => -1,
				'tax_query'      => array(
					array(
						'taxonomy' => 'zd_section',
						'field'    => 'term_id',
						'terms'    => $term->term_id,
					),
				),
			)
		);
		?>

		<section class="wwj-zdguide-template__section" aria-label="<?php esc_attr_e('Articles', 'wwj-zdguide'); ?>">
			<h2 class="wwj-zdguide-template__section-title"><?php esc_html_e('Articles', 'wwj-zdguide'); ?></h2>

			<?php if ($article_query->have_posts()) : ?>
				<ul class="wwj-zdguide-taxonomy-list">
					<?php
					while ($article_query->have_posts()) :
						$article_query->the_post();
					?>
						<li class="wwj-zdguide-taxonomy-item">
							<div class="wwj-zdguide-taxonomy-header">
								<a class="wwj-zdguide-taxonomy-name" href="<?php the_permalink(); ?>">
									<?php the_title(); ?>
								</a>
							</div>
							<?php if (has_excerpt()) : ?>
								<p class="wwj-zdguide-taxonomy-description"><?php echo esc_html(wp_strip_all_tags(get_the_excerpt())); ?></p>
							<?php endif; ?>
						</li>
					<?php endwhile; ?>
				</ul>
			<?php else : ?>
				<p class="wwj-zdguide-template__empty">
					<?php esc_html_e('No articles are assigned to this section yet.', 'wwj-zdguide'); ?>
				</p>
			<?php endif; ?>
		</section>

		<?php wp_reset_postdata(); ?>
	<?php endif; ?>
</main>

<?php get_footer();
