<?php

/**
 * Server-side render for the Help Center Search block.
 *
 * @package Wwj_Zdguide
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$placeholder  = isset($attributes['placeholder']) ? sanitize_text_field($attributes['placeholder']) : __('Search help articles', 'zd-guide');
$show_excerpt = ! empty($attributes['showExcerpt']);
$results_per  = isset($attributes['resultsPerPage']) ? max(1, min(absint($attributes['resultsPerPage']), 20)) : 5;
$unique_id    = wp_unique_id('wp-block-search__input-');

$endpoint_url = rest_url('zd-guide/v1/search');

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class'               => 'wp-block-search wp-block-search__button-outside zd-guide-help-center-search',
		'action'              => esc_url(home_url('/')),
		'role'                => 'search',
		'method'              => 'get',
		'aria-label'          => __('Search Zendesk help center articles', 'zd-guide'),
		'data-endpoint'       => esc_url($endpoint_url),
		'data-results-per-page' => (string) $results_per,
		'data-show-excerpt'   => $show_excerpt ? '1' : '0',
	)
);

?>
<form <?php echo wp_kses_post($wrapper_attrs); ?>>
	<label class="wp-block-search__label screen-reader-text" for="<?php echo esc_attr($unique_id); ?>">
		<?php esc_html_e('Search help articles', 'zd-guide'); ?>
	</label>
	<div class="wp-block-search__inside-wrapper">
		<input
			type="search"
			id="<?php echo esc_attr($unique_id); ?>"
			class="wp-block-search__input"
			name="s"
			placeholder="<?php echo esc_attr($placeholder); ?>"
			autocapitalize="none"
			autocomplete="off"
			spellcheck="false" />
		<button type="submit" class="wp-block-search__button wp-element-button">
			<?php esc_html_e('Search', 'zd-guide'); ?>
		</button>
	</div>
	<input type="hidden" name="post_type" value="zd_article" />
	<div
		class="zd-guide-search-results"
		hidden
		aria-live="polite"
		data-label-searching="<?php echo esc_attr__('Searching…', 'zd-guide'); ?>"
		data-label-empty="<?php echo esc_attr__('No results found for your query.', 'zd-guide'); ?>"
		data-label-error="<?php echo esc_attr__('Something went wrong. Please try again.', 'zd-guide'); ?>"
		data-label-open="<?php echo esc_attr__('Open article', 'zd-guide'); ?>"></div>
</form>
