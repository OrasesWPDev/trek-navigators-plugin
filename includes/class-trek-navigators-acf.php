<?php
/**
 * ACF Field Group Registration
 *
 * @package Trek_Navigators_Plugin
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class to handle registration and synchronization of ACF field groups.
 */
class Trek_Navigators_ACF {

    /**
     * Constructor.
     */
    public function __construct() {
        // Register local JSON save point
        add_filter('acf/settings/save_json', array($this, 'acf_json_save_point'));

        // Register local JSON load point
        add_filter('acf/settings/load_json', array($this, 'acf_json_load_point'));

        // Hook into ACF initialization - important for correct loading order
        add_action('acf/init', array($this, 'initialize_acf_sync'), 5);

        // Add a notice if there are field groups that need syncing
        add_action('admin_notices', array($this, 'sync_admin_notice'));

        // Add an action to handle syncing
        add_action('admin_post_trek_navigators_sync_acf', array($this, 'handle_sync_action'));

        // Add logging for debugging
        if (WP_DEBUG) {
            error_log('Trek_Navigators_ACF initialized');
        }
    }

    /**
     * Define ACF JSON save point
     *
     * @param string $path The path to save ACF JSON files.
     * @return string The modified path.
     */
    public function acf_json_save_point($path) {
        // Create acf-json directory in plugin if it doesn't exist
        $plugin_acf_path = TREK_NAVIGATORS_PLUGIN_PATH . 'acf-json';

        if (!file_exists($plugin_acf_path)) {
            mkdir($plugin_acf_path, 0755, true);

            if (WP_DEBUG) {
                error_log('Created ACF JSON directory at: ' . $plugin_acf_path);
            }
        }

        // Set save point to plugin directory
        return $plugin_acf_path;
    }

    /**
     * Register ACF JSON load point
     *
     * @param array $paths Array of paths ACF will load JSON files from.
     * @return array Modified array of paths.
     */
    public function acf_json_load_point($paths) {
        // Ensure paths is an array
        if (!is_array($paths)) {
            $paths = array();
        }

        // Add our path to the load paths
        $paths[] = TREK_NAVIGATORS_PLUGIN_PATH . 'acf-json';

        if (WP_DEBUG) {
            error_log('Added ACF JSON load path: ' . TREK_NAVIGATORS_PLUGIN_PATH . 'acf-json');
        }

        return $paths;
    }

    /**
     * Initialize ACF sync
     */
    public function initialize_acf_sync() {
        // Check if we're in the admin and have ACF functions
        if (!is_admin() || !function_exists('acf_get_field_group')) {
            return;
        }

        // Import post type definitions
        $this->import_post_types();

        // Import field groups
        $this->import_field_groups();
    }

    /**
     * Import post type definitions from JSON
     */
    private function import_post_types() {
        if (!function_exists('acf_get_post_type_post') || !function_exists('acf_update_post_type')) {
            return;
        }

        $json_file = TREK_NAVIGATORS_PLUGIN_PATH . 'acf-json/post_type_trek_navigator.json';

        if (file_exists($json_file)) {
            $json_content = file_get_contents($json_file);
            $post_types = json_decode($json_content, true);

            if (is_array($post_types)) {
                foreach ($post_types as $post_type) {
                    // Check if this post type already exists in ACF
                    $existing = acf_get_post_type_post($post_type['key']);

                    if (!$existing) {
                        // Set import info
                        $post_type['import_source'] = 'trek-navigators-plugin';
                        $post_type['import_date'] = date('Y-m-d H:i:s');

                        // Import the post type - pass the entire array as expected in newer ACF versions
                        acf_update_post_type($post_type);

                        if (WP_DEBUG) {
                            error_log('Imported post type: ' . $post_type['title']);
                        }
                    }
                }
            }
        }
    }

    /**
     * Import field groups from JSON
     */
    private function import_field_groups() {
        if (!function_exists('acf_get_field_group') || !function_exists('acf_import_field_group')) {
            return;
        }

        $json_file = TREK_NAVIGATORS_PLUGIN_PATH . 'acf-json/group_trek_navigators_fields.json';

        if (file_exists($json_file)) {
            $json_content = file_get_contents($json_file);
            $field_group = json_decode($json_content, true);

            if (is_array($field_group) && isset($field_group['key'])) {
                // Check if this field group already exists
                $existing = acf_get_field_group($field_group['key']);

                if (!$existing) {
                    // Import the field group
                    acf_import_field_group($field_group);

                    if (WP_DEBUG) {
                        error_log('Imported field group: ' . $field_group['title']);
                    }
                }
            }
        }
    }

    /**
     * Display admin notice if there are field groups that need syncing
     */
    public function sync_admin_notice() {
        // Only show on ACF admin pages
        $screen = get_current_screen();
        if (!$screen || !is_object($screen) || !isset($screen->id) || strpos($screen->id, 'acf-field-group') === false) {
            return;
        }

        $sync_required = $this->get_field_groups_requiring_sync();

        if (!empty($sync_required) && is_array($sync_required)) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <?php
                    printf(
                        _n(
                            'There is %d Trek Navigators field group that requires synchronization.',
                            'There are %d Trek Navigators field groups that require synchronization.',
                            count($sync_required),
                            'trek-navigators'
                        ),
                        count($sync_required)
                    );
                    ?>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=trek_navigators_sync_acf'), 'trek_navigators_sync_acf')); ?>" class="button button-primary">
                        <?php _e('Sync Field Groups', 'trek-navigators'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Get field groups that require synchronization
     *
     * @return array Array of field groups that require synchronization
     */
    private function get_field_groups_requiring_sync() {
        if (!function_exists('acf_get_field_group')) {
            return array();
        }

        $sync_required = array();
        $json_file = TREK_NAVIGATORS_PLUGIN_PATH . 'acf-json/group_trek_navigators_fields.json';

        if (file_exists($json_file)) {
            $json_content = file_get_contents($json_file);
            $json_group = json_decode($json_content, true);

            if (is_array($json_group) && isset($json_group['key'])) {
                // Get database version
                $db_group = acf_get_field_group($json_group['key']);

                // If DB version doesn't exist or has a different modified time, it needs sync
                if (!$db_group) {
                    $sync_required[] = $json_group;
                } else if (isset($json_group['modified']) && isset($db_group['modified']) && $db_group['modified'] != $json_group['modified']) {
                    $sync_required[] = $json_group;
                }
            }
        }

        return $sync_required;
    }

    /**
     * Handle the synchronization action
     */
    public function handle_sync_action() {
        // Security check - use a more inclusive approach for capabilities
        if (!current_user_can('manage_acf') && !current_user_can('edit_posts') && !current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'trek-navigators'));
        }

        // Verify nonce for security
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'trek_navigators_sync_acf')) {
            wp_die(__('Security check failed.', 'trek-navigators'));
        }

        // Import post types
        $this->import_post_types();

        // Import field groups
        $this->import_field_groups();

        // Redirect to the main ACF field groups list
        wp_redirect(add_query_arg(array(
            'post_type' => 'acf-field-group',
            'sync' => 'complete',
            'count' => 1
        ), admin_url('edit.php')));

        exit;
    }
}