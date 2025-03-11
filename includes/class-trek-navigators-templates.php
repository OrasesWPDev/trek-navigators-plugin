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

        // Add shortcode for breadcrumbs (already registered in post type class)
        // This allows it to be available in templates
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
