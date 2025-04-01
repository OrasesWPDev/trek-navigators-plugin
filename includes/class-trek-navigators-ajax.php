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

		// Get page number
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
		));

		// Set the current page for the query
		$atts['paged'] = $page;

		// Get an instance of the shortcodes class
		$shortcode_instance = new Trek_Navigators_Shortcodes();

		// Get navigators for this page
		$navigators = $shortcode_instance->get_navigators($atts);

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
			echo paginate_links(array(
				'base' => '%_%',
				'format' => '?paged=%#%',
				'current' => max(1, $page),
				'total' => $navigators->max_num_pages,
				'prev_text' => '<< Previous',
				'next_text' => 'Next >>',
				'type' => 'plain',
				'end_size' => 2,
				'mid_size' => 1
			));
		}

		$pagination_html = ob_get_clean();

		// Send JSON response
		wp_send_json_success(array(
			'grid_html' => $grid_html,
			'pagination_html' => $pagination_html
		));
	}
}