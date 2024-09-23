<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

// js & css
wp_enqueue_script('imagesloaded');
wp_enqueue_script('awl-ms-jquery-sliderPro-min-js');
wp_enqueue_style('awl-ms-slider-pro-min-css');
// custom
wp_enqueue_style('awl-ms-bootstrap-css');

$media_slider_id = esc_attr($post_id['id']);

if (!function_exists('is_ms_serialized')) {
    function is_ms_serialized($str) {
        return ($str == serialize(false) || @unserialize($str) !== false);
    }
}

$all_sliders = array(
	'p' => $media_slider_id,
	'post_type' => 'media_slider',
	'orderby' => 'ASC',
);
$loop = new WP_Query($all_sliders);

if ($loop->have_posts()) {
	while ($loop->have_posts()):
		$loop->the_post();
		$post_id = esc_attr(get_the_ID());
		
		// Retrieve the base64 encoded data
		$encodedData = get_post_meta($post_id, 'awl_ms_settings_' . $post_id, true);

		// Decode the base64 encoded data
		$decodedData = base64_decode($encodedData);

		// Check if the data is serialized
		if (is_ms_serialized($decodedData)) {

			// The data is serialized, so unserialize it
			$slider_settings = unserialize($decodedData);
			// Optionally, convert the unserialized data to JSON and save it back in base64 encoding for future access
			// This step is optional but recommended to transition your data format

			$jsonEncodedData = json_encode($slider_settings);
			update_post_meta($post_id, 'awl_ms_settings_' . $post_id, $jsonEncodedData);

			// Now, to use the newly saved format, fetch and decode again
			$encodedData = get_post_meta($post_id, 'awl_ms_settings_' . $post_id, true);
			$slider_settings = json_decode(($encodedData), true);

		} else {
			// Assume the data is in JSON format
			$jsonData = get_post_meta($post_id, 'awl_ms_settings_' . $post_id, true);
			// Decode the JSON string into an associative array
			$slider_settings = json_decode($jsonData, true); // Ensure true is passed to get an associative array
		}

		// Slide
		$width = $slider_settings['width'];
		$height = $slider_settings['height'];
		$slide_autoheight = $slider_settings['slide_autoheight'];
		$slide_imagescalemode = $slider_settings['slide_imagescalemode'];
		$slide_imagecenter = $slider_settings['slide_imagecenter'];
		$slide_scaleup = $slider_settings['slide_scaleup'];
		$slide_autoslidesize = $slider_settings['slide_autoslidesize'];
		$shuffle_slide = $slider_settings['shuffle_slide'];
		$slide_caption = $slider_settings['slide_caption'];
		$slide_loop = $slider_settings['slide_loop'];
		$slide_visiblesize = $slider_settings['slide_visiblesize'];
		// Autoplay
		$slide_autoplay = $slider_settings['slide_autoplay'];
		$slide_autoplay_delay = $slider_settings['slide_autoplay_delay'];
		$slide_autoplay_hover = $slider_settings['slide_autoplay_hover'];
		// Arrows
		$slide_arrows = $slider_settings['slide_arrows'];
		// FullScreen Button
		$slide_fullscreen_btn = $slider_settings['slide_fullscreen_btn'];
		// Layers
		$slide_waitforlayers = $slider_settings['slide_waitforlayers'];
		$slide_autoscalelayers = $slider_settings['slide_autoscalelayers'];
		// Thumbnails
		$slide_thumb = $slider_settings['slide_thumb'];
		$slide_thumb_width = $slider_settings['slide_thumb_width'];
		$slide_thumb_height = $slider_settings['slide_thumb_height'];
		$slide_thumb_pos = $slider_settings['slide_thumb_pos'];
		$slide_thumb_arrows = $slider_settings['slide_thumb_arrows'];
		$slide_thumb_touchswipe = $slider_settings['slide_thumb_touchswipe'];
		// Video
		$videoaction_play = $slider_settings['videoaction_play'];
		$videoaction_pause = $slider_settings['videoaction_pause'];
		// Text Area
		$slide_text = $slider_settings['slide_text'];
		$slide_text_pos = $slider_settings['slide_text_pos'];
		$custom_css = $slider_settings['custom_css'];

		// start the Media Slider contents
		?>
		<div id="image_gallery_<?php echo esc_attr($media_slider_id); ?>" class="row all-images">
			<div class="slider-pro" id="my-slider-<?php echo esc_attr($media_slider_id); ?>">
				<div class="sp-slides">
					<?php
					if (isset($slider_settings['media-slide-ids']) && count($slider_settings['media-slide-ids']) > 0) {
						$count = 0;
						foreach ($slider_settings['media-slide-ids'] as $attachment_id) {
							$thumb = wp_get_attachment_image_src($attachment_id, 'thumb', true);
							$thumbnail = wp_get_attachment_image_src($attachment_id, 'thumbnail', true);
							$medium = wp_get_attachment_image_src($attachment_id, 'medium', true);
							$large = wp_get_attachment_image_src($attachment_id, 'large', true);
							$full = wp_get_attachment_image_src($attachment_id, 'full', true);
							$attachment_details = get_post($attachment_id);
							$title = $attachment_details->post_title;
							$description = $attachment_details->post_content;
							$slide_type = $slider_settings['media-slide-type'][$count];
							$slide_link = $slider_settings['media-slide-link'][$count];

							if (
								$slide_text_pos == 'topleft' || $slide_text_pos == 'top' || $slide_text_pos == 'topright' ||
								$slide_text_pos == 'bottomleft' || $slide_text_pos == 'bottom' || $slide_text_pos == 'bottomright'
							) {
								$dv1 = 35;
								$dh1 = 10;
							}
							if ($slide_text_pos == 'left' || $slide_text_pos == 'center' || $slide_text_pos == 'right') {
								$dv1 = 0;
								$dh1 = 60;
							}
							if ($slide_text_pos == 'topright' || $slide_text_pos == 'right' || $slide_text_pos == 'bottomright') {
								$align = 'right';
							}
							if ($slide_text_pos == 'top' || $slide_text_pos == 'center' || $slide_text_pos == 'bottom') {
								$align = 'center';
							}
							?>
							<div class="sp-slide">
								<?php if ($slide_type == 'i') { ?>
									<img class="sp-image" src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'css/images/blank.gif'); ?>"
										data-src="<?php echo esc_url($full[0]); ?>" data-small="<?php echo esc_url($thumb[0]); ?>"
										data-medium="<?php echo esc_url($full[0]); ?>" data-large="<?php echo esc_url($large[0]); ?>"
										data-retina="<?php echo esc_url($full[0]); ?>" />

									<?php if ($slide_text == 'true') { ?>
										<p class="sp-layer sp-white sp-padding" align="<?php echo esc_html($align); ?>"
											data-position="<?php echo esc_html($slide_text_pos); ?>"
											data-vertical="<?php echo esc_html($dv1); ?>" data-horizontal="<?php echo esc_html($dh1); ?>"
											data-show-delay="500">
											<?php
											if ($title != null) {
												?>
												<span class="title-css">
													<?php echo esc_html($title); ?>
												</span><br>
											<?php } ?>
											<?php
											if ($description != null) {
												?>
												<span class="desc-css">
													<?php echo esc_html($description); ?>
												</span><br>
											<?php } ?>
										</p>
									<?php } ?>

									<?php
									if ($slide_caption == 'true') {
										?>
										<p class="sp-caption"><span class="caption-css">
												<?php echo esc_html($title); ?>
											</span></p>
									<?php } ?>
								<?php } ?>
								<?php if ($slide_type == 'v') { ?>
									<a class="sp-video" href="<?php echo esc_url($slide_link); ?>">
										<img class="sp-image" src="<?php echo esc_url($full[0]); ?>">
									</a>
									<?php
									if ($slide_caption == 'true') {
										?>
										<p class="sp-caption"><span class="caption-css">
												<?php echo esc_html($title); ?>
											</span></p>
									<?php } ?>
								<?php } ?>
							</div>
							<?php
							$count++;
						}// end of attachment foreach
					} else {
						esc_html_e('Sorry! No media slider found', 'media-slider');
					} // end of if else of slides avaialble check into slider
					?>
				</div>
				<?php if ($slide_thumb == 'true') { ?>
					<div class="sp-thumbnails">
						<?php
						if (isset($slider_settings['media-slide-ids']) && count($slider_settings['media-slide-ids']) > 0) {
							$count = 0;
							foreach ($slider_settings['media-slide-ids'] as $attachment_id) {
								$thumb = wp_get_attachment_image_src($attachment_id, 'thumb', true);
								$thumbnail = wp_get_attachment_image_src($attachment_id, 'thumbnail', true);
								$medium = wp_get_attachment_image_src($attachment_id, 'medium', true);
								$large = wp_get_attachment_image_src($attachment_id, 'large', true);
								$full = wp_get_attachment_image_src($attachment_id, 'full', true);
								?>
								<img class="sp-thumbnail" src="<?php echo esc_url($full[0]); ?>" />
								<?php
								$count++;
							}// end of attachment foreach
						} else {
							esc_html_e('Sorry! No media slider found', 'media-slider');
						} // end of if else of slides avaialble check into slider
						?>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	endwhile;
} //end if
wp_reset_query();
?>
<style>
	.title-css {
		font-size: 18px;
		font-weight: bolder;
		text-transform: uppercase;
	}

	.desc-css {
		font-size: 16px;
	}

	.caption-css {
		font-size: 16px;
		font-weight: bolder;
		text-transform: uppercase;
	}

	a.sp-video:after {
		box-sizing: unset;
	}

	<?php echo $custom_css; ?>
</style>
<script type="application/javascript">
	jQuery(document).ready(function (jQuery) {
		jQuery("#my-slider-<?php echo esc_js($media_slider_id); ?>").sliderPro({
			width: <?php echo esc_js($width); ?>,
			height: <?php echo esc_js($height); ?>,
			//Slide
			centerImage: <?php echo esc_js($slide_imagecenter); ?>,
			allowScaleUp: <?php echo esc_js($slide_scaleup); ?>,
			autoSlideSize: <?php echo esc_js($slide_autoslidesize); ?>,
			autoHeight: <?php echo esc_js($slide_autoheight); ?>,
			shuffle: <?php echo esc_js($shuffle_slide); ?>,
			loop: <?php echo esc_js($slide_loop); ?>,
			visibleSize: '<?php echo esc_js($slide_visiblesize); ?>',
			waitForLayers: <?php echo esc_js($slide_waitforlayers); ?>,
			autoScaleLayers: <?php echo esc_js($slide_autoscalelayers); ?>,
			//Auto
			autoplay: <?php echo esc_js($slide_autoplay); ?>,
			autoplayDelay: <?php echo esc_js($slide_autoplay_delay); ?>,
			autoplayOnHover: '<?php echo esc_js($slide_autoplay_hover); ?>',
			//Navigation
			arrows: <?php echo esc_js($slide_arrows); ?>,
			fadeArrows: false,
			buttons: false,
			keyboard: false,
			fullScreen: <?php echo esc_js($slide_fullscreen_btn); ?>,
			fadeFullScreen: false,
			//Video
			playVideoAction: '<?php echo esc_js($videoaction_play); ?>',
			pauseVideoAction: '<?php echo esc_js($videoaction_pause); ?>',
			//Thumbnails
			thumbnailWidth: <?php echo esc_js($slide_thumb_width); ?>,
			thumbnailHeight: <?php echo esc_js($slide_thumb_height); ?>,
			thumbnailsPosition: '<?php echo esc_js($slide_thumb_pos); ?>',
			thumbnailArrows: <?php echo esc_js($slide_thumb_arrows); ?>,
			fadeThumbnailArrows: false,
			thumbnailTouchSwipe: <?php echo esc_js($slide_thumb_touchswipe); ?>
		});
	});
</script>