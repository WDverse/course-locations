<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// FILTER: add location details under the content on a single location page.
function cl_append_location_details( $content ) {
	// Only on a single Course Location page, in the main content area.
	if ( ! is_singular( 'course_location' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content; // A filter must ALWAYS return something.
	}

	$address  = get_post_meta( get_the_ID(), 'cl_address', true );
	$city     = get_post_meta( get_the_ID(), 'cl_city', true );
	$capacity = get_post_meta( get_the_ID(), 'cl_capacity', true );

	$details  = '<h3>Location Details</h3>';
	$details .= '<p><strong>Address:</strong> ' . esc_html( $address . ', ' . $city ) . '</p>';
	$details .= '<p><strong>Capacity:</strong> ' . esc_html( $capacity ) . ' seats</p>';

	return $content . $details; // Original content + our extra bit.
}
add_filter( 'the_content', 'cl_append_location_details' );

// FILTER: add two column headings to the admin list.
function cl_admin_columns( $columns ) {
	$columns['cl_city']     = 'City';
	$columns['cl_capacity'] = 'Capacity';
	return $columns;
}
add_filter( 'manage_course_location_posts_columns', 'cl_admin_columns' );

// ACTION: print the value in each row of those columns.
function cl_admin_column_values( $column, $post_id ) {
	if ( $column === 'cl_city' ) {
		echo esc_html( get_post_meta( $post_id, 'cl_city', true ) );
	}
	if ( $column === 'cl_capacity' ) {
		echo esc_html( get_post_meta( $post_id, 'cl_capacity', true ) );
	}
}
add_action( 'manage_course_location_posts_custom_column', 'cl_admin_column_values', 10, 2 );