<?php
/**
 * Plugin Name: Course Locations
 * Description: My practice plugin.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load each feature from its own file.
require_once plugin_dir_path( __FILE__ ) . 'includes/post-type.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/hooks.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/rest-api.php';

// Runs once when the plugin is activated.
function cl_activate() {
	cl_register_post_type(); // Make sure WordPress knows about the post type first.
	flush_rewrite_rules();   // Rebuild the URL list so location pages work.
}
register_activation_hook( __FILE__, 'cl_activate' );

// Runs once when the plugin is deactivated: remove our URLs.
function cl_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'cl_deactivate' );

