<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register our API routes. Must run on the 'rest_api_init' hook.
function cl_register_rest_routes() {

	// Route 1: GET /wp-json/course-locations/v1/locations
	register_rest_route( 'course-locations/v1', '/locations', array(
		'methods'             => 'GET',
		'callback'            => 'cl_rest_get_locations',
		'permission_callback' => '__return_true', // Public: anyone can read.
	) );

	// Route 2: GET /wp-json/course-locations/v1/locations/123
	// (?P<id>\d+) means "a number, and call it id".
	register_rest_route( 'course-locations/v1', '/locations/(?P<id>\d+)', array(
		'methods'             => 'GET',
		'callback'            => 'cl_rest_get_location',
		'permission_callback' => '__return_true',
	) );
}
add_action( 'rest_api_init', 'cl_register_rest_routes' );

// Helper: turn one location post into a clean array.
// Both routes use this, so they always return the same shape.
function cl_prepare_location( $post ) {
	return array(
		'id'       => $post->ID,
		'title'    => get_the_title( $post ),
		'address'  => get_post_meta( $post->ID, 'cl_address', true ),
		'city'     => get_post_meta( $post->ID, 'cl_city', true ),
		'capacity' => (int) get_post_meta( $post->ID, 'cl_capacity', true ),
		'link'     => get_permalink( $post ),
	);
}

// Handle Route 1: list locations, optionally filtered by ?city=
function cl_rest_get_locations( $request ) {
	$args = array(
		'post_type'      => 'course_location',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'orderby'        => 'title',
		'order'          => 'ASC',
	);

	$city = sanitize_text_field( $request->get_param( 'city' ) );
	if ( $city !== '' ) {
		$args['meta_key']   = 'cl_city';
		$args['meta_value'] = $city;
	}

	$posts = get_posts( $args );
	$data  = array_map( 'cl_prepare_location', $posts );

	return rest_ensure_response( $data ); // Sends it as JSON with status 200.
}

// Handle Route 2: one location by ID, or a 404 error.
function cl_rest_get_location( $request ) {
	$post = get_post( (int) $request['id'] );

	if ( ! $post || $post->post_type !== 'course_location' || $post->post_status !== 'publish' ) {
		return new WP_Error( 'cl_not_found', 'Course location not found.', array( 'status' => 404 ) );
	}

	return rest_ensure_response( cl_prepare_location( $post ) );
}