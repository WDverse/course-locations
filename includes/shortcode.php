<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// [course_locations] shortcode: lists locations on any page.
// Optional: [course_locations city="Toronto"]
function cl_shortcode_course_locations( $atts ) {
	// Merge what the editor typed with defaults.
	$atts = shortcode_atts( array(
		'city' => '',
	), $atts );

	// Build the query: get published Course Locations, A to Z.
	$args = array(
		'post_type'      => 'course_location',
		'post_status'    => 'publish',
		'posts_per_page' => 10,
		'orderby'        => 'title',
		'order'          => 'ASC',
	);

	// If a city was given, only get locations in that city.
	if ( $atts['city'] !== '' ) {
		$args['meta_key']   = 'cl_city';
		$args['meta_value'] = sanitize_text_field( $atts['city'] );
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '<p>No course locations found.</p>';
	}

	// Build the HTML as a string. Shortcodes must RETURN, not echo.
	$html = '<ul class="cl-list">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$city     = get_post_meta( get_the_ID(), 'cl_city', true );
		$capacity = get_post_meta( get_the_ID(), 'cl_capacity', true );

		$html .= '<li class="cl-card">';
		$html .= '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
		$html .= ' — ' . esc_html( $city ) . ' (' . esc_html( $capacity ) . ' seats)';
		$html .= '</li>';
	}
	$html .= '</ul>';

	wp_reset_postdata(); // Put the page's own post data back.

	return $html;
}
add_shortcode( 'course_locations', 'cl_shortcode_course_locations' );