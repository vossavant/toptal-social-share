<?php
/**
 * Plugin bootstrap file.
 * 
 * @since		1.0.0
 * @package		Toptal_Social_Share
 * @author		Ryan Burney <hello@ryanburney.com>
 * 
 * Plugin Name: Toptal Social Share
 * Description: Easily add social sharing links to your post or page. Supports Facebook, Twitter, Google+, Pinterest, LinkedIn, and Whatsapp. Includes a shortcode for placing social links within a post's or page's content.
 * Version: 1.0
 * Author: Ryan Burney
 * Author URI: http://www.ryanburney.com
 * Text Domain: toptal-social-share
 * License: GPL2
 *
 * Toptal Social Share is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 2 of the License, or any later version.
 *
 * Toptal Social Share is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Toptal Social Share. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */

// prevent direct file execution
if (!defined('WPINC')) {
	die;
}

/**
 * Set constants for social network sharing URLs.
 */
if (!defined('TTSS_FACEBOOK_SHARE_URL')) {
	define('TTSS_FACEBOOK_SHARE_URL', 'https://www.facebook.com/sharer/sharer.php?u=');
}

if (!defined('TTSS_TWITTER_SHARE_URL')) {
	define('TTSS_TWITTER_SHARE_URL', 'https://twitter.com/home?status=');
}

if (!defined('TTSS_GOOGLE_SHARE_URL')) {
	define('TTSS_GOOGLE_SHARE_URL', 'https://plus.google.com/share?url=');
}

if (!defined('TTSS_PINTEREST_SHARE_URL')) {
	define('TTSS_PINTEREST_SHARE_URL', 'https://pinterest.com/pin/create/button/?url=');
}

if (!defined('TTSS_LINKEDIN_SHARE_URL')) {
	define('TTSS_LINKEDIN_SHARE_URL', 'https://www.linkedin.com/shareArticle?mini=true&url=');
}

if (!defined('TTSS_WHATSAPP_SHARE_URL')) {
	define('TTSS_WHATSAPP_SHARE_URL', 'whatsapp://send?text=');
}

/**
 * Code that runs when plugin is activated; adds default plugin
 * options to the database.
 */
function ttss_activate() {
	add_option('ttss_post_types', array('post' => 1, 'page' => 1));
	add_option('ttss_social_networks', array('Facebook' => 1, 'Twitter' => 1));
	add_option('ttss_icon_order', array('Facebook', 'Twitter', 'Google+', 'Pinterest', 'LinkedIn', 'WhatsApp'));
	add_option('ttss_share_bar_locations', array('below_title' => 1));
}

/**
 * Code that runs when plugin is deactivated.
 */
function ttss_deactivate() {
	// nothing to see here
}

/**
 * Code that runs when plugin is uninstalled; removes plugin options
 * (prefixed with `ttss_`) from the database.
 */
function ttss_uninstall() {
	global $wpdb;
	
	$plugin_options = $wpdb->get_results("SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'ttss_%'");
	
	foreach ($plugin_options as $option) {
		delete_option($option->option_name);
	}
}

/**
 * @param $links
 * @return array
 *
 * Adds a "settings" link on the plugin page
 */
function ttss_plugin_action_links($links) {
	$links[] = '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=toptal-social-share-options')) . '">Settings</a>';
	return $links;
}

/**
 * @param $hook
 *
 * Loads plugin-specific CSS and JS on the plugin settings page only.
 */
function ttss_load_admin_scripts($hook) {
	if ($hook != 'settings_page_toptal-social-share-options') {
		return;
	}
	
	// enqueue dependencies for general plugin styles and behavior
	wp_enqueue_style('ttss_admin_css', plugins_url('includes/css/toptal-social-share-admin.css', __FILE__));
	wp_enqueue_script('ttss_admin_js', plugins_url('includes/js/toptal-social-share-admin.js', __FILE__), null, '1.0.0', true);
	
	// enqueue dependencies for the color picker field
	wp_enqueue_style('ttss_spectrum_css', plugins_url('includes/css/spectrum.css', __FILE__));
	wp_enqueue_script('ttss_spectrum_js', plugins_url('includes/js/spectrum.js', __FILE__), null, '1.8.0', true);
	
	// enqueue JS for the sortable list field
	wp_enqueue_script('jquery-ui-sortable');
}

/**
 * Loads plugin-specific CSS/JS on front-end.
 */
function ttss_load_frontend_scripts() {
	wp_enqueue_style('ttss_frontend_css', plugins_url('includes/css/toptal-social-share.css', __FILE__));
	wp_enqueue_script('ttss_stickyfill', plugins_url('includes/js/stickyfill.js', __FILE__), null, '2.0.3', true);
	wp_enqueue_script('ttss_frontend_js', plugins_url('includes/js/toptal-social-share.js', __FILE__), 'ttss_stickyfill', '1.0.0', true);
}

/**
 * Creates a submenu under "Settings" for accessing the plugin options.
 */
function ttss_create_submenu()
{
	add_options_page(
		'Toptal Social Share Settings',
		'Toptal Social Share',
		'manage_options',
		'toptal-social-share-options',
		'ttss_render_settings'
	);
}

/**
 * Renders settings form for the plugin options page.
 */
function ttss_render_settings()
{
	if (!current_user_can('manage_options')) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?= esc_html(get_admin_page_title()); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields('toptal-social-share-options');
			do_settings_sections('toptal-social-share-options');
			submit_button('Save Settings');
			?>
		</form>
	</div>
	<?php
}

/**
 * Creates sections and fields for each setting available to this plugin,
 * and registers each setting using the Settings API.
 */
function ttss_init_settings() {
	register_setting('toptal-social-share-options', 'ttss_post_types', array('sanitize_callback' => 'ttss_validate_post_types'));
	register_setting('toptal-social-share-options', 'ttss_social_networks', array('sanitize_callback' => 'ttss_validate_networks'));
	register_setting('toptal-social-share-options', 'ttss_icon_size');
	register_setting('toptal-social-share-options', 'ttss_icon_color');
	register_setting('toptal-social-share-options', 'ttss_icon_order');
	register_setting('toptal-social-share-options', 'ttss_share_bar_locations', array('sanitize_callback' => 'ttss_validate_locations'));
	
	add_settings_section(
		'ttss-general-settings',
		__('General', 'toptal-social-share'),
		'ttss_settings_general_callback',
		'toptal-social-share-options'
	);
	
	add_settings_section(
		'ttss-appearance-settings',
		__('Appearance', 'toptal-social-share'),
		'ttss_settings_appearance_callback',
		'toptal-social-share-options'
	);
	
	add_settings_section(
		'ttss-location-settings',
		__('Placement', 'toptal-social-share'),
		'ttss_settings_location_callback',
		'toptal-social-share-options'
	);
	
	add_settings_field(
		'ttss-post-types',
		__('Choose Post Types', 'toptal-social-share'),
		'ttss_render_setting_post_type',
		'toptal-social-share-options',
		'ttss-general-settings'
	);
	
	add_settings_field(
		'ttss-networks',
		__('Choose Social Networks', 'toptal-social-share'),
		'ttss_render_setting_social_networks',
		'toptal-social-share-options',
		'ttss-general-settings',
		array(
			'networks' => array(
				'Facebook',
				'Twitter',
				'Google+',
				'Pinterest',
				'LinkedIn',
				'WhatsApp',
			)
		)
	);
	
	add_settings_field(
		'ttss-icon-size',
		__('Select Icon Size', 'toptal-social-share'),
		'ttss_render_setting_icon_size',
		'toptal-social-share-options',
		'ttss-appearance-settings',
		array(
			'sizes' => array(
				'sm' => __('Small', 'toptal-social-share'),
				'md' => __('Medium', 'toptal-social-share'),
				'lg' => __('Large', 'toptal-social-share')
			)
		)
	);
	
	add_settings_field(
		'ttss-icon-color',
		__('Select Icon Color', 'toptal-social-share'),
		'ttss_render_setting_icon_color',
		'toptal-social-share-options',
		'ttss-appearance-settings'
	);
	
	add_settings_field(
		'ttss-network-order',
		__('Display Order', 'toptal-social-share'),
		'ttss_render_setting_icon_order',
		'toptal-social-share-options',
		'ttss-appearance-settings'
	);
	
	add_settings_field(
		'ttss-icons-location',
		__('Choose Share Bar Location', 'toptal-social-share'),
		'ttss_render_setting_share_bar_location',
		'toptal-social-share-options',
		'ttss-location-settings',
		array(
			'locations' => array(
				'below_title' => __('Below the post/page title', 'toptal-social-share'),
				'floating' => __('Floating on left side of screen', 'toptal-social-share'),
				'after_content' => __('After post/page content', 'toptal-social-share'),
				'inside_image' => __('Inside the featured image', 'toptal-social-share')
			)
		)
	);
}

/**
 * @param $input
 * @return mixed
 *
 * Simple validation to ensure at least one checkbox is checked
 * for the "choose post types" option.
 */
function ttss_validate_post_types($input) {
	$current_options = get_option('ttss_post_types');
	
	if (empty($input)) {
		add_settings_error(
			'requiredCheckboxEmpty',
			'empty',
			__('You must choose at least one post type.', 'toptal-social-share'),
			'error'
		);
		$input = $current_options;
	}
	
	return $input;
}

/**
 * @param $input
 * @return mixed
 *
 * Simple validation to ensure at least one checkbox is checked
 * for the "choose social networks" option.
 */
function ttss_validate_networks($input) {
	$current_options = get_option('ttss_social_networks');
	
	if (empty($input)) {
		add_settings_error(
			'requiredCheckboxEmpty',
			'empty',
			__('You must choose at least one social network.', 'toptal-social-share'),
			'error'
		);
		$input = $current_options;
	}
	
	return $input;
}

/**
 * @param $input
 * @return mixed
 *
 * Simple validation to ensure at least one checkbox is checked
 * for the "choose share bar location" option.
 */
function ttss_validate_locations($input) {
	$current_options = get_option('ttss_share_bar_locations');
	
	if (empty($input)) {
		add_settings_error(
			'requiredCheckboxEmpty',
			'empty',
			__('You must choose at least one share bar location.', 'toptal-social-share'),
			'error'
		);
		$input = $current_options;
	}
	
	return $input;
}

/**
 * Callback for the "General" settings section; outputs helper text
 * beneath the section title.
 */
function ttss_settings_general_callback() {
	echo '<p>' . esc_html__('Choose which post types should display the social share bar, and which social networks should be shown.', 'toptal-social-share') . '</p>';
}

/**
 * Callback for the "Appearance" settings section; outputs helper text
 * beneath the section title.
 */
function ttss_settings_appearance_callback() {
	echo '<p>' . esc_html__('Choose the size for each social network icon (16px, 24px, or 32px), whether to use original icon colors or a custom color for all icons, and in which order the icons should appear (drag and drop to reorder).', 'toptal-social-share') . '</p>';
}

/**
 * Callback for the "Placement" settings section; outputs helper text
 * beneath the section title.
 */
function ttss_settings_location_callback() {
	echo '<p>' . esc_html__('Choose where the social share bar should appear on your posts/pages.', 'toptal-social-share') . '</p>';
}

/**
 * Outputs a checkbox group for the "Choose Post Types" setting.
 */
function ttss_render_setting_post_type() {
	$options = get_option('ttss_post_types');
	
	foreach (get_post_types(array('public' => true), 'objects') as $post_type) {
		if ($post_type->name != 'attachment') {
			echo '<label><input ' . (isset($options[$post_type->name]) ? 'checked="checked"' : '') . ' name="ttss_post_types[' . $post_type->name . ']" type="checkbox" value="1">' . $post_type->label . '</label><br>';
		}
	}
}

/**
 * @param $args
 *
 * Outputs a checkbox group for the "Choose Social Networks" setting.
 */
function ttss_render_setting_social_networks($args) {
	$options = get_option('ttss_social_networks');
	
	foreach ($args['networks'] as $network) {
		echo '<label><input ' . (isset($options[$network]) ? 'checked="checked"' : '') . ' name="ttss_social_networks[' . $network . ']" type="checkbox" value="1">' . $network . '</label><br>';
	}
}

/**
 * @param $args
 *
 * Outputs a radio button group for the "Select Button Size" setting.
 */
function ttss_render_setting_icon_size($args) {
	$options = get_option('ttss_icon_size', 'sm');
	
	foreach ($args['sizes'] as $size_slug => $size_label) {
		echo '<label class="ttss-radio"><input ' . ($options == $size_slug ? 'checked="checked"' : '') . ' name="ttss_icon_size" type="radio" value="' . $size_slug . '">' . $size_label . '</label>';
	}
}

/**
 * Outputs a radio button group for the "Select Icon Color" setting.
 */
function ttss_render_setting_icon_color() {
	$options = get_option('ttss_icon_color', 'default');
	
	echo '<label class="ttss-radio"><input ' . ($options == 'default' ? 'checked="checked"' : '') . ' name="ttss_icon_color" type="radio" value="default">Use originals</label>';
	echo '<label class="ttss-radio"><input ' . (!empty($options) && $options != 'default' ? 'checked="checked"' : '') . ' class="ttss-custom-color" name="ttss_icon_color" type="radio" value="' . ($options != 'default' ? $options : '#3dbe8b') . '">Set custom</label>';
	echo '<span class="ttss-colorpicker__wrap"><input class="ttss-colorpicker__input" type="text"></span>';
}

/**
 * Outputs a sortable list for the "Select Icon Order" setting.
 */
function ttss_render_setting_icon_order() {
	$options = get_option('ttss_icon_order');
	
	// TODO: sorting should be done in DB
	ksort($options);
	
	echo '<ul class="ttss-ui-sortable">';
	foreach ($options as $key => $network) {
		echo '<li class="ttss-ui-sortable__item" id="network_' . $key . '"><div class="ttss-ui-sortable__label">' . $network . '<span class="ttss-ui-sortable__icon dashicons dashicons-sort"></span></div></li>';
		echo '<input type="hidden" name="ttss_icon_order[' . $key . ']" value="' . $network . '">';
	}
	echo '</ul>';
}

/**
 * @param $args
 *
 * Outputs a checkbox group for the "Choose Share Bar Placement" setting.
 */
function ttss_render_setting_share_bar_location($args) {
	$options = get_option('ttss_share_bar_locations');
	
	foreach ($args['locations'] as $location_slug => $location_label) {
		echo '<label><input ' . (isset($options[$location_slug]) ? 'checked="checked"' : '') . ' name="ttss_share_bar_locations[' . $location_slug . ']" type="checkbox" value="1">' . $location_label . '</label><br>';
	}
}

/**
 * @param $html
 * @return string
 *
 * Places social share bar within a wrapper around the featured
 * thumbnail. Called only if a post thumbnail exists.
 */
function ttss_modify_post_thumbnail($html) {
	$html = '<div class="ttss-feature-image__wrap">' . $html . ttss_build_share_bar_html() . '</div>';
	return $html;
}

/**
 * @param $enabled_networks
 * @param $user_sorted_networks
 * @return array
 *
 * Takes an array of enabled networks and orders them as they appear
 * in the plugin's "Display Order" setting.
 */
function ttss_sort_networks($enabled_networks, $user_sorted_networks) {
	ksort($user_sorted_networks);
	$enabled_and_sorted = array();
	foreach ($user_sorted_networks as $key) {
		if (array_key_exists($key, $enabled_networks)) {
			$enabled_and_sorted[$key] = $enabled_networks[$key];
		}
	}
	return $enabled_and_sorted + $enabled_networks;
}

/**
 * @return string
 *
 * Returns HTML string of the completed social share bar.
 */
function ttss_build_share_bar_html() {
	$icon_size = get_option('ttss_icon_size', 'sm');
	$icon_color = get_option('ttss_icon_color', 'default');
	$icon_order = get_option('ttss_icon_order');
	
	$html = '<ul class="ttss-share-bar__wrap ttss-share-bar__wrap--' . $icon_size . '">';
	
	if ($enabled_networks = ttss_sort_networks(get_option('ttss_social_networks'), $icon_order)) :
		foreach ($enabled_networks as $network => $is_enabled) :
			$network = str_replace('+', '', $network);
	
			if ($network == 'WhatsApp' && !ttss_is_mobile_browser()) {
				continue;
			}
			
			$html .= '<li class="ttss-share-bar__item">';
			$html .= '<a class="ttss-share-bar__link ttss-share-bar__link--' . $network . '" href="' . constant('TTSS_' . strtoupper($network) . '_SHARE_URL') . urlencode(get_permalink()) . '" title="' . $network . '">' . call_user_func('ttss_get_' . strtolower($network) . '_svg_icon', $icon_color) . '</a>';
			$html .= '</li>';
		endforeach;
	endif;
	
	$html .= '</ul>';
	
	return $html;
}

/**
 * @param $content
 * @return string
 *
 * Determines where to insert the social share bar based on
 * plugin settings.
 */
function ttss_render_share_bar($content) {
	$enabled_on_post_types = get_option('ttss_post_types');
	$enabled_locations = get_option('ttss_share_bar_locations');
	$share_bar = ttss_build_share_bar_html();
	
	foreach ($enabled_on_post_types as $post_type => $is_enabled) {
		if (is_singular($post_type)) {
			foreach ($enabled_locations as $location => $is_set) {
				switch ($location) {
					case 'below_title':
						$content = $share_bar . $content;
						break;
					
					case 'floating':
						$content = '<div class="ttss-share-bar__float ttss-sticky">' . $share_bar . '</div>' . $content;
						break;
					
					case 'after_content':
						$content .= $share_bar;
						break;
					
					case 'inside_image':
						// TODO: code should be here, not in is_admin() check
						break;
					
					default:
						$content .= $share_bar;
						break;
				}
			}
		}
	}
	
	return $content;
}

/**
 * @return string
 *
 * Generates shortcode for placing the social share bar within
 * post/page content.
 */
function ttss_shortcode() {
	$content = ttss_build_share_bar_html();
	return $content;
}
add_shortcode('toptal_social_share', 'ttss_shortcode');

/**
 * @param $color
 * @return string
 *
 * The following "svg" methods return raw SVG code for each social
 * network's icon. Allows fill with custom or default color.
 */
function ttss_get_facebook_svg_icon($color) {
	return '<svg aria-labelledby="simpleicons-facebook-icon" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="' . ($color == 'default' ? '#3B5998' : $color) . '" d="M22.676 0H1.324C.593 0 0 .593 0 1.324v21.352C0 23.408.593 24 1.324 24h11.494v-9.294H9.689v-3.621h3.129V8.41c0-3.099 1.894-4.785 4.659-4.785 1.325 0 2.464.097 2.796.141v3.24h-1.921c-1.5 0-1.792.721-1.792 1.771v2.311h3.584l-.465 3.63H16.56V24h6.115c.733 0 1.325-.592 1.325-1.324V1.324C24 .593 23.408 0 22.676 0"/></svg>';
}

function ttss_get_twitter_svg_icon($color) {
	return '<svg aria-labelledby="simpleicons-twitter-icon" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="' . ($color == 'default' ? '#1DA1F2' : $color) . '" d="M23.954 4.569c-.885.389-1.83.654-2.825.775 1.014-.611 1.794-1.574 2.163-2.723-.951.555-2.005.959-3.127 1.184-.896-.959-2.173-1.559-3.591-1.559-2.717 0-4.92 2.203-4.92 4.917 0 .39.045.765.127 1.124C7.691 8.094 4.066 6.13 1.64 3.161c-.427.722-.666 1.561-.666 2.475 0 1.71.87 3.213 2.188 4.096-.807-.026-1.566-.248-2.228-.616v.061c0 2.385 1.693 4.374 3.946 4.827-.413.111-.849.171-1.296.171-.314 0-.615-.03-.916-.086.631 1.953 2.445 3.377 4.604 3.417-1.68 1.319-3.809 2.105-6.102 2.105-.39 0-.779-.023-1.17-.067 2.189 1.394 4.768 2.209 7.557 2.209 9.054 0 13.999-7.496 13.999-13.986 0-.209 0-.42-.015-.63.961-.689 1.8-1.56 2.46-2.548l-.047-.02z"/></svg>';
}

function ttss_get_google_svg_icon($color) {
	return '<svg aria-labelledby="simpleicons-googleplus-icon" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="' . ($color == 'default' ? '#DC4E41' : $color) . '" d="M7.635 10.909v2.619h4.335c-.173 1.125-1.31 3.295-4.331 3.295-2.604 0-4.731-2.16-4.731-4.823 0-2.662 2.122-4.822 4.728-4.822 1.485 0 2.479.633 3.045 1.178l2.073-1.994c-1.33-1.245-3.056-1.995-5.115-1.995C3.412 4.365 0 7.785 0 12s3.414 7.635 7.635 7.635c4.41 0 7.332-3.098 7.332-7.461 0-.501-.054-.885-.12-1.265H7.635zm16.365 0h-2.183V8.726h-2.183v2.183h-2.182v2.181h2.184v2.184h2.189V13.09H24"/></svg>';
}

function ttss_get_pinterest_svg_icon($color) {
	return '<svg aria-labelledby="simpleicons-pinterest-icon" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="' . ($color == 'default' ? '#BD081C' : $color) . '" d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/></svg>';
}

function ttss_get_linkedin_svg_icon($color) {
	return '<svg aria-labelledby="simpleicons-linkedin-icon" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="' . ($color == 'default' ? '#0077B5' : $color) . '" d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>';
}

function ttss_get_whatsapp_svg_icon($color) {
	return '<svg aria-labelledby="simpleicons-whatsapp-icon" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="' . ($color == 'default' ? '#25D366' : $color) . '" d="M17.498 14.382c-.301-.15-1.767-.867-2.04-.966-.273-.101-.473-.15-.673.15-.197.295-.771.964-.944 1.162-.175.195-.349.21-.646.075-.3-.15-1.263-.465-2.403-1.485-.888-.795-1.484-1.77-1.66-2.07-.174-.3-.019-.465.13-.615.136-.135.301-.345.451-.523.146-.181.194-.301.297-.496.1-.21.049-.375-.025-.524-.075-.15-.672-1.62-.922-2.206-.24-.584-.487-.51-.672-.51-.172-.015-.371-.015-.571-.015-.2 0-.523.074-.797.359-.273.3-1.045 1.02-1.045 2.475s1.07 2.865 1.219 3.075c.149.195 2.105 3.195 5.1 4.485.714.3 1.27.48 1.704.629.714.227 1.365.195 1.88.121.574-.091 1.767-.721 2.016-1.426.255-.705.255-1.29.18-1.425-.074-.135-.27-.21-.57-.345m-5.446 7.443h-.016c-1.77 0-3.524-.48-5.055-1.38l-.36-.214-3.75.975 1.005-3.645-.239-.375c-.99-1.576-1.516-3.391-1.516-5.26 0-5.445 4.455-9.885 9.942-9.885 2.654 0 5.145 1.035 7.021 2.91 1.875 1.859 2.909 4.35 2.909 6.99-.004 5.444-4.46 9.885-9.935 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.447h.006c6.585 0 11.946-5.336 11.949-11.896 0-3.176-1.24-6.165-3.495-8.411"/></svg>';
}

/**
 * @return bool
 *
 * Checks if user agent is a mobile browser.
 * Source: http://detectmobilebrowsers.com/
 */
function ttss_is_mobile_browser() {
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $user_agent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($user_agent, 0, 4))) {
		return true;
	}
	
	return false;
}

/**
 * Register actions and filters separately for WP admin and front-end.
 */
if (is_admin()) {
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ttss_plugin_action_links');
	add_action('admin_menu', 'ttss_create_submenu');
	add_action('admin_init', 'ttss_init_settings');
	add_action('admin_enqueue_scripts', 'ttss_load_admin_scripts');
	add_action('wp_ajax_ttss_update_dnd_order', 'ttss_update_dnd_network_order');
} else {
	add_action('wp_enqueue_scripts', 'ttss_load_frontend_scripts');
	add_filter('the_content', 'ttss_render_share_bar');
	
	// TODO: this seems less than elegant - should ideally live inside `ttss_render_share_bar()`
	
	if ($options = get_option('ttss_share_bar_locations')) {
		if (array_key_exists('inside_image', $options)) {
			add_filter('post_thumbnail_html', 'ttss_modify_post_thumbnail', 10, 5);
		}
	}
}

register_activation_hook(__FILE__, 'ttss_activate');
register_deactivation_hook(__FILE__, 'ttss_deactivate');
register_uninstall_hook(__FILE__, 'ttss_uninstall');