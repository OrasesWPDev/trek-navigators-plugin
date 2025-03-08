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

        if (!file_exists($json_file)) {
            if (WP_DEBUG) {
                error_log('Post type JSON file not found: ' . $json_file);
            }
            return;
        }

        $json_content = file_get_contents($json_file);

        if (empty($json_content)) {
            if (WP_DEBUG) {
                error_log('Empty JSON file: ' . $json_file);
            }
            return;
        }

        $post_types = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            if (WP_DEBUG) {
                error_log('JSON decode error: ' . json_last_error_msg() . ' in file: ' . $json_file);
            }
            return;
        }

        // If not an array, try to handle a single post type definition
        if (!is_array($post_types)) {
            if (WP_DEBUG) {
                error_log('Post type JSON is not an array, attempting to process as single object');
            }

            // Try to process as a single object
            if (is_object($post_types) || (is_array($post_types) && isset($post_types['key']))) {
                $post_types = array($post_types);
            } else {
                if (WP_DEBUG) {
                    error_log('Unable to process post type JSON: invalid format');
                }
                return;
            }
        }

        // Process each post type
        foreach ($post_types as $post_type_data) {
            // Skip if not an array or missing required keys
            if (!is_array($post_type_data) || !isset($post_type_data['key'])) {
                if (WP_DEBUG) {
                    error_log('Invalid post type data structure, missing key');
                }
                continue;
            }

            try {
                // Get post type key
                $post_type_key = $post_type_data['key'];

                // Check if this post type already exists in ACF
                $existing = false;
                if (function_exists('acf_get_post_type_post')) {
                    $existing = acf_get_post_type_post($post_type_key);
                }

                if (!$existing) {
                    // Set import info
                    $post_type_data['import_source'] = 'trek-navigators-plugin';
                    $post_type_data['import_date'] = date('Y-m-d H:i:s');

                    if (WP_DEBUG) {
                        error_log('Importing post type: ' . $post_type_data['title']);
                    }

                    // Different versions of ACF might require different approaches
                    if (function_exists('acf_update_post_type')) {
                        acf_update_post_type($post_type_data);

                        if (WP_DEBUG) {
                            error_log('Successfully imported post type via acf_update_post_type()');
                        }
                    } else {
                        // Fallback to native WordPress registration if ACF function not available
                        $this->register_post_type_fallback($post_type_data);
                    }
                } else {
                    if (WP_DEBUG) {
                        error_log('Post type already exists: ' . $post_type_key);
                    }
                }
            } catch (Exception $e) {
                if (WP_DEBUG) {
                    error_log('Error importing post type: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Fallback post type registration using WordPress native functions
     * Used if ACF post type registration fails
     *
     * @param array $post_type_data The post type definition from JSON
     */
    private function register_post_type_fallback($post_type_data) {
        // Only run if this isn't already registered
        if (post_type_exists($post_type_data['post_type'])) {
            return;
        }

        if (WP_DEBUG) {
            error_log('Using fallback post type registration for: ' . $post_type_data['post_type']);
        }

        // Get labels from the data or use defaults
        $labels = isset($post_type_data['labels']) ? $post_type_data['labels'] : array();

        // Basic arguments
        $args = array(
            'labels'             => $labels,
            'description'        => isset($post_type_data['description']) ? $post_type_data['description'] : '',
            'public'             => isset($post_type_data['public']) ? $post_type_data['public'] : true,
            'hierarchical'       => isset($post_type_data['hierarchical']) ? $post_type_data['hierarchical'] : false,
            'exclude_from_search' => isset($post_type_data['exclude_from_search']) ? $post_type_data['exclude_from_search'] : false,
            'publicly_queryable' => isset($post_type_data['publicly_queryable']) ? $post_type_data['publicly_queryable'] : true,
            'show_ui'            => isset($post_type_data['show_ui']) ? $post_type_data['show_ui'] : true,
            'show_in_menu'       => isset($post_type_data['show_in_menu']) ? $post_type_data['show_in_menu'] : true,
            'show_in_admin_bar'  => isset($post_type_data['show_in_admin_bar']) ? $post_type_data['show_in_admin_bar'] : false,
            'show_in_nav_menus'  => isset($post_type_data['show_in_nav_menus']) ? $post_type_data['show_in_nav_menus'] : true,
            'show_in_rest'       => isset($post_type_data['show_in_rest']) ? $post_type_data['show_in_rest'] : true,
            'menu_position'      => isset($post_type_data['menu_position']) ? $post_type_data['menu_position'] : null,
            'menu_icon'          => isset($post_type_data['menu_icon']['value']) ? $post_type_data['menu_icon']['value'] : 'dashicons-businessperson',
            'capability_type'    => 'post',
            'supports'           => isset($post_type_data['supports']) ? $post_type_data['supports'] : array('title', 'editor'),
            'taxonomies'         => isset($post_type_data['taxonomies']) ? $post_type_data['taxonomies'] : array(),
            'has_archive'        => isset($post_type_data['has_archive']) ? $post_type_data['has_archive'] : true,
        );

        // Handle rewrite rules
        if (isset($post_type_data['rewrite'])) {
            $rewrite = $post_type_data['rewrite'];

            $args['rewrite'] = array(
                'slug'       => isset($rewrite['permalink_rewrite']) && $rewrite['permalink_rewrite'] === 'custom' ? 'techtrek/navigators' : $post_type_data['post_type'],
                'with_front' => isset($rewrite['with_front']) ? ($rewrite['with_front'] === '1') : true,
                'feeds'      => isset($rewrite['feeds']) ? ($rewrite['feeds'] === '1') : false,
                'pages'      => isset($rewrite['pages']) ? ($rewrite['pages'] === '1') : true
            );
        }

        // Register the post type
        register_post_type($post_type_data['post_type'], $args);

        if (WP_DEBUG) {
            error_log('Fallback post type registration complete');
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

        if (!file_exists($json_file)) {
            if (WP_DEBUG) {
                error_log('Field group JSON file not found: ' . $json_file);
            }
            return;
        }

        $json_content = file_get_contents($json_file);

        if (empty($json_content)) {
            if (WP_DEBUG) {
                error_log('Empty field group JSON file: ' . $json_file);
            }
            return;
        }

        $field_group = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            if (WP_DEBUG) {
                error_log('Field group JSON decode error: ' . json_last_error_msg());
            }
            return;
        }

        if (!is_array($field_group)) {
            if (WP_DEBUG) {
                error_log('Field group JSON is not an array');
            }
            return;
        }

        // Handle both single field group format and array of field groups
        if (isset($field_group['key'])) {
            // Single field group format
            $this->import_single_field_group($field_group);
        } else {
            // Possibly array of field groups
            foreach ($field_group as $single_group) {
                if (is_array($single_group) && isset($single_group['key'])) {
                    $this->import_single_field_group($single_group);
                }
            }
        }
    }

    /**
     * Import a single field group
     *
     * @param array $field_group Field group definition
     */
    private function import_single_field_group($field_group) {
        // Check if this field group already exists
        $existing = acf_get_field_group($field_group['key']);

        if (!$existing) {
            try {
                // Import the field group
                acf_import_field_group($field_group);

                if (WP_DEBUG) {
                    error_log('Imported field group: ' . $field_group['title']);
                }
            } catch (Exception $e) {
                if (WP_DEBUG) {
                    error_log('Error importing field group: ' . $e->getMessage());
                }
            }
        } else {
            if (WP_DEBUG) {
                error_log('Field group already exists: ' . $field_group['key']);
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