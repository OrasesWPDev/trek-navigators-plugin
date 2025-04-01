<?php
/**
 * AJAX Handler for Trek Navigators
 *
 * @package Trek_Navigators_Plugin
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class to handle AJAX functionality for Trek Navigator pagination.
 */
class Trek_Navigators_Ajax {
	/**
	 * Constructor.
	 */
	public function __construct() {
		// Register scripts
		add_action('wp_enqueue_scripts', array($this, 'register_scripts'));

		// AJAX handlers
		add_action('wp_ajax_trek_navigators_load_page', array($this, 'load_page'));
		add_action('wp_ajax_nopriv_trek_navigators_load_page', array($this, 'load_page'));
	}

	/**
	 * Register and enqueue AJAX pagination script
	 */
	public function register_scripts() {
		// Only register/enqueue on pages with the shortcode
		if (!is_admin() && is_a(get_post(), 'WP_Post') &&
		    (has_shortcode(get_post()->post_content, 'trek_navigators') ||
		     has_shortcode(get_post()->post_content, 'trek_navigator'))) {

			$js_file = TREK_NAVIGATORS_PLUGIN_PATH . 'assets/js/trek-navigators-ajax-pagination.js';
			$js_version = file_exists($js_file) ? filemtime($js_file) : TREK_NAVIGATORS_VERSION;

			wp_register_script(
				'trek-navigators-ajax-pagination',
				TREK_NAVIGATORS_PLUGIN_URL . 'assets/js/trek-navigators-ajax-pagination.js',
				array('jquery'),
				$js_version,
				true
			);

			wp_localize_script('trek-navigators-ajax-pagination', 'trek_navigators_ajax', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('trek_navigators_ajax_nonce')
			));

			wp_enqueue_script('trek-navigators-ajax-pagination');
		}
	}

	/**
	 * AJAX handler for loading Trek Navigator pages
	 */
	public function load_page() {
		// Log the request for debugging
		error_log('Trek Navigators AJAX request received: ' . print_r($_POST, true));

		// Verify nonce
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'trek_navigators_ajax_nonce')) {
			error_log('Trek Navigators AJAX: Invalid nonce');
			wp_send_json_error('Invalid security token');
			return;
		}

		// Get page number - this is critical
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;

		// Get shortcode attributes from request or use defaults
		$atts = isset($_POST['shortcode_atts']) ? $_POST['shortcode_atts'] : array();

		// Ensure we have minimum required attributes
		$atts = wp_parse_args($atts, array(
			'display_type' => 'grid',
			'columns' => 3,
			'posts_per_page' => 12,
			'pagination' => true,
			'layout' => 'archive',
			'orderby' => 'menu_order title',
			'order' => 'ASC',
			'show_title' => false,
			'image_size' => 'large',
			'link_target' => '_self',
			'offset' => 0  // Add default offset
		));

		// IMPORTANT: Set up a direct WP_Query instead of using the shortcode class
		$args = array(
			'post_type'      => 'trek-navigator',
			'posts_per_page' => $atts['posts_per_page'],
			'paged'          => $page,  // Use the page number from the AJAX request
			'post_status'    => 'publish',
		);

		// Set ordering based on layout or explicit parameters
		if ($atts['layout'] === 'row') {
			$args['orderby'] = 'date';
			$args['order'] = 'DESC';
		} else {
			// Handle multi-parameter orderby
			if (strpos($atts['orderby'], ' ') !== false) {
				$orderby_parts = explode(' ', $atts['orderby']);
				$orderby_array = array();
				foreach ($orderby_parts as $part) {
					$orderby_array[$part] = $atts['order'];
				}
				$args['orderby'] = $orderby_array;
			} else {
				$args['orderby'] = $atts['orderby'];
				$args['order'] = $atts['order'];
			}
		}

		// Add filtering options based on shortcode attributes
		if (!empty($atts['category'])) {
			if (is_numeric($atts['category'])) {
				$args['cat'] = intval($atts['category']);
			} else {
				$args['category_name'] = $atts['category'];
			}
		}

		if (!empty($atts['tag'])) {
			if (is_numeric($atts['tag'])) {
				$args['tag_id'] = intval($atts['tag']);
			} else {
				$args['tag'] = $atts['tag'];
			}
		}

		if (!empty($atts['include'])) {
			$args['post__in'] = array_map('intval', explode(',', $atts['include']));
		}

		if (!empty($atts['exclude'])) {
			$args['post__not_in'] = array_map('intval', explode(',', $atts['exclude']));
		}

		if (!empty($atts['offset']) && intval($atts['offset']) > 0) {
			// When using pagination, adjust the offset
			if ($page > 1) {
				$args['offset'] = intval($atts['offset']) + (($page - 1) * intval($atts['posts_per_page']));
			} else {
				$args['offset'] = intval($atts['offset']);
			}
		}

		// Debug the query arguments
		error_log('Trek Navigators AJAX query args: ' . print_r($args, true));

		// Create the query
		$navigators = new WP_Query($args);

		// Debug the query results
		error_log('Trek Navigators found posts: ' . $navigators->found_posts . ', page ' . $page . ' of ' . $navigators->max_num_pages);

		ob_start();

		// Output grid HTML
		if ($navigators && $navigators->have_posts()) {
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
						$image_src = isset($header_image['sizes'][$atts['image_size']])
							? $header_image['sizes'][$atts['image_size']]
							: $header_image['url'];
						$image = '<img src="' . esc_url($image_src) . '" alt="' . esc_attr($title) . '" class="trek-navigators-grid-image" />';
					}
				}

				// If no image is available, show a placeholder
				if (empty($image)) {
					$image = '<div class="trek-navigators-no-image"><div class="trek-navigators-placeholder">' . esc_html($title) . '</div></div>';
				}

				// Output navigator item
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

			// Reset post data
			wp_reset_postdata();
		}

		$grid_html = ob_get_clean();

// Generate pagination HTML
		ob_start();

		if ($navigators && $navigators->max_num_pages > 1) {
			// Create pagination links that will work with our AJAX handler
			$output = '';

			// Previous page link
			if ($page > 1) {
				$output .= '<a class="prev page-numbers" href="' . esc_url(add_query_arg('paged', ($page - 1), home_url($_SERVER['REQUEST_URI']))) . '"><< Previous</a>';
			} else {
				$output .= '<span class="prev page-numbers disabled"><< Previous</span>';
			}

			// Page links
			$total_pages = $navigators->max_num_pages;
			$end_size = 2;
			$mid_size = 1;

			// Calculate which pages to show
			$dots1 = $dots2 = false;
			for ($i = 1; $i <= $total_pages; $i++) {
				if ($i === $page) {
					// Current page
					$output .= '<span aria-current="page" class="page-numbers current">' . $i . '</span>';
				} else if (
					$i <= $end_size ||
					($i >= $page - $mid_size && $i <= $page + $mid_size) ||
					$i > $total_pages - $end_size
				) {
					// Beginning, around current, or end pages
					$url = add_query_arg('paged', $i, home_url($_SERVER['REQUEST_URI']));
					$output .= '<a class="page-numbers" href="' . esc_url($url) . '">' . $i . '</a>';
				} else if ($i < $page && !$dots1) {
					// Dots before current
					$output .= '<span class="page-numbers dots">…</span>';
					$dots1 = true;
				} else if ($i > $page && !$dots2) {
					// Dots after current
					$output .= '<span class="page-numbers dots">…</span>';
					$dots2 = true;
				}
			}

			// Next page link
			if ($page < $total_pages) {
				$output .= '<a class="next page-numbers" href="' . esc_url(add_query_arg('paged', ($page + 1), home_url($_SERVER['REQUEST_URI']))) . '">Next >></a>';
			} else {
				$output .= '<span class="next page-numbers disabled">Next >></span>';
			}

			echo $output;
		}

		$pagination_html = ob_get_clean();

		// Send JSON response
		wp_send_json_success(array(
			'grid_html' => $grid_html,
			'pagination_html' => $pagination_html
		));
	}
}