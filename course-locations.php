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

// Add a "Location Details" box to the edit screen.
function cl_add_meta_box() {
	add_meta_box(
		'cl_location_details',  // ID of the box
		'Location Details',     // Title shown on the box
		'cl_render_meta_box',   // Function that prints the box's HTML
		'course_location'       // Only show it on Course Locations
	);
}
add_action( 'add_meta_boxes', 'cl_add_meta_box' );

// Print the three input boxes.
function cl_render_meta_box( $post ) {
	wp_nonce_field( 'cl_save_meta', 'cl_meta_nonce' ); // security token

	$address  = get_post_meta( $post->ID, 'cl_address', true );
	$city     = get_post_meta( $post->ID, 'cl_city', true );
	$capacity = get_post_meta( $post->ID, 'cl_capacity', true );
	?>
	<p>
		<label>Street address</label><br>
		<input type="text" name="cl_address" class="widefat" value="<?php echo esc_attr( $address ); ?>">
	</p>
	<p>
		<label>City</label><br>
		<input type="text" name="cl_city" class="widefat" value="<?php echo esc_attr( $city ); ?>">
	</p>
	<p>
		<label>Seat capacity</label><br>
		<input type="number" min="0" name="cl_capacity" value="<?php echo esc_attr( $capacity ); ?>">
	</p>
	<?php
}

// Save what was typed when you click Publish/Update.
function cl_save_meta( $post_id ) {
	// Stop if the security token is missing or wrong.
	if ( ! isset( $_POST['cl_meta_nonce'] ) || ! wp_verify_nonce( $_POST['cl_meta_nonce'], 'cl_save_meta' ) ) {
		return;
	}
	// Stop if this user isn't allowed to edit the post.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['cl_address'] ) ) {
		update_post_meta( $post_id, 'cl_address', sanitize_text_field( $_POST['cl_address'] ) );
	}
	if ( isset( $_POST['cl_city'] ) ) {
		update_post_meta( $post_id, 'cl_city', sanitize_text_field( $_POST['cl_city'] ) );
	}
	if ( isset( $_POST['cl_capacity'] ) ) {
		update_post_meta( $post_id, 'cl_capacity', absint( $_POST['cl_capacity'] ) );
	}
}
add_action( 'save_post_course_location', 'cl_save_meta' );

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
	$html = '<ul>';
	while ( $query->have_posts() ) {
		$query->the_post();
		$city     = get_post_meta( get_the_ID(), 'cl_city', true );
		$capacity = get_post_meta( get_the_ID(), 'cl_capacity', true );

		$html .= '<li>';
		$html .= '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
		$html .= ' — ' . esc_html( $city ) . ' (' . esc_html( $capacity ) . ' seats)';
		$html .= '</li>';
	}
	$html .= '</ul>';

	wp_reset_postdata(); // Put the page's own post data back.

	return $html;
}
add_shortcode( 'course_locations', 'cl_shortcode_course_locations' );