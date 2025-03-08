<?php
/**
 * Plugin Name: Trek Navigators Plugin
 * Plugin URI: https://yourwebsite.com/
 * Description: Custom post type for Trek Navigators with shortcodes for displaying navigator profiles and archives.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: trek-navigators
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package Trek_Navigators_Plugin
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants.
define('TREK_NAVIGATORS_VERSION', '1.0.0');
define('TREK_NAVIGATORS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TREK_NAVIGATORS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TREK_NAVIGATORS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Check if ACF is active - renamed function to avoid conflicts
function trek_navigators_has_acf() {
    return class_exists('ACF');
}

// Plugin initialization
function trek_navigators_plugin_init() {
    // Load plugin textdomain
    load_plugin_textdomain('trek-navigators', false, dirname(plugin_basename(__FILE__)) . '/languages');

    // Include required files
    require_once TREK_NAVIGATORS_PLUGIN_PATH . 'includes/class-trek-navigators-post-type.php';

    // Only load ACF integration if ACF is active
    if (trek_navigators_has_acf()) {
        require_once TREK_NAVIGATORS_PLUGIN_PATH . 'includes/class-trek-navigators-acf.php';
    } else {
        // Admin notice if ACF is not active
        add_action('admin_notices', 'trek_navigators_acf_missing_notice');
    }

    // Load shortcode functionality
    require_once TREK_NAVIGATORS_PLUGIN_PATH . 'includes/class-trek-navigators-shortcodes.php';

    // Load template handling
    require_once TREK_NAVIGATORS_PLUGIN_PATH . 'includes/class-trek-navigators-templates.php';

    // Initialize classes
    new Trek_Navigators_Post_Type();
    if (trek_navigators_has_acf()) {
        new Trek_Navigators_ACF();
    }
    new Trek_Navigators_Shortcodes();
    new Trek_Navigators_Templates();

    // Register assets
    add_action('wp_enqueue_scripts', 'trek_navigators_register_assets');
    add_action('admin_enqueue_scripts', 'trek_navigators_register_admin_assets');
}
add_action('plugins_loaded', 'trek_navigators_plugin_init');

/**
 * Add Help/Documentation page for the plugin
 */
function trek_navigators_add_help_page() {
    add_submenu_page(
        'edit.php?post_type=trek-navigator',  // Parent menu slug
        'Trek Navigators Help',               // Page title
        'How to Use',                      // Menu title
        'edit_posts',                      // Capability
        'trek-navigators-help',               // Menu slug
        'trek_navigators_help_page_content'   // Callback function
    );
}
add_action('admin_menu', 'trek_navigators_add_help_page', 11);

/**
 * Content for help page will be added later
 */
function trek_navigators_help_page_content() {
    // This will be implemented later
}

// Admin notice for missing ACF
function trek_navigators_acf_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('Trek Navigators Plugin requires Advanced Custom Fields PRO to be installed and activated.', 'trek-navigators'); ?></p>
    </div>
    <?php
}

// Register front-end assets with dynamic versioning
function trek_navigators_register_assets() {
    // Main CSS with dynamic versioning
    $css_file = TREK_NAVIGATORS_PLUGIN_PATH . 'assets/css/trek-navigators-public.css';
    $css_version = file_exists($css_file) ? filemtime($css_file) : TREK_NAVIGATORS_VERSION;

    wp_register_style(
        'trek-navigators-public',
        TREK_NAVIGATORS_PLUGIN_URL . 'assets/css/trek-navigators-public.css',
        array(),
        $css_version
    );

    // JS with dynamic versioning
    $js_file = TREK_NAVIGATORS_PLUGIN_PATH . 'assets/js/trek-navigators-public.js';
    $js_version = file_exists($js_file) ? filemtime($js_file) : TREK_NAVIGATORS_VERSION;

    wp_register_script(
        'trek-navigators-public',
        TREK_NAVIGATORS_PLUGIN_URL . 'assets/js/trek-navigators-public.js',
        array('jquery'),
        $js_version,
        true
    );

    // Always enqueue the base styles and scripts
    wp_enqueue_style('trek-navigators-public');
    wp_enqueue_script('trek-navigators-public');
}

// Register admin assets with dynamic versioning
function trek_navigators_register_admin_assets($hook) {
    // Only load on Trek Navigator post type screens
    global $post_type;
    if ('trek-navigator' !== $post_type) {
        return;
    }

    // Admin CSS with dynamic versioning
    $css_file = TREK_NAVIGATORS_PLUGIN_PATH . 'assets/css/trek-navigators-admin.css';
    $css_version = file_exists($css_file) ? filemtime($css_file) : TREK_NAVIGATORS_VERSION;

    wp_enqueue_style(
        'trek-navigators-admin',
        TREK_NAVIGATORS_PLUGIN_URL . 'assets/css/trek-navigators-admin.css',
        array(),
        $css_version
    );

    // Admin JS with dynamic versioning
    $js_file = TREK_NAVIGATORS_PLUGIN_PATH . 'assets/js/trek-navigators-admin.js';
    $js_version = file_exists($js_file) ? filemtime($js_file) : TREK_NAVIGATORS_VERSION;

    wp_enqueue_script(
        'trek-navigators-admin',
        TREK_NAVIGATORS_PLUGIN_URL . 'assets/js/trek-navigators-admin.js',
        array('jquery'),
        $js_version,
        true
    );
}

// Force registration of ACF field groups from JSON
function trek_navigators_force_acf_sync() {
    if (!function_exists('acf_get_field_groups') || !function_exists('acf_add_local_field_group')) {
        return;
    }

    // Path to the ACF JSON file
    $json_file = TREK_NAVIGATORS_PLUGIN_PATH . 'acf-json/group_trek_navigators_fields.json';

    if (file_exists($json_file)) {
        $json_content = file_get_contents($json_file);

        // Check if json content is valid
        if (!$json_content) {
            error_log('Failed to read JSON file: ' . $json_file);
            return;
        }

        $json_data = json_decode($json_content, true);

        // Check if json_decode was successful
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decoding error: ' . json_last_error_msg());
            return;
        }

        // Verify json_data is an array
        if (!is_array($json_data)) {
            error_log('JSON data is not an array');
            return;
        }

        acf_add_local_field_group($json_data);

        if (WP_DEBUG) {
            error_log('Registered field group: ' . (isset($json_data['title']) ? $json_data['title'] : 'Unknown'));
        }
    } else {
        error_log('ACF JSON file not found: ' . $json_file);
    }
}
add_action('acf/init', 'trek_navigators_force_acf_sync', 20);

// Activation hook
register_activation_hook(__FILE__, 'trek_navigators_activate');
function trek_navigators_activate() {
    // Include post type file to register it before flushing
    require_once TREK_NAVIGATORS_PLUGIN_PATH . 'includes/class-trek-navigators-post-type.php';
    $post_type = new Trek_Navigators_Post_Type();
    $post_type->register();

    // Flush rewrite rules
    flush_rewrite_rules();

    // Debug log on activation
    if (WP_DEBUG) {
        error_log('Trek Navigators Plugin activated');
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'trek_navigators_deactivate');
function trek_navigators_deactivate() {
    // Flush rewrite rules on deactivation
    flush_rewrite_rules();

    // Debug log on deactivation
    if (WP_DEBUG) {
        error_log('Trek Navigators Plugin deactivated');
    }
}