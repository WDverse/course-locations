<?php
/**
 * Plugin Name: Course Locations
 * Description: My practice plugin.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
function cl_register_post_type() {
	register_post_type( 'course_location', array(
		'labels' => array(
			'name'          => 'Course Locations',
			'singular_name' => 'Course Location',
		),
		'public'       => true,
		'has_archive'  => true,
		'menu_icon'    => 'dashicons-location',
		'show_in_rest' => true,
	) );
}
add_action( 'init', 'cl_register_post_type' );