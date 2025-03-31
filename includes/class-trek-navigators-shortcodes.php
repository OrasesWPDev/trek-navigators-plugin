<?php
/**
 * Shortcodes for displaying Trek Navigators
 *
 * @package Trek_Navigators_Plugin
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class to handle Trek Navigator shortcodes.
 */
class Trek_Navigators_Shortcodes {
	/**
	 * Constructor.
	 */
	public function __construct() {
		// Register shortcodes
		add_shortcode('trek_navigators', array($this, 'trek_navigators_grid_shortcode'));
		add_shortcode('trek_navigator', array($this, 'single_trek_navigator_shortcode'));
		// Register shortcode-specific stylesheet
		add_action('wp_enqueue_scripts', array($this, 'register_shortcode_styles'));
	}

	/**
	 * Register shortcode-specific stylesheet
	 */
	public function register_shortcode_styles() {
		$css_file = TREK_NAVIGATORS_PLUGIN_PATH . 'assets/css/trek-navigators-public.css';
		$css_version = file_exists($css_file) ? filemtime($css_file) : TREK_NAVIGATORS_VERSION;
		wp_register_style(
			'trek-navigators-shortcode',
			TREK_NAVIGATORS_PLUGIN_URL . 'assets/css/trek-navigators-public.css',
			array(),
			$css_version
		);
	}

	/**
	 * Shortcode to display Trek Navigators in a grid layout
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function trek_navigators_grid_shortcode($atts) {
		// Enqueue styles
		wp_enqueue_style('trek-navigators-shortcode');
		wp_enqueue_style('trek-navigators-public');
		wp_enqueue_style('trek-navigators-responsive');
		
		// Shortcode attributes
		$atts = shortcode_atts(
			array(
				// Basic display parameters
				'display_type'    => 'grid',     // 'grid' or 'list'
				'columns'         => 3,          // Changed default from 4 to 3 columns in grid view
				'posts_per_page'  => 12,         // Number of navigators to display
				'pagination'      => 'true',     // Changed default from 'false' to 'true'
				// Ordering parameters
				'order'           => 'ASC',      // ASC or DESC
				'orderby'         => 'menu_order title',    // Changed to match archive query
				'meta_key'        => '',         // For ordering by meta_value
				// Filtering parameters
				'category'        => '',         // Filter by category slug or ID
				'tag'             => '',         // Filter by tag slug or ID
				'include'         => '',         // Specific navigator IDs to include
				'exclude'         => '',         // Specific navigator IDs to exclude
				// Layout & content parameters - keeping these for backward compatibility
				'show_image'      => 'true',     // Whether to show the navigator image
				'image_size'      => 'large',    // Changed from 'medium' to 'large' to match archive
				'show_title'      => 'false',    // Display navigator's title (default to false now)
				'show_date'       => 'false',    // Display start date
				'excerpt_length'  => 25,         // Length of excerpt in words
				'show_badge'      => 'false',    // Whether to show PTCB badges image
				// Link parameters
				'link_target'     => '_self',    // Where to open links
				'show_read_more'  => 'false',    // Display "Read More" link (default to false now)
				'read_more_text'  => 'View Profile', // Custom text for read more link
				// Advanced parameters
				'offset'          => 0,          // Number of posts to offset/skip
				'cache'           => 'true',     // Whether to cache results
				'class'           => '',         // Additional CSS classes
			),
			$atts,
			'trek_navigators'
		);
		// Convert string booleans to actual booleans
		foreach (array('pagination', 'show_image', 'show_title', 'show_date', 'show_badge', 'show_read_more', 'cache') as $bool_att) {
			$atts[$bool_att] = filter_var($atts[$bool_att], FILTER_VALIDATE_BOOLEAN);
		}
		// Convert numeric attributes
		$atts['columns'] = intval($atts['columns']);
		$atts['posts_per_page'] = intval($atts['posts_per_page']);
		$atts['excerpt_length'] = intval($atts['excerpt_length']);
		$atts['offset'] = intval($atts['offset']);
		// Start output buffering
		ob_start();
		// Get cached output if caching is enabled
		$cache_key = 'trek_navigators_' . md5(serialize($atts));
		$cached_output = $atts['cache'] ? get_transient($cache_key) : false;
		if ($cached_output !== false) {
			echo $cached_output;
			return ob_get_clean();
		}
		// Get navigators
		$navigators = $this->get_navigators($atts);
		// Check if any navigators exist
		if ($navigators && $navigators->have_posts()) {
			// Add container class based on display type
			$container_class = 'trek-navigators-grid-container';
			// Add custom class if provided
			if (!empty($atts['class'])) {
				$container_class .= ' ' . esc_attr($atts['class']);
			}
			// Output container
			echo '<div class="' . esc_attr($container_class) . '" data-columns="' . esc_attr($atts['columns']) . '">';
			echo '<div class="trek-navigators-grid">';
			while ($navigators->have_posts()) {
				$navigators->the_post();
				// Get navigator data
				$id = get_the_ID();
				$title = get_the_title();
				$permalink = get_permalink();
				$image = '';
				// Get image
				if (has_post_thumbnail()) {
					$image = get_the_post_thumbnail($id, $atts['image_size'], array(
						'class' => 'trek-navigators-grid-image',
						'alt' => esc_attr($title)
					));
				} elseif (function_exists('get_field')) {
					// Try to get header image from ACF
					$header_image = get_field('navigator_header_image', $id);
					if ($header_image && is_array($header_image)) {
						$image_src = $header_image['sizes'][$atts['image_size']] ?? $header_image['url'];
						$image = '<img src="' . esc_url($image_src) . '" alt="' . esc_attr($title) . '" class="trek-navigators-grid-image" />';
					}
				}
				// If no image is available, show a placeholder
				if (empty($image)) {
					$image = '<div class="trek-navigators-no-image"><div class="trek-navigators-placeholder">' . esc_html($title) . '</div></div>';
				}
				
				// Output navigator item - using the same structure as archive template
				?>
                <div class="trek-navigators-grid-item">
                    <a href="<?php echo esc_url($permalink); ?>" class="trek-navigators-grid-link" target="<?php echo esc_attr($atts['link_target']); ?>" title="<?php echo esc_attr($title); ?>">
                        <div class="trek-navigators-grid-image-wrapper">
							<?php echo $image; ?>
                        </div>
                        <?php if ($atts['show_title']): ?>
                        <div class="trek-navigators-grid-title">
                            <h3><?php echo esc_html($title); ?></h3>
                        </div>
                        <?php endif; ?>
                    </a>
                </div>
				<?php
			}
			// Close grid
			echo '</div>';

			// ENHANCED PAGINATION: Added temporary query storage and restoration to fix pagination
			// on custom pages using the shortcode
			if ($atts['pagination'] && $navigators->max_num_pages > 1) {
				global $wp_query;
				$big = 999999999; // Need an unlikely integer

				// Get the current page - check both query vars
				$current_page = get_query_var('paged') ? get_query_var('paged') : 1;
				if (!$current_page && get_query_var('page')) {
					$current_page = get_query_var('page');
				}

				// Store the original query to restore later
				$temp_query = $wp_query;

				// Set our custom query temporarily to generate proper pagination
				$wp_query = $navigators;

				// Get the current URL path for proper base URL
				$current_url = home_url(add_query_arg(array(), $wp_query->request));
				$base_url = trailingslashit(get_pagenum_link(1));
					
				// Remove any existing pagination from the base URL
				$base_url = preg_replace('/page\/[0-9]+\//', '', $base_url);
					
				echo '<div class="trek-navigators-pagination">';
				echo paginate_links(array(
					'base'         => $base_url . 'page/%#%/',
					'format'       => '',
					'current'      => max(1, $current_page),
					'total'        => $navigators->max_num_pages,
					'prev_text'    => '<< Previous',
					'next_text'    => 'Next >>',
					'type'         => 'plain',
					'end_size'     => 2,
					'mid_size'     => 1,
					'show_all'     => false,
					'add_args'     => false,
					'add_fragment' => ''
				));
				echo '</div>';

				// Restore the original query
				$wp_query = $temp_query;
			}

			echo '</div>'; // Close container

			// Reset post data
			wp_reset_postdata();
		} else {
			// No navigators found
			echo '<p class="trek-navigators-none">' . __('No Trek Navigators found.', 'trek-navigators') . '</p>';
		}
		// Get buffer contents and clean buffer
		$output = ob_get_clean();
		// Cache the output if caching is enabled
		if ($atts['cache']) {
			set_transient($cache_key, $output, HOUR_IN_SECONDS);
		}
		return $output;
	}

	/**
	 * Shortcode to display a single Trek Navigator
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function single_trek_navigator_shortcode($atts) {
		// Enqueue styles
		wp_enqueue_style('trek-navigators-shortcode');
		// Shortcode attributes
		$atts = shortcode_atts(
			array(
				'id'                    => 0,          // Trek Navigator ID
				'show_image'            => 'true',     // Whether to show the navigator image
				'show_date'             => 'true',     // Display start date
				'show_video'            => 'true',     // Whether to display the video embed
				'show_content_sections' => 'true',     // Display the content sections
				'show_digital_badges_link' => 'true',  // Show link to digital badges
				'show_more_about'       => 'true',     // Display the "More About" section
				'class'                 => '',         // Additional CSS classes
			),
			$atts,
			'trek_navigator'
		);
		// Convert string booleans to actual booleans
		foreach (array('show_image', 'show_date', 'show_video', 'show_content_sections', 'show_digital_badges_link', 'show_more_about') as $bool_att) {
			$atts[$bool_att] = filter_var($atts[$bool_att], FILTER_VALIDATE_BOOLEAN);
		}
		// Convert ID to integer
		$atts['id'] = intval($atts['id']);
		// Start output buffering
		ob_start();
		// Check if we have a valid ID
		if ($atts['id'] <= 0) {
			echo '<p class="trek-navigators-error">' . __('Error: No Trek Navigator ID specified.', 'trek-navigators') . '</p>';
			return ob_get_clean();
		}
		// Get the navigator post
		$navigator = get_post($atts['id']);
		// Check if the navigator exists and is of the correct post type
		if (!$navigator || 'trek-navigator' !== $navigator->post_type) {
			echo '<p class="trek-navigators-error">' . __('Error: Trek Navigator not found.', 'trek-navigators') . '</p>';
			return ob_get_clean();
		}
		// Set up post data
		setup_postdata($GLOBALS['post'] = $navigator);
		// Container class
		$container_class = 'trek-navigators-single';
		if (!empty($atts['class'])) {
			$container_class .= ' ' . esc_attr($atts['class']);
		}
		// Start output
		?>
        <div class="<?php echo esc_attr($container_class); ?>">
            <h2 class="trek-navigators-single-title"><?php the_title(); ?></h2>
			<?php if ($atts['show_date'] && function_exists('get_field')) : ?>
				<?php $start_date = get_field('navigator_start_date', $navigator->ID); ?>
				<?php if ($start_date) : ?>
                    <div class="trek-navigators-single-meta">
                        <span class="trek-navigators-single-date">
                            <?php echo esc_html($start_date); ?>
                        </span>
                    </div>
				<?php endif; ?>
			<?php endif; ?>
			<?php if ($atts['show_image'] && function_exists('get_field')) : ?>
				<?php $header_image = get_field('navigator_header_image', $navigator->ID); ?>
				<?php if ($header_image && is_array($header_image)) : ?>
                    <div class="trek-navigators-single-image">
                        <img src="<?php echo esc_url($header_image['url']); ?>" alt="<?php the_title_attribute(); ?>" />
                    </div>
				<?php endif; ?>
			<?php endif; ?>
			<?php if ($atts['show_video'] && function_exists('get_field')) : ?>
				<?php $video = get_field('navigator_video_embed', $navigator->ID); ?>
				<?php if ($video) : ?>
                    <div class="trek-navigators-single-video">
						<?php echo $video; ?>
                    </div>
				<?php endif; ?>
			<?php endif; ?>
            <div class="trek-navigators-single-content">
				<?php the_content(); ?>
            </div>
			<?php if ($atts['show_content_sections'] && function_exists('get_field')) : ?>
				<?php $sections = get_field('navigator_content_sections', $navigator->ID); ?>
				<?php if ($sections && is_array($sections)) : ?>
                    <div class="trek-navigators-single-sections">
						<?php foreach ($sections as $section) : ?>
                            <div class="trek-navigators-single-section">
                                <h3 class="trek-navigators-section-title"><?php echo esc_html($section['section_title']); ?></h3>
                                <div class="trek-navigators-section-content">
									<?php echo $section['section_content']; ?>
                                </div>
                            </div>
						<?php endforeach; ?>
                    </div>
				<?php endif; ?>
			<?php endif; ?>
			<?php if ($atts['show_more_about'] && function_exists('get_field')) : ?>
				<?php $more_about_image = get_field('navigator_more_about_image', $navigator->ID); ?>
				<?php if ($more_about_image && is_array($more_about_image)) : ?>
                    <div class="trek-navigators-more-about">
                        <h3><?php _e('More About', 'trek-navigators'); ?></h3>
                        <div class="trek-navigators-more-about-image">
                            <img src="<?php echo esc_url($more_about_image['url']); ?>" alt="<?php _e('More About', 'trek-navigators'); ?>" />
                        </div>
                    </div>
				<?php endif; ?>
			<?php endif; ?>
			<?php if ($atts['show_digital_badges_link'] && function_exists('get_field')) : ?>
				<?php $badges_url = get_field('navigator_digital_badges_url', $navigator->ID); ?>
				<?php if ($badges_url) : ?>
                    <div class="trek-navigators-single-badges-link">
                        <a href="<?php echo esc_url($badges_url); ?>" target="_blank" rel="noopener">
							<?php _e('View Digital Badges', 'trek-navigators'); ?>
                        </a>
                    </div>
				<?php endif; ?>
			<?php endif; ?>
        </div>
		<?php
		// Reset post data
		wp_reset_postdata();
		// Get buffer contents and clean buffer
		return ob_get_clean();
	}

	/**
	 * Get navigators query
	 *
	 * @param array $atts Query parameters.
	 * @return WP_Query Trek Navigators query.
	 */
	private function get_navigators($atts) {
		// Get current page for pagination - check both query vars
		$paged = get_query_var('paged') ? get_query_var('paged') : 1;
		if (!$paged && get_query_var('page')) {
			$paged = get_query_var('page');
		}

		// Query arguments
		$args = array(
			'post_type'      => 'trek-navigator',
			'posts_per_page' => $atts['posts_per_page'],
			'paged'          => $paged,
			'post_status'    => 'publish',
		);

		// Handle custom orderby parameter that includes multiple fields
		if (strpos($atts['orderby'], ' ') !== false) {
			// Multiple orderby parameters (like "menu_order title")
			$orderby_parts = explode(' ', $atts['orderby']);
			$orderby_array = array();
			foreach ($orderby_parts as $part) {
				$orderby_array[$part] = $atts['order'];
			}
			$args['orderby'] = $orderby_array;
		} else {
			// Single orderby parameter
			$args['order'] = $atts['order'];
			$args['orderby'] = $atts['orderby'];
		}

		// Add meta key for ordering if specified
		if ($atts['orderby'] === 'meta_value' && !empty($atts['meta_key'])) {
			$args['meta_key'] = $atts['meta_key'];
		}

		// Add category filter if specified
		if (!empty($atts['category'])) {
			// Check if category is an ID or slug
			if (is_numeric($atts['category'])) {
				$args['cat'] = intval($atts['category']);
			} else {
				$args['category_name'] = $atts['category'];
			}
		}

		// Add tag filter if specified
		if (!empty($atts['tag'])) {
			// Check if tag is an ID or slug
			if (is_numeric($atts['tag'])) {
				$args['tag_id'] = intval($atts['tag']);
			} else {
				$args['tag'] = $atts['tag'];
			}
		}

		// Add specific posts to include
		if (!empty($atts['include'])) {
			$include_ids = array_map('intval', explode(',', $atts['include']));
			$args['post__in'] = $include_ids;
		}

		// Add specific posts to exclude
		if (!empty($atts['exclude'])) {
			$exclude_ids = array_map('intval', explode(',', $atts['exclude']));
			$args['post__not_in'] = $exclude_ids;
		}

		// Create and return query
		return new WP_Query($args);
	}
}
