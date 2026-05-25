<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

// js & css
wp_enqueue_script('imagesloaded');
wp_enqueue_script('awl-ms-jquery-sliderPro-min-js');
wp_enqueue_style('awl-ms-slider-pro-min-css');
// Note: awl-ms-bootstrap-css frontend enqueuing is removed to avoid global theme collisions.

$media_slider_id = isset($post_id['id']) ? intval($post_id['id']) : 0;

$all_sliders = array(
	'p' => $media_slider_id,
	'post_type' => 'media_slider',
	'order' => 'ASC',
);
$loop = new WP_Query($all_sliders);

if ($loop->have_posts()) {
	while ($loop->have_posts()):
		$loop->the_post();
		$current_slider_id = esc_attr(get_the_ID());
		
		$slider_settings = Awl_Media_Slider::get_slider_settings($current_slider_id);

		// Slide settings extraction with fallbacks
		$width = isset($slider_settings['width']) ? $slider_settings['width'] : 960;
		$height = isset($slider_settings['height']) ? $slider_settings['height'] : 540;
		$slide_autoheight = isset($slider_settings['slide_autoheight']) ? $slider_settings['slide_autoheight'] : 'true';
		$slide_imagescalemode = isset($slider_settings['slide_imagescalemode']) ? $slider_settings['slide_imagescalemode'] : 'cover';
		$slide_imagecenter = isset($slider_settings['slide_imagecenter']) ? $slider_settings['slide_imagecenter'] : 'true';
		$slide_scaleup = isset($slider_settings['slide_scaleup']) ? $slider_settings['slide_scaleup'] : 'true';
		$slide_autoslidesize = isset($slider_settings['slide_autoslidesize']) ? $slider_settings['slide_autoslidesize'] : 'false';
		$shuffle_slide = isset($slider_settings['shuffle_slide']) ? $slider_settings['shuffle_slide'] : 'false';
		$slide_caption = isset($slider_settings['slide_caption']) ? $slider_settings['slide_caption'] : 'true';
		$slide_loop = isset($slider_settings['slide_loop']) ? $slider_settings['slide_loop'] : 'true';
		$slide_visiblesize = isset($slider_settings['slide_visiblesize']) ? $slider_settings['slide_visiblesize'] : 'auto';
		// Autoplay
		$slide_autoplay = isset($slider_settings['slide_autoplay']) ? $slider_settings['slide_autoplay'] : 'true';
		$slide_autoplay_delay = isset($slider_settings['slide_autoplay_delay']) ? $slider_settings['slide_autoplay_delay'] : 5000;
		$slide_autoplay_hover = isset($slider_settings['slide_autoplay_hover']) ? $slider_settings['slide_autoplay_hover'] : 'pause';
		// Arrows
		$slide_arrows = isset($slider_settings['slide_arrows']) ? $slider_settings['slide_arrows'] : 'true';
		// FullScreen Button
		$slide_fullscreen_btn = isset($slider_settings['slide_fullscreen_btn']) ? $slider_settings['slide_fullscreen_btn'] : 'false';
		// Layers
		$slide_waitforlayers = isset($slider_settings['slide_waitforlayers']) ? $slider_settings['slide_waitforlayers'] : 'false';
		$slide_autoscalelayers = isset($slider_settings['slide_autoscalelayers']) ? $slider_settings['slide_autoscalelayers'] : 'true';
		// Thumbnails
		$slide_thumb = isset($slider_settings['slide_thumb']) ? $slider_settings['slide_thumb'] : 'true';
		$slide_thumb_width = isset($slider_settings['slide_thumb_width']) ? $slider_settings['slide_thumb_width'] : 200;
		$slide_thumb_height = isset($slider_settings['slide_thumb_height']) ? $slider_settings['slide_thumb_height'] : 100;
		$slide_thumb_pos = isset($slider_settings['slide_thumb_pos']) ? $slider_settings['slide_thumb_pos'] : 'top';
		$slide_thumb_arrows = isset($slider_settings['slide_thumb_arrows']) ? $slider_settings['slide_thumb_arrows'] : 'true';
		$slide_thumb_touchswipe = isset($slider_settings['slide_thumb_touchswipe']) ? $slider_settings['slide_thumb_touchswipe'] : 'true';
		// Video
		$videoaction_play = isset($slider_settings['videoaction_play']) ? $slider_settings['videoaction_play'] : 'stopAutoplay';
		$videoaction_pause = isset($slider_settings['videoaction_pause']) ? $slider_settings['videoaction_pause'] : 'none';
		// Text Area
		$slide_text = isset($slider_settings['slide_text']) ? $slider_settings['slide_text'] : 'true';
		$slide_text_pos = isset($slider_settings['slide_text_pos']) ? $slider_settings['slide_text_pos'] : 'bottom';
		$custom_css = isset($slider_settings['custom_css']) ? $slider_settings['custom_css'] : '';

		// Construct static count suffix for multi-instance compatibility
		$slider_selector = 'my-slider-' . esc_attr($media_slider_id) . (isset($instance) ? '-' . esc_attr($instance) : '');
		?>
		<div id="image_gallery_<?php echo esc_attr($media_slider_id); ?>" class="row all-images">
			<div class="slider-pro" id="<?php echo $slider_selector; ?>">
				<div class="sp-slides">
					<?php
					if (isset($slider_settings['media-slide-ids']) && is_array($slider_settings['media-slide-ids']) && count($slider_settings['media-slide-ids']) > 0) {
						$count = 0;
						foreach ($slider_settings['media-slide-ids'] as $attachment_id) {
							$attachment_id = intval($attachment_id);
							$thumb = wp_get_attachment_image_src($attachment_id, 'thumb', true);
							$thumbnail = wp_get_attachment_image_src($attachment_id, 'thumbnail', true);
							$medium = wp_get_attachment_image_src($attachment_id, 'medium', true);
							$large = wp_get_attachment_image_src($attachment_id, 'large', true);
							$full = wp_get_attachment_image_src($attachment_id, 'full', true);

							$thumb_url = is_array($thumb) ? $thumb[0] : '';
							$thumbnail_url = is_array($thumbnail) ? $thumbnail[0] : '';
							$medium_url = is_array($medium) ? $medium[0] : '';
							$large_url = is_array($large) ? $large[0] : '';
							$full_url = is_array($full) ? $full[0] : '';

							$attachment_details = get_post($attachment_id);
							$title = $attachment_details ? $attachment_details->post_title : (isset($slider_settings['media-slide-title'][$count]) ? $slider_settings['media-slide-title'][$count] : '');
							$description = $attachment_details ? $attachment_details->post_content : (isset($slider_settings['media-slide-desc'][$count]) ? $slider_settings['media-slide-desc'][$count] : '');

							$slide_type = isset($slider_settings['media-slide-type'][$count]) ? $slider_settings['media-slide-type'][$count] : 'i';
							$slide_link = isset($slider_settings['media-slide-link'][$count]) ? $slider_settings['media-slide-link'][$count] : '';

							// Pre-initialize to default values to avoid notices
							$dv1 = 0;
							$dh1 = 0;
							$align = 'left';

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
										data-src="<?php echo esc_url($full_url); ?>" data-small="<?php echo esc_url($thumb_url); ?>"
										data-medium="<?php echo esc_url($full_url); ?>" data-large="<?php echo esc_url($large_url); ?>"
										data-retina="<?php echo esc_url($full_url); ?>" />

									<?php if ($slide_text == 'true') { ?>
										<p class="sp-layer sp-white sp-padding" align="<?php echo esc_attr($align); ?>"
											data-position="<?php echo esc_attr($slide_text_pos); ?>"
											data-vertical="<?php echo esc_attr($dv1); ?>" data-horizontal="<?php echo esc_attr($dh1); ?>"
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
													<?php echo wp_kses_post($description); ?>
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
										<img class="sp-image" src="<?php echo esc_url($full_url); ?>">
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
						if (isset($slider_settings['media-slide-ids']) && is_array($slider_settings['media-slide-ids']) && count($slider_settings['media-slide-ids']) > 0) {
							$count = 0;
							foreach ($slider_settings['media-slide-ids'] as $attachment_id) {
								$attachment_id = intval($attachment_id);
								$full = wp_get_attachment_image_src($attachment_id, 'full', true);
								$full_url = is_array($full) ? $full[0] : '';
								?>
								<img class="sp-thumbnail" src="<?php echo esc_url($full_url); ?>" />
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
} else {
	if (current_user_can('manage_options')) {
		echo '<div class="notice notice-warning"><p>' . sprintf(esc_html__('Media Slider with ID %d does not exist or has no slides.', 'media-slider'), $media_slider_id) . '</p></div>';
	}
}
wp_reset_postdata();
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

	<?php echo wp_strip_all_tags($custom_css); ?>
</style>
<script type="application/javascript">
	jQuery(document).ready(function (jQuery) {
		jQuery("#<?php echo esc_js($slider_selector); ?>").sliderPro({
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