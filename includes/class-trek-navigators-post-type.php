<?php
/**
 * Custom Post Type Registration
 *
 * @package Trek_Navigators_Plugin
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class to handle registration of the Trek Navigator custom post type.
 */
class Trek_Navigators_Post_Type {

    /**
     * Constructor.
     */
    public function __construct() {
        // Register the custom post type if ACF isn't available
        add_action('init', array($this, 'register'), 5);

        // Register breadcrumbs shortcode
        add_shortcode('trek_navigator_breadcrumbs', array($this, 'breadcrumbs_shortcode'));

        // Add filter to ensure the post type is properly set up with ACF
        add_filter('acf/post_type/registration_args', array($this, 'modify_post_type_args'), 10, 2);
    }

    /**
     * Register Trek Navigator custom post type.
     * This acts as a fallback if ACF is not handling the registration
     */
    public function register() {
        // Debug commented out
        // Only register if ACF isn't handling it
        if (trek_navigators_has_acf() && function_exists('acf_get_post_type_posts')) {
            $acf_post_types = acf_get_post_type_posts();

            // Check if our post type is already registered by ACF
            foreach ($acf_post_types as $acf_post_type) {
                if (isset($acf_post_type['key']) && $acf_post_type['key'] === 'post_type_trek_navigator') {
                    return;
                }
            }
        }

        // If we get here, either ACF isn't active or it hasn't registered our post type
        $labels = array(
            'name'                  => _x('Trek Navigators', 'Post type general name', 'trek-navigators'),
            'singular_name'         => _x('Trek Navigator', 'Post type singular name', 'trek-navigators'),
            'menu_name'             => _x('Trek Navigators', 'Admin Menu text', 'trek-navigators'),
            'all_items'             => __('All Trek Navigators', 'trek-navigators'),
            'edit_item'             => __('Edit Trek Navigator', 'trek-navigators'),
            'view_item'             => __('View Trek Navigator', 'trek-navigators'),
            'view_items'            => __('View Trek Navigators', 'trek-navigators'),
            'add_new_item'          => __('Add New Trek Navigator', 'trek-navigators'),
            'add_new'               => __('Add New Trek Navigator', 'trek-navigators'),
            'new_item'              => __('New Trek Navigator', 'trek-navigators'),
            'parent_item_colon'     => __('Parent Trek Navigator:', 'trek-navigators'),
            'search_items'          => __('Search Trek Navigators', 'trek-navigators'),
            'not_found'             => __('No trek navigators found', 'trek-navigators'),
            'not_found_in_trash'    => __('No trek navigators found in Trash', 'trek-navigators'),
            'archives'              => __('Trek Navigator Archives', 'trek-navigators'),
            'attributes'            => __('Trek Navigator Attributes', 'trek-navigators'),
            'featured_image'        => __('Featured Image', 'trek-navigators'),
            'set_featured_image'    => __('Set featured image', 'trek-navigators'),
            'remove_featured_image' => __('Remove featured image', 'trek-navigators'),
            'use_featured_image'    => __('Use as featured image', 'trek-navigators'),
            'insert_into_item'      => __('Insert into trek navigator', 'trek-navigators'),
            'uploaded_to_this_item' => __('Uploaded to this trek navigator', 'trek-navigators'),
            'filter_items_list'     => __('Filter trek navigators list', 'trek-navigators'),
            'filter_by_date'        => __('Filter trek navigators by date', 'trek-navigators'),
            'items_list_navigation' => __('Trek Navigators list navigation', 'trek-navigators'),
            'items_list'            => __('Trek Navigators list', 'trek-navigators'),
            'item_published'        => __('Trek Navigator published.', 'trek-navigators'),
            'item_published_privately' => __('Trek Navigator published privately.', 'trek-navigators'),
            'item_reverted_to_draft' => __('Trek Navigator reverted to draft.', 'trek-navigators'),
            'item_scheduled'        => __('Trek Navigator scheduled.', 'trek-navigators'),
            'item_updated'          => __('Trek Navigator updated.', 'trek-navigators'),
            'item_link'             => __('Trek Navigator Link', 'trek-navigators'),
            'item_link_description' => __('A link to a trek navigator.', 'trek-navigators'),
        );

        $args = array(
            'labels'              => $labels,
            'description'         => __('Add Navigators', 'trek-navigators'),
            'public'              => true,
            'hierarchical'        => false,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'admin_menu_parent'   => '',
            'show_in_admin_bar'   => false,
            'show_in_nav_menus'   => true,
            'show_in_rest'        => true,
            'rest_base'           => '',
            'rest_namespace'      => 'wp/v2',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'menu_position'       => null,
            'menu_icon'           => 'dashicons-businessperson',
            'capability_type'     => 'post',
            'supports'            => array(
                'title',
                'editor',
                'page-attributes',
                'thumbnail',
                'custom-fields',
                'post-formats'
            ),
            'taxonomies'          => array(
                'category',
                'post_tag',
                'block_categories'
            ),
            'has_archive'         => false, // Ensure this is false to disable the archive template
            'rewrite'             => array(
                'slug'        => 'tech-trek/navigators',
                'with_front'  => false,
                'feeds'       => false,
                'pages'       => true
            ),
            'query_var'           => true,
            'can_export'          => true,
            'delete_with_user'    => false,
        );

        register_post_type('trek-navigator', $args);
    }

    /**
     * Modify the post type registration arguments for ACF
     *
     * @param array $args The post type arguments
     * @param array $post_type The post type settings
     * @return array Modified arguments
     */
    public function modify_post_type_args($args, $post_type) {
        // Only modify our specific post type
        if (isset($post_type['key']) && $post_type['key'] === 'post_type_trek_navigator') {
            // Ensure the rewrite rules are set up correctly
            $args['rewrite'] = array(
                'slug' => 'tech-trek/navigators',
                'with_front' => false,
                'feeds' => false,
                'pages' => true
            );

            // Add any additional modifications needed
            if (!in_array('custom-fields', $args['supports'])) {
                $args['supports'][] = 'custom-fields';
            }
        }

        return $args;
    }

	/**
	 * Breadcrumb shortcode callback
	 */
	public function breadcrumbs_shortcode() {
		if (!is_singular('trek-navigator')) {
			return do_shortcode('[wpseo_breadcrumb]'); // Fallback to Yoast if available
		}

		ob_start();

		$post_title = get_the_title();
		?>
        <span>
        <span><a href="<?php echo home_url(); ?>">Ptcb</a></span>
        <span class="trek-navigators-breadcrumb-divider">/</span>
        <span><a href="<?php echo home_url('/tech-trek/'); ?>">Take the Next Step on your Tech Trek Today</a></span>
        <span class="trek-navigators-breadcrumb-divider">/</span>
        <span><a href="<?php echo home_url('/tech-trek/navigators/'); ?>">Meet the Tech Trek Navigators</a></span>
        <span class="trek-navigators-breadcrumb-divider">/</span>
        <span class="breadcrumb_last" aria-current="page"><?php echo esc_html($post_title); ?></span>
    </span>
		<?php

		return ob_get_clean();
	}
}
