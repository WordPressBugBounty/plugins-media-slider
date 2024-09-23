<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * video Gallery Premium Shortcode
 */
add_shortcode( 'MDSL', 'awl_media_slider_shortcode' );
function awl_media_slider_shortcode( $post_id ) {
	ob_start();
	// output code file
	require 'media-slider-code.php';
	return ob_get_clean();
}

