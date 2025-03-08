<?php
/**
 * Help Documentation Page
 *
 * @package Trek_Navigators_Plugin
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class to handle Trek Navigator help documentation.
 */
class Trek_Navigators_Help {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Add submenu page
		add_action('admin_menu', array($this, 'add_help_page'), 11);

		// Add admin-specific styles
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
	}

	/**
	 * Add Help/Documentation page for the plugin
	 */
	public function add_help_page() {
		add_submenu_page(
			'edit.php?post_type=trek-navigator',  // Parent menu slug
			'Trek Navigators Help',              // Page title
			'How to Use',                        // Menu title
			'edit_posts',                        // Capability
			'trek-navigators-help',             // Menu slug
			array($this, 'help_page_content')    // Callback function
		);
	}

	/**
	 * Enqueue styles for admin help page
	 *
	 * @param string $hook Current admin page
	 */
	public function enqueue_admin_styles($hook) {
		// Only load on our help page
		if ('trek-navigator_page_trek-navigators-help' !== $hook) {
			return;
		}

		// Add inline styles for help page
		wp_add_inline_style('wp-admin', $this->get_admin_styles());
	}

	/**
 * Get admin styles for help page
 *
 * @return string CSS styles
 */
private function get_admin_styles() {
    return '
        .trek-navigators-help-wrap {
            max-width: 1300px; /* Increased from 1200px */
            margin: 20px 20px 0 0;
        }
        .trek-navigators-help-header {
            background: #fff;
            padding: 20px;
            border-radius: 3px;
            margin-bottom: 20px;
            border-left: 4px solid #2c6c3e;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .trek-navigators-help-section {
            background: #fff;
            padding: 20px;
            border-radius: 3px;
            margin-bottom: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            overflow-x: auto; /* Added for table overflow */
        }
        .trek-navigators-help-section h2 {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .trek-navigators-help-section h3 {
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }
        .trek-navigators-help-section table {
            border-collapse: collapse;
            width: 100%;
            margin: 1em 0;
            table-layout: fixed;
        }
        .trek-navigators-help-section table th,
        .trek-navigators-help-section table td {
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
            vertical-align: top;
            word-wrap: break-word;
            word-break: break-word; /* Added to break long words */
            hyphens: auto; /* Added for better text wrapping */
        }
        /* Adjust column widths */
        .trek-navigators-help-section table th:nth-child(1), 
        .trek-navigators-help-section table td:nth-child(1) {
            width: 15%; /* Parameter column */
        }
        .trek-navigators-help-section table th:nth-child(2), 
        .trek-navigators-help-section table td:nth-child(2) {
            width: 25%; /* Description column */
        }
        .trek-navigators-help-section table th:nth-child(3), 
        .trek-navigators-help-section table td:nth-child(3) {
            width: 10%; /* Default column */
        }
        .trek-navigators-help-section table th:nth-child(4), 
        .trek-navigators-help-section table td:nth-child(4) {
            width: 20%; /* Options column */
        }
        .trek-navigators-help-section table th:nth-child(5), 
        .trek-navigators-help-section table td:nth-child(5) {
            width: 30%; /* Examples column */
        }
        .trek-navigators-help-section table th {
            background-color: #f8f8f8;
            font-weight: 600;
        }
        .trek-navigators-help-section table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .trek-navigators-help-section code {
            background: #f8f8f8;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 13px;
            color: #0073aa;
            display: inline-block;
            max-width: 100%; /* Ensure code blocks does not overflow */
            overflow-wrap: break-word; /* Allow long code to wrap */
            white-space: normal; /* Allow long code to wrap */
        }
        .trek-navigators-shortcode-example {
            background: #f8f8f8;
            padding: 15px;
            border-left: 4px solid #0073aa;
            font-family: monospace;
            margin: 10px 0;
            overflow-x: auto; /* Allow scrolling for very long examples */
            white-space: pre-wrap; /* Better wrapping for code examples */
            word-break: break-word; /* Break words if necessary */
        }
    ';
}

	/**
	 * Content for help page
	 */
	public function help_page_content() {
		?>
		<div class="wrap trek-navigators-help-wrap">
			<div class="trek-navigators-help-header">
				<h1><?php esc_html_e('Trek Navigators - Documentation', 'trek-navigators'); ?></h1>
				<p><?php esc_html_e('This page provides documentation on how to use Trek Navigators shortcodes and features.', 'trek-navigators'); ?></p>
			</div>

			<!-- Overview Section -->
			<div class="trek-navigators-help-section">
				<h2><?php esc_html_e('Overview', 'trek-navigators'); ?></h2>
				<p><?php esc_html_e('Trek Navigators allows you to create and display navigator profiles on your site. The plugin provides two main shortcodes:', 'trek-navigators'); ?></p>
				<ul>
					<li><code>[trek_navigators]</code> - <?php esc_html_e('Display multiple navigators in a grid or list layout', 'trek-navigators'); ?></li>
					<li><code>[trek_navigator]</code> - <?php esc_html_e('Display a single navigator\'s profile', 'trek-navigators'); ?></li>
				</ul>
			</div>

			<!-- Multiple Navigators Shortcode Section -->
			<div class="trek-navigators-help-section">
				<h2><?php esc_html_e('Shortcode: [trek_navigators]', 'trek-navigators'); ?></h2>
				<p><?php esc_html_e('This shortcode displays a grid or list of Trek Navigators with various customization options.', 'trek-navigators'); ?></p>

				<h3><?php esc_html_e('Basic Usage', 'trek-navigators'); ?></h3>
				<div class="trek-navigators-shortcode-example">
					[trek_navigators]
				</div>

				<h3><?php esc_html_e('Display Options', 'trek-navigators'); ?></h3>
				<table>
					<tr>
						<th><?php esc_html_e('Parameter', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Description', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Default', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Options', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Examples', 'trek-navigators'); ?></th>
					</tr>
					<tr>
						<td><code>display_type</code></td>
						<td><?php esc_html_e('Layout type for navigators', 'trek-navigators'); ?></td>
						<td><code>grid</code></td>
						<td><code>grid</code>, <code>list</code></td>
						<td><code>display_type="list"</code></td>
					</tr>
					<tr>
						<td><code>columns</code></td>
						<td><?php esc_html_e('Number of columns in grid view', 'trek-navigators'); ?></td>
						<td><code>3</code></td>
						<td><?php esc_html_e('any number (1-6 recommended)', 'trek-navigators'); ?></td>
						<td><code>columns="4"</code></td>
					</tr>
					<tr>
						<td><code>posts_per_page</code></td>
						<td><?php esc_html_e('Number of navigators to display', 'trek-navigators'); ?></td>
						<td><code>12</code></td>
						<td><?php esc_html_e('any number, -1 for all', 'trek-navigators'); ?></td>
						<td><code>posts_per_page="6"</code><br><code>posts_per_page="-1"</code></td>
					</tr>
					<tr>
						<td><code>pagination</code></td>
						<td><?php esc_html_e('Whether to show pagination', 'trek-navigators'); ?></td>
						<td><code>false</code></td>
						<td><code>true</code>, <code>false</code></td>
						<td><code>pagination="true"</code></td>
					</tr>
				</table>

				<h3><?php esc_html_e('Ordering Parameters', 'trek-navigators'); ?></h3>
				<table>
					<tr>
						<th><?php esc_html_e('Parameter', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Description', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Default', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Options', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Examples', 'trek-navigators'); ?></th>
					</tr>
					<tr>
						<td><code>order</code></td>
						<td><?php esc_html_e('Sort order', 'trek-navigators'); ?></td>
						<td><code>ASC</code></td>
						<td><code>ASC</code>, <code>DESC</code></td>
						<td><code>order="DESC"</code></td>
					</tr>
					<tr>
						<td><code>orderby</code></td>
						<td><?php esc_html_e('Field to order by', 'trek-navigators'); ?></td>
						<td><code>title</code></td>
						<td><code>date</code>, <code>title</code>, <code>menu_order</code>, <code>rand</code>, <code>meta_value</code></td>
						<td><code>orderby="date"</code><br><code>orderby="rand"</code></td>
					</tr>
					<tr>
						<td><code>meta_key</code></td>
						<td><?php esc_html_e('Custom field to order by (when orderby is meta_value)', 'trek-navigators'); ?></td>
						<td><code>''</code></td>
						<td><?php esc_html_e('any ACF field name', 'trek-navigators'); ?></td>
						<td><code>orderby="meta_value" meta_key="navigator_start_date"</code></td>
					</tr>
				</table>

				<h3><?php esc_html_e('Filtering Parameters', 'trek-navigators'); ?></h3>
				<table>
					<tr>
						<th><?php esc_html_e('Parameter', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Description', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Default', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Options', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Examples', 'trek-navigators'); ?></th>
					</tr>
					<tr>
						<td><code>category</code></td>
						<td><?php esc_html_e('Filter by category', 'trek-navigators'); ?></td>
						<td><code>''</code></td>
						<td><?php esc_html_e('category slug or ID', 'trek-navigators'); ?></td>
						<td><code>category="featured"</code><br><code>category="5"</code></td>
					</tr>
					<tr>
						<td><code>tag</code></td>
						<td><?php esc_html_e('Filter by tag', 'trek-navigators'); ?></td>
						<td><code>''</code></td>
						<td><?php esc_html_e('tag slug or ID', 'trek-navigators'); ?></td>
						<td><code>tag="senior"</code><br><code>tag="8"</code></td>
					</tr>
					<tr>
						<td><code>include</code></td>
						<td><?php esc_html_e('Include only specific navigators', 'trek-navigators'); ?></td>
						<td><code>''</code></td>
						<td><?php esc_html_e('IDs separated by commas', 'trek-navigators'); ?></td>
						<td><code>include="42,51,90"</code></td>
					</tr>
					<tr>
						<td><code>exclude</code></td>
						<td><?php esc_html_e('Exclude specific navigators', 'trek-navigators'); ?></td>
						<td><code>''</code></td>
						<td><?php esc_html_e('IDs separated by commas', 'trek-navigators'); ?></td>
						<td><code>exclude="42,51,90"</code></td>
					</tr>
				</table>

				<h3><?php esc_html_e('Content Parameters', 'trek-navigators'); ?></h3>
				<table>
					<tr>
						<th><?php esc_html_e('Parameter', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Description', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Default', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Options', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Examples', 'trek-navigators'); ?></th>
					</tr>
					<tr>
						<td><code>show_image</code></td>
						<td><?php esc_html_e('Whether to show the navigator image', 'trek-navigators'); ?></td>
						<td><code>true</code></td>
						<td><code>true</code>, <code>false</code></td>
						<td><code>show_image="false"</code></td>
					</tr>
					<tr>
						<td><code>image_size</code></td>
						<td><?php esc_html_e('Size of the image', 'trek-navigators'); ?></td>
						<td><code>medium</code></td>
						<td><code>thumbnail</code>, <code>medium</code>, <code>large</code>, <code>full</code></td>
						<td><code>image_size="thumbnail"</code><br><code>image_size="large"</code></td>
					</tr>
					<tr>
						<td><code>show_title</code></td>
						<td><?php esc_html_e('Whether to show the navigator title', 'trek-navigators'); ?></td>
						<td><code>true</code></td>
						<td><code>true</code>, <code>false</code></td>
						<td><code>show_title="false"</code></td>
					</tr>
					<tr>
						<td><code>show_date</code></td>
						<td><?php esc_html_e('Whether to show the start date', 'trek-navigators'); ?></td>
						<td><code>false</code></td>
						<td><code>true</code>, <code>false</code></td>
						<td><code>show_date="true"</code></td>
					</tr>
					<tr>
						<td><code>excerpt_length</code></td>
						<td><?php esc_html_e('Length of excerpt in words', 'trek-navigators'); ?></td>
						<td><code>25</code></td>
						<td><?php esc_html_e('any number', 'trek-navigators'); ?></td>
						<td><code>excerpt_length="15"</code><br><code>excerpt_length="50"</code></td>
					</tr>
					<tr>
						<td><code>show_badge</code></td>
						<td><?php esc_html_e('Whether to show PTCB badges image', 'trek-navigators'); ?></td>
						<td><code>false</code></td>
						<td><code>true</code>, <code>false</code></td>
						<td><code>show_badge="true"</code></td>
					</tr>
					<tr>
						<td><code>link_target</code></td>
						<td><?php esc_html_e('Where to open links', 'trek-navigators'); ?></td>
						<td><code>_self</code></td>
						<td><code>_self</code>, <code>_blank</code></td>
						<td><code>link_target="_blank"</code></td>
					</tr>
					<tr>
						<td><code>show_read_more</code></td>
						<td><?php esc_html_e('Display "Read More" link', 'trek-navigators'); ?></td>
						<td><code>true</code></td>
						<td><code>true</code>, <code>false</code></td>
						<td><code>show_read_more="false"</code></td>
					</tr>
					<tr>
						<td><code>read_more_text</code></td>
						<td><?php esc_html_e('Custom text for read more link', 'trek-navigators'); ?></td>
						<td><code>View Profile</code></td>
						<td><?php esc_html_e('any text', 'trek-navigators'); ?></td>
						<td><code>read_more_text="Learn More"</code><br><code>read_more_text="Meet this Navigator"</code></td>
					</tr>
				</table>

				<h3><?php esc_html_e('Advanced Parameters', 'trek-navigators'); ?></h3>
				<table>
					<tr>
						<th><?php esc_html_e('Parameter', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Description', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Default', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Options', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Examples', 'trek-navigators'); ?></th>
					</tr>
					<tr>
						<td><code>offset</code></td>
						<td><?php esc_html_e('Number of posts to skip', 'trek-navigators'); ?></td>
						<td><code>0</code></td>
						<td><?php esc_html_e('any number', 'trek-navigators'); ?></td>
						<td><code>offset="3"</code><br><code>offset="10"</code></td>
					</tr>
					<tr>
						<td><code>cache</code></td>
						<td><?php esc_html_e('Whether to cache results', 'trek-navigators'); ?></td>
						<td><code>true</code></td>
						<td><code>true</code>, <code>false</code></td>
						<td><code>cache="false"</code></td>
					</tr>
					<tr>
						<td><code>class</code></td>
						<td><?php esc_html_e('Additional CSS classes', 'trek-navigators'); ?></td>
						<td><code>''</code></td>
						<td><?php esc_html_e('any class names', 'trek-navigators'); ?></td>
						<td><code>class="featured-navigators"</code><br><code>class="blue-theme highlighted"</code></td>
					</tr>
				</table>

				<h3><?php esc_html_e('Example Shortcodes', 'trek-navigators'); ?></h3>
				<p><?php esc_html_e('Basic grid with 3 columns:', 'trek-navigators'); ?></p>
				<div class="trek-navigators-shortcode-example">
					[trek_navigators columns="3" posts_per_page="6"]
				</div>

				<p><?php esc_html_e('List display with pagination:', 'trek-navigators'); ?></p>
				<div class="trek-navigators-shortcode-example">
					[trek_navigators display_type="list" pagination="true" posts_per_page="10"]
				</div>

				<p><?php esc_html_e('Navigators from a specific category, randomly ordered:', 'trek-navigators'); ?></p>
				<div class="trek-navigators-shortcode-example">
					[trek_navigators category="featured-navigators" orderby="rand"]
				</div>
			</div>

			<!-- Single Navigator Shortcode Section -->
			<div class="trek-navigators-help-section">
				<h2><?php esc_html_e('Shortcode: [trek_navigator]', 'trek-navigators'); ?></h2>
				<p><?php esc_html_e('This shortcode displays a single Trek Navigator profile with customizable elements.', 'trek-navigators'); ?></p>

				<h3><?php esc_html_e('Basic Usage', 'trek-navigators'); ?></h3>
				<p><?php esc_html_e('You must specify the ID of the navigator to display:', 'trek-navigators'); ?></p>
				<div class="trek-navigators-shortcode-example">
					[trek_navigator id="42"]
				</div>

				<h3><?php esc_html_e('Available Parameters', 'trek-navigators'); ?></h3>
				<table>
					<tr>
						<th><?php esc_html_e('Parameter', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Description', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Default', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Options', 'trek-navigators'); ?></th>
						<th><?php esc_html_e('Examples', 'trek-navigators'); ?></th>
					</tr>
					<tr>
						<td><code>id</code></td>
						<td><?php esc_html_e('Trek Navigator ID (required)', 'trek-navigators'); ?></td>
						<td><code>0</code></td>
						<td><?php esc_html_e('any valid post ID', 'trek-navigators'); ?></td>
						<td><code>id="42"</code><br><code>id="156"</code></td>
					</tr>
					<tr>
						<td><code>show_image</code></td>
						<td><?php esc_html_e('Whether to show the navigator image', 'trek-navigators'); ?></td>
						<td><code>true</code></td>
						<td><code>true</code>, <code>false</code></td>
						<td><code>show_image="false"</code></td>
					</tr>
					<tr>
						<td><code>show_date</code></td>
						<td><?php esc_html_e('Display start date', 'trek-navigators'); ?></td>
						<td><code>true</code></td>
						<td><code>true</code>, <code>false</code></td>
						<td><code>show_date="false"</code></td>
					</tr>
					<tr>
						<td><code>show_video</code></td>
						<td><?php esc_html_e('Whether to display the video embed', 'trek-navigators'); ?></td>
						<td><code>true</code></td>
						<td><code>true</code>, <code>false</code></td>
						<td><code>show_video="false"</code></td>
					</tr>
					<tr>
						<td><code>show_content_sections</code></td>
						<td><?php esc_html_e('Display the content sections', 'trek-navigators'); ?></td>
						<td><code>true</code></td>
						<td><code>true</code>, <code>false</code></td>
						<td><code>show_content_sections="false"</code></td>
					</tr>
					<tr>
						<td><code>show_digital_badges_link</code></td>
						<td><?php esc_html_e('Show link to digital badges', 'trek-navigators'); ?></td>
						<td><code>true</code></td>
						<td><code>true</code>, <code>false</code></td>
						<td><code>show_digital_badges_link="false"</code></td>
					</tr>
					<tr>
						<td><code>show_more_about</code></td>
						<td><?php esc_html_e('Display the "More About" section', 'trek-navigators'); ?></td>
						<td><code>true</code></td>
						<td><code>true</code>, <code>false</code></td>
						<td><code>show_more_about="false"</code></td>
					</tr>
					<tr>
						<td><code>class</code></td>
						<td><?php esc_html_e('Additional CSS classes', 'trek-navigators'); ?></td>
						<td><code>''</code></td>
						<td><?php esc_html_e('any class names', 'trek-navigators'); ?></td>
						<td><code>class="featured-profile"</code><br><code>class="compact-layout special"</code></td>
					</tr>
				</table>

				<h3><?php esc_html_e('Example Shortcodes', 'trek-navigators'); ?></h3>
				<p><?php esc_html_e('Display a navigator with ID 42, hiding the video:', 'trek-navigators'); ?></p>
				<div class="trek-navigators-shortcode-example">
					[trek_navigator id="42" show_video="false"]
				</div>

				<p><?php esc_html_e('Display a simplified navigator profile:', 'trek-navigators'); ?></p>
				<div class="trek-navigators-shortcode-example">
					[trek_navigator id="42" show_video="false" show_digital_badges_link="false" show_more_about="false"]
				</div>

				<p><?php esc_html_e('Add a custom class to the navigator for styling:', 'trek-navigators'); ?></p>
				<div class="trek-navigators-shortcode-example">
					[trek_navigator id="42" class="featured-profile special-layout"]
				</div>
			</div>

			<!-- Finding IDs Section -->
			<div class="trek-navigators-help-section">
				<h2><?php esc_html_e('Finding Navigator IDs', 'trek-navigators'); ?></h2>
				<p><?php esc_html_e('To find the ID of a Trek Navigator:', 'trek-navigators'); ?></p>
				<ol>
					<li><?php esc_html_e('Go to Trek Navigators in the admin menu', 'trek-navigators'); ?></li>
					<li><?php esc_html_e('Hover over a navigator\'s title', 'trek-navigators'); ?></li>
					<li><?php esc_html_e('Look at the URL that appears in your browser\'s status bar', 'trek-navigators'); ?></li>
					<li><?php esc_html_e('The ID is the number after "post=", e.g., post=42', 'trek-navigators'); ?></li>
				</ol>
				<p><?php esc_html_e('Alternatively, open a navigator for editing and the ID will be visible in the URL.', 'trek-navigators'); ?></p>
			</div>

			<!-- Need Help Section -->
			<div class="trek-navigators-help-section">
				<h2><?php esc_html_e('Need More Help?', 'trek-navigators'); ?></h2>
				<p><?php esc_html_e('If you need further assistance:', 'trek-navigators'); ?></p>
				<ul>
					<li><?php esc_html_e('Contact your website administrator', 'trek-navigators'); ?></li>
					<li><?php esc_html_e('Refer to the WordPress documentation for general shortcode usage', 'trek-navigators'); ?></li>
				</ul>
			</div>
		</div>
		<?php
	}
}