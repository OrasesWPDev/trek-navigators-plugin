<?php
/**
 * Custom Templates Handler
 *
 * @package Trek_Navigators_Plugin
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class to handle custom templates for Trek Navigator post type.
 */
class Trek_Navigators_Templates {
	/**
	 * Constructor.
	 */
	public function __construct() {
		// Add filters for template loading
		add_filter('single_template', array($this, 'single_template'));
		add_filter('archive_template', array($this, 'archive_template'));
		// Add action to override archive with custom page
		add_action('template_redirect', array($this, 'override_archive_with_page'), 99);
	}

	/**
	 * Load custom template for single Trek Navigator
	 *
	 * @param string $template The path of the template to include.
	 * @return string The path of the template to include.
	 */
	public function single_template($template) {
		if (is_singular('trek-navigator')) {
			// Check if a custom template exists in the theme
			$theme_template = locate_template(array('single-trek-navigator.php'));
			// If a theme template exists, use that
			if ($theme_template) {
				return $theme_template;
			}
			// Check if plugin template exists
			$plugin_template = TREK_NAVIGATORS_PLUGIN_PATH . 'templates/single-trek-navigator.php';
			if (file_exists($plugin_template)) {
				return $plugin_template;
			}
			// Fall back to theme's page.php template
			$page_template = locate_template(array('page.php'));
			if ($page_template) {
				return $page_template;
			}
		}
		return $template;
	}

	/**
	 * Load custom template for Trek Navigator archive
	 *
	 * @param string $template The path of the template to include.
	 * @return string The path of the template to include.
	 */
	public function archive_template($template) {
		if (is_post_type_archive('trek-navigator') || is_tax('category') && get_query_var('post_type') === 'trek-navigator') {
			// Check if a custom template exists in the theme
			$theme_template = locate_template(array('archive-trek-navigator.php'));
			// If a theme template exists, use that
			if ($theme_template) {
				return $theme_template;
			}
			// Check if plugin template exists
			$plugin_template = TREK_NAVIGATORS_PLUGIN_PATH . 'templates/archive-trek-navigator.php';
			if (file_exists($plugin_template)) {
				return $plugin_template;
			}
			// Fall back to theme's page.php template for archives too
			$page_template = locate_template(array('page.php'));
			if ($page_template) {
				return $page_template;
			}
		}
		return $template;
	}

	/**
	 * Override archive page with custom page at same URL
	 */
	public function override_archive_with_page() {
		// Check for the correct URL pattern in a more flexible way
		$request_path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
		$navigators_path = '/tech-trek/navigators';
		
		// More aggressive check for the navigator URL
		$is_navigator_url = (
			is_post_type_archive('trek-navigator') || 
			(is_404() && substr($request_path, -strlen($navigators_path)) === $navigators_path) ||
			$request_path === $navigators_path ||
			strpos($request_path, $navigators_path) !== false
		) && !is_singular('trek-navigator');
		
		if ($is_navigator_url) {
			// Find a page with the slug 'navigators' and parent 'tech-trek'
			$custom_page = get_page_by_path('tech-trek/navigators');
			
			if ($custom_page) {
				
				// Get current page for pagination - check both query vars
				$paged = get_query_var('paged') ? get_query_var('paged') : 1;
				if (!$paged && get_query_var('page')) {
					$paged = get_query_var('page');
				}
				
				// Set the query to use this page instead
				global $wp_query;
				$wp_query = new WP_Query([
					'page_id' => $custom_page->ID,
					'paged' => $paged
				]);
				
				// Set the query var for shortcodes to use
				set_query_var('paged', $paged);
				
				// Update globals
				$wp_query->the_post();
				rewind_posts();
				
				// Load page template
				include(get_page_template());
				
				// Stop execution to prevent the archive template from loading
				exit;
			} else {
				error_log('Custom page not found for tech-trek/navigators');
			}
		}
	}

	/**
	 * Get template part with fallback to plugin templates
	 *
	 * This is a helper method that can be used in templates to include partial templates
	 * It first checks the theme directory, then falls back to plugin templates
	 *
	 * @param string $slug The slug name for the generic template.
	 * @param string $name The name of the specialized template.
	 */
	public static function get_template_part($slug, $name = null) {
		// Debug commented out
		// First try to get the template from the theme
		$template = locate_template(array(
			$slug . '-' . $name . '.php',
			$slug . '.php'
		));
		// If not found in theme, look in plugin templates directory
		if (!$template && $name) {
			$template = TREK_NAVIGATORS_PLUGIN_PATH . 'templates/' . $slug . '-' . $name . '.php';
		}
		if (!$template) {
			$template = TREK_NAVIGATORS_PLUGIN_PATH . 'templates/' . $slug . '.php';
		}
		// If we have a template, include it
		if ($template && file_exists($template)) {
			include $template;
		}
	}

	/**
	 * Check if we're on a Trek Navigator template
	 *
	 * @return bool Whether we're on a Trek Navigator template
	 */
	public static function is_trek_navigator_template() {
		return is_singular('trek-navigator') || is_post_type_archive('trek-navigator') ||
		       (is_tax() && get_query_var('post_type') === 'trek-navigator');
	}

	/**
	 * Add body classes for Trek Navigator templates
	 *
	 * @param array $classes Existing body classes.
	 * @return array Modified body classes.
	 */
	public function body_classes($classes) {
		if (is_singular('trek-navigator')) {
			$classes[] = 'trek-navigator-single';
		} elseif (is_post_type_archive('trek-navigator') ||
		          (is_tax() && get_query_var('post_type') === 'trek-navigator')) {
			$classes[] = 'trek-navigator-archive';
		}
		return $classes;
	}
}
