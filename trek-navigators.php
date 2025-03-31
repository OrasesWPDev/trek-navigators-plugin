<?php
/**
 * Plugin Name: Trek Navigators Plugin
 * Plugin URI: https://orases.com/
 * Description: Custom post type for Trek Navigators with shortcodes for displaying navigator profiles and archives.
 * Version: 1.0.0
 * Author: Orases
 * Author URI: https://orases.com
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

	// Load help/documentation
	require_once TREK_NAVIGATORS_PLUGIN_PATH . 'includes/class-trek-navigators-help.php';

    // Initialize classes
    new Trek_Navigators_Post_Type();
    if (trek_navigators_has_acf()) {
        new Trek_Navigators_ACF();
    }
    new Trek_Navigators_Shortcodes();
    new Trek_Navigators_Templates();
	new Trek_Navigators_Help();

    // Register assets
    add_action('wp_enqueue_scripts', 'trek_navigators_register_assets');
    add_action('admin_enqueue_scripts', 'trek_navigators_register_admin_assets');
}
add_action('plugins_loaded', 'trek_navigators_plugin_init');

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

/**
 * Register and conditionally enqueue front-end assets
 *
 * Only loads assets on pages where Trek Navigator content is displayed
 * to improve site performance.
 */
function trek_navigators_register_assets() {
	// Check if we're on a relevant page before loading assets
	$should_load = false;
	// Check if we're on a Trek Navigator single page
	if (is_singular('trek-navigator')) {
		$should_load = true;
	}
	// Check global $post for shortcodes if available
    elseif (is_a(get_post(), 'WP_Post')) {
		// Use get_post()->post_content directly instead of get_the_content()
		// which requires being in the loop
		$post_content = get_post()->post_content;
		if (has_shortcode($post_content, 'trek_navigators') ||
		    has_shortcode($post_content, 'trek_navigator') ||
		    has_shortcode($post_content, 'trek_navigator_breadcrumbs')) {
			$should_load = true;
		}
	}
	// Apply filter to allow theme/plugins to force load our assets
	$should_load = apply_filters('trek_navigators_load_assets', $should_load);
	// Register assets regardless of whether we'll load them (for potential manual enqueuing)
	// Main CSS with dynamic versioning
	$css_file = TREK_NAVIGATORS_PLUGIN_PATH . 'assets/css/trek-navigators-public.css';
	$css_version = file_exists($css_file) ? filemtime($css_file) : TREK_NAVIGATORS_VERSION;
	wp_register_style(
		'trek-navigators-public',
		TREK_NAVIGATORS_PLUGIN_URL . 'assets/css/trek-navigators-public.css',
		array(),
		$css_version
	);
	// Responsive CSS with dynamic versioning
	$responsive_css_file = TREK_NAVIGATORS_PLUGIN_PATH . 'assets/css/trek-navigators-responsive.css';
	$responsive_css_version = file_exists($responsive_css_file) ? filemtime($responsive_css_file) : TREK_NAVIGATORS_VERSION;
	wp_register_style(
		'trek-navigators-responsive',
		TREK_NAVIGATORS_PLUGIN_URL . 'assets/css/trek-navigators-responsive.css',
		array('trek-navigators-public'),
		$responsive_css_version
	);
	// Shortcode CSS with dynamic versioning
	$shortcode_css_file = TREK_NAVIGATORS_PLUGIN_PATH . 'assets/css/trek-navigators-shortcodes.css';
	$shortcode_css_version = file_exists($shortcode_css_file) ? filemtime($shortcode_css_file) : TREK_NAVIGATORS_VERSION;
	wp_register_style(
		'trek-navigators-shortcode',
		TREK_NAVIGATORS_PLUGIN_URL . 'assets/css/trek-navigators-shortcodes.css',
		array('trek-navigators-public'),
		$shortcode_css_version
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
	// Only enqueue the assets if we're on a relevant page
	if ($should_load) {
		wp_enqueue_style('trek-navigators-public');
		wp_enqueue_style('trek-navigators-responsive');
		wp_enqueue_style('trek-navigators-shortcode');
		wp_enqueue_script('trek-navigators-public');
	}
}

/**
 * Register admin assets with dynamic versioning
 *
 * @param string $hook The current admin page
 */
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
            // error_log('Failed to read JSON file: ' . $json_file);
            return;
        }

        $json_data = json_decode($json_content, true);

        // Check if json_decode was successful
        if (json_last_error() !== JSON_ERROR_NONE) {
            // error_log('JSON decoding error: ' . json_last_error_msg());
            return;
        }

        // Verify json_data is an array
        if (!is_array($json_data)) {
            // error_log('JSON data is not an array');
            return;
        }

        acf_add_local_field_group($json_data);

        /*if (WP_DEBUG) {
            error_log('Registered field group: ' . (isset($json_data['title']) ? $json_data['title'] : 'Unknown'));
        }*/
    } else {
        // error_log('ACF JSON file not found: ' . $json_file);
    }
}
add_action('acf/init', 'trek_navigators_force_acf_sync', 20);

/**
 * Clear cache when Trek Navigator posts are saved, trashed, or modified
 *
 * @param int $post_id The ID of the post being saved
 */
function trek_navigators_clear_cache($post_id) {
    // Skip if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Skip if this isn't a Trek Navigator post
    if (get_post_type($post_id) !== 'trek-navigator') {
        return;
    }

    // Log clearing cache if debugging is enabled
    /*if (WP_DEBUG) {
        error_log('Clearing Trek Navigators cache for post ID: ' . $post_id);
    }*/

    // Method 1: Delete all transients with our prefix using a direct DB query
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_trek_navigators_%'");
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_trek_navigators_%'");

    // Method 2: Also clear any transients that may have been added via the shortcode
    $cache_key_pattern = 'trek_navigators_' . md5('*');
    $pattern_length = strlen($cache_key_pattern) - 1; // Subtract 1 for the wildcard

    // Get all transients
    $all_transients = $wpdb->get_results(
        "SELECT option_name FROM $wpdb->options 
        WHERE option_name LIKE '_transient_trek_navigators_%'"
    );

    // Manually delete transients that match our pattern
    if ($all_transients) {
        foreach ($all_transients as $transient) {
            $transient_name = str_replace('_transient_', '', $transient->option_name);
            delete_transient($transient_name);
        }
    }

    // Also clear any page cache for related URLs
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }

    // Allow other code to run additional cache clearing logic
    do_action('trek_navigators_cache_cleared', $post_id);
}

// Hook into various actions that modify Trek Navigator posts
add_action('save_post', 'trek_navigators_clear_cache');
add_action('edit_post', 'trek_navigators_clear_cache');
add_action('delete_post', 'trek_navigators_clear_cache');
add_action('trashed_post', 'trek_navigators_clear_cache');
add_action('untrashed_post', 'trek_navigators_clear_cache');


/**
 * Add custom rewrite rules for single navigator posts and pagination
 */
function trek_navigators_add_rewrite_rules() {
	// Rule for single navigator posts
	add_rewrite_rule(
		'tech-trek/navigators/([^/]+)/?$',
		'index.php?trek-navigator=$matches[1]',
		'top'
	);
	
	// Rule for pagination on the custom page
	add_rewrite_rule(
		'tech-trek/navigators/page/([0-9]+)/?$',
		'index.php?pagename=tech-trek/navigators&paged=$matches[1]',
		'top'
	);
	
	// Add a rule for the base URL to ensure it goes to the custom page
	add_rewrite_rule(
		'tech-trek/navigators/?$',
		'index.php?pagename=tech-trek/navigators',
		'top'
	);
	
	// Add a rule to handle potential archive URLs
	add_rewrite_rule(
		'tech-trek/navigators/page/?([0-9]{1,})/?$',
		'index.php?pagename=tech-trek/navigators&paged=$matches[1]',
		'top'
	);
}
add_action('init', 'trek_navigators_add_rewrite_rules', 10);

// Modify the existing activation function

// Activation hook
register_activation_hook(__FILE__, 'trek_navigators_activate');
function trek_navigators_activate() {
	// Include post type file to register it before flushing
	require_once TREK_NAVIGATORS_PLUGIN_PATH . 'includes/class-trek-navigators-post-type.php';
	$post_type = new Trek_Navigators_Post_Type();
	$post_type->register();

	// Add the custom rewrite rule before flushing
	trek_navigators_add_rewrite_rules();

	// Flush rewrite rules
	flush_rewrite_rules();

	// Debug log on activation
	/*if (WP_DEBUG) {
		error_log('Trek Navigators Plugin activated');
	}*/
}

// Function to manually flush rewrite rules
function trek_navigators_flush_rules() {
	// Add the custom rewrite rules
	trek_navigators_add_rewrite_rules();
	
	// Flush rewrite rules
	flush_rewrite_rules();
}

// Add an action to flush rules when the plugin is loaded
// This ensures rules are updated without requiring deactivation/reactivation
add_action('init', 'trek_navigators_flush_rules', 20);

// Deactivation hook
register_deactivation_hook(__FILE__, 'trek_navigators_deactivate');
function trek_navigators_deactivate() {
    // Flush rewrite rules on deactivation
    flush_rewrite_rules();

    // Debug log on deactivation
    /*if (WP_DEBUG) {
        error_log('Trek Navigators Plugin deactivated');
    }*/
}
