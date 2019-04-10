=== Plugin Name ===
Contributors: vossavant
Requires at least: 4.9.2
Tested up to: 4.9.2
Stable tag: 4.9.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily add social sharing links to your post or page. Supports Facebook, Twitter, Google+, Pinterest, LinkedIn, and Whatsapp. Includes a shortcode for placing social links within a post's or page's content.

== Description ==

Easily add social sharing links to your post or page. Supports Facebook, Twitter, Google+, Pinterest, LinkedIn, and Whatsapp. Includes a shortcode for placing social links within a post's or page's content. Includes several options for customizing the appearance and location of your social share icons.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the `toptal-social-share` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the `Plugins` menu in WordPress
3. Configure settings (appearance, etc.) from the `Settings > Toptal Social Share` menu
4. Add social media sharing to your posts and pages using the `[toptal_social_share]` shortcode

== Frequently Asked Questions ==

= How do I use the shortcode? =

The shortcode can be placed within the content area of any post or page:

[toptal_social_share]

This shortcode does not accept any parameters; it will show the social sharing bar as configured on the plugin's settings page.

NOTE: the shortcode display a share bar even on post types that you have not enabled in the "Choose Post Types" section on the plugin settings page.

= I activated the WhatsApp link, but I can't see it. What's wrong? =

The WhatsApp sharing link only works on mobile browsers, so you will only see it if you are on a mobile device. The plugin uses JavaScript to detect if you are on an iOS or Android device, so screen size is not a consideration.

== Changelog ==

= 1.0 =
* Initial release