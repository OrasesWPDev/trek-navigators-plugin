<?php
/**
 * The template for displaying Trek Navigator archives
 *
 * @package Trek_Navigators_Plugin
 */

get_header();

// Get current page for pagination
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Custom query to order by attribute:order (menu_order) ascending, then alphabetically
$args = array(
	'post_type'      => 'trek-navigator',
	'posts_per_page' => 12, // Show a reasonable number per page instead of -1
	'paged'          => $paged,
	'orderby'        => array(
		'menu_order' => 'ASC',
		'title'      => 'ASC'
	)
);
// Debug commented out
$navigators_query = new WP_Query($args);
?>

    <main id="main" class="trek-navigators-archive">
        <div class="container">
            <div class="trek-navigators-grid-container">
				<?php if ($navigators_query->have_posts()) : ?>
                    <div class="trek-navigators-grid" data-columns="3">
						<?php while ($navigators_query->have_posts()) : $navigators_query->the_post(); ?>
                            <div class="trek-navigators-grid-item">
                                <a href="<?php the_permalink(); ?>" class="trek-navigators-grid-link" title="<?php echo esc_attr(get_the_title()); ?>">
                                    <div class="trek-navigators-grid-image-wrapper">
										<?php if (has_post_thumbnail()) : ?>
											<?php
											// Use large size and let CSS control dimensions
											the_post_thumbnail('large', array(
												'class' => 'trek-navigators-grid-image',
												'alt' => get_the_title()
											));
											?>
										<?php elseif (function_exists('get_field') && $header_image = get_field('navigator_header_image')) : ?>
											<?php
											// Try to get header image from ACF if available
											$image_url = is_array($header_image) ? ($header_image['sizes']['large'] ?? $header_image['url']) : $header_image;
											?>
                                            <img src="<?php echo esc_url($image_url); ?>"
                                                 alt="<?php echo esc_attr(get_the_title()); ?>"
                                                 class="trek-navigators-grid-image" />
										<?php else : ?>
                                            <div class="trek-navigators-no-image">
                                                <div class="trek-navigators-placeholder">
													<?php echo esc_html(get_the_title()); ?>
                                                </div>
                                            </div>
										<?php endif; ?>
                                    </div>
                                </a>
                            </div>
						<?php endwhile; ?>
                    </div>

					<?php
					// Add pagination if there are more posts than displayed
					if ($navigators_query->max_num_pages > 1) :
						?>
                        <div class="trek-navigators-pagination">
							<?php
							echo paginate_links(array(
								'base'         => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
								'total'        => $navigators_query->max_num_pages,
								'current'      => $paged,
								'format'       => '?paged=%#%',
								'show_all'     => false,
								'type'         => 'plain',
								'end_size'     => 2,
								'mid_size'     => 1,
								'prev_next'    => true,
								'prev_text'    => '<< Previous',
								'next_text'    => 'Next >>',
							));
							?>
                        </div>
					<?php endif; ?>

					<?php wp_reset_postdata(); // Reset post data after custom query ?>

				<?php else : ?>
                    <p class="trek-navigators-none"><?php _e('No Trek Navigators found.', 'trek-navigators'); ?></p>
				<?php endif; ?>
            </div>
        </div>
    </main>

<?php get_footer(); ?>
