<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Media Slider Shortcode
 */
add_shortcode( 'MDSL', 'awl_media_slider_shortcode' );
function awl_media_slider_shortcode( $atts ) {
	static $instance = 0;
	$instance++;

	$atts = shortcode_atts(
		array(
			'id' => 0,
		),
		$atts,
		'MDSL'
	);

	// Map $post_id for compatibility with media-slider-code.php
	$post_id = $atts;

	ob_start();
	// output code file
	require MS_PLUGIN_DIR . 'media-slider-code.php';
	return ob_get_clean();
}
