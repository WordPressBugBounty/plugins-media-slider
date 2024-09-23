<?Php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

// load settings
$post_id = esc_attr($post->ID);

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

// css
wp_enqueue_style('ms-bootstrap-css', MS_PLUGIN_URL . 'css/ms-bootstrap.css');
wp_enqueue_style('ms-font-awesome-min-css', MS_PLUGIN_URL . 'css/font-awesome.min.css');
wp_enqueue_style('ms-styles-css', MS_PLUGIN_URL . 'css/styles.css');
wp_enqueue_style('ms-go-to-top-css', MS_PLUGIN_URL . 'css/go-to-top.css');
wp_enqueue_style('ms-toogle-button-css', MS_PLUGIN_URL . 'css/toogle-button.css');
wp_enqueue_style('awl-em-pe-icon-7-stroke-css', MS_PLUGIN_URL . 'css/pe-icon-7-stroke.css');
// js
wp_enqueue_script('jquery');
wp_enqueue_script('ms-bootstrap-js', MS_PLUGIN_URL . 'js/bootstrap.js', array('jquery'), '', true);
wp_enqueue_script('ms-go-to-top-js', MS_PLUGIN_URL . 'js/go-to-top.js', array('jquery'), '', true);

?>
<style>
	div.add-text {
		font-size: 25px !important;
	}

	.setting-toggle-div {
		background-color: #FFFFFF;
		padding: 10px;
		margin-bottom: 15px;
		border: 2px solid #CCCCCC;
		border-radius: 3px;
	}

	.slider-settings {
		padding: 8px 0px 8px 8px !important;
		margin: 10px 10px 5px 0px !important;
	}

	.slider-settings label {
		font-size: 13px !important;
		font-weight: bold;
	}

	.ms_comment_settings {
		font-size: 16px !important;
		font-family: Geneva;
		padding-left: 4px;
		font: initial;
		margin-top: 5px;
		padding-left: 14px;
	}

	.tps input,
	.cps input,
	.bps input {
		display: none !important;
	}

	.btn_st {
		border-radius: 5px !important;
	}

	h4>a {
		color: #ea0000 !important;
		margin-left: 45px;
		font-family: Geneva;
	}

	h4>a:hover {
		color: #000 !important;
	}

	.panel .panel-heading .panel-title a .fa {
		color: #ef3e36 !important;

	}

	.panel-default>.panel-heading {
		background-color: #f4f0ef !important;
	}

	.lower-title {
		background-color: #F4F0EF;
		color: #23282d;
		font-family: icon;
		font-size: 20px;
		font-weight: 500;
		margin-left: 10px;
		padding-left: 10px;
	}
</style>
<!-- Return to Top -->
<a href="javascript:" id="return-to-top"><i class="fa fa-chevron-up"></i></a>

<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
	<!--Slider Settings-->
	<div class="panel panel-default">
		<div class="panel-heading panel-heading-theme-1 icon-right" role="tab" id="heading1" data-toggle="collapse"
			data-parent="#accordion" href="#collapse1" aria-controls="collapse1">
			<h4 class="panel-title">
				<a data-toggle="collapse" data-parent="#accordion" href="#collapse1" aria-expanded="true"
					aria-controls="collapse1"><i class="fa fa-chevron-down"></i><span class="pe-7s-monitor"></span>
					<?php esc_html_e('Slider Settings', 'media-slider'); ?>
				</a>
			</h4>
		</div>
		<div id="collapse1" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading1">
			<div class="panel-body">
				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Width', 'media-slider'); ?>
				</p></br>
				<?php
				if (isset($slider_settings['width'])) {
					$width = $slider_settings['width'];
				} else {
					$width = 960;
				}
				?>

				<input type="text" class="form-control" id="width" name="width"
					value="<?php echo esc_html($width); ?>" style="margin-left:25px;"></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Set the slider frame preview width(Default is 960)', 'media-slider'); ?>
				</p>
				</p>

				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Height', 'media-slider'); ?>
				</p></br>
				<?php
				if (isset($slider_settings['height'])) {
					$height = $slider_settings['height'];
				} else {
					$height = 540;
				}
				?>
				<input type="text" class="form-control" id="height" name="height"
					value="<?php echo esc_html($height); ?>" style="margin-left:25px;"></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Set the slider frame preview height(Default is 540)', 'media-slider'); ?>
				</p>
				</p>

				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Auto Height', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['slide_autoheight'])) {
						$slide_autoheight = $slider_settings['slide_autoheight'];
					} else {
						$slide_autoheight = 'true';
					}
					?>
					<input type="radio" class="form-control" id="slide_autoheight1" name="slide_autoheight" value="true"
						<?php
						if ($slide_autoheight == 'true') {
							echo 'checked';
						}
						?>>
					<label for="slide_autoheight1">
						<?php esc_html_e('On', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="slide_autoheight2" name="slide_autoheight"
						value="false" <?php
						if ($slide_autoheight == 'false') {
							echo 'checked';
						}
						?>>
					<label for="slide_autoheight2">
						<?php esc_html_e('Off', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if the image can be scaled up more than its original size', 'media-slider'); ?>
				</p>
				</p>
				</p>

				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Image Scale Mode', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['slide_imagescalemode'])) {
						$slide_imagescalemode = $slider_settings['slide_imagescalemode'];
					} else {
						$slide_imagescalemode = 'cover';
					}
					?>
					<input type="radio" class="form-control" id="slide_imagescalemode1" name="slide_imagescalemode"
						value="cover" <?php
						if ($slide_imagescalemode == 'cover') {
							echo 'checked';
						}
						?>>
					<label for="slide_imagescalemode1">
						<?php esc_html_e('Cover', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="slide_imagescalemode4" name="slide_imagescalemode"
						value="none" <?php
						if ($slide_imagescalemode == 'none') {
							echo 'checked';
						}
						?>>
					<label for="slide_imagescalemode4">
						<?php esc_html_e('None', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Sets the scale mode of the main slide images (images added as background)', 'media-slider'); ?>
				</p>
				</p>
				</p>

				<p class="slider-settings">

				<p class="ms-title">
					<?php esc_html_e('Image Centered', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['slide_imagecenter'])) {
						$slide_imagecenter = $slider_settings['slide_imagecenter'];
					} else {
						$slide_imagecenter = 'true';
					}
					?>
					<input type="radio" class="form-control" id="slide_imagecenter1" name="slide_imagecenter"
						value="true" <?php
						if ($slide_imagecenter == 'true') {
							echo 'checked';
						}
						?>>
					<label for="slide_imagecenter1">
						<?php esc_html_e('On', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="slide_imagecenter2" name="slide_imagecenter"
						value="false" <?php
						if ($slide_imagecenter == 'false') {
							echo 'checked';
						}
						?>>
					<label for="slide_imagecenter2">
						<?php esc_html_e('Off', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if the image will be centered', 'media-slider'); ?>
				</p>
				</p>
				</p>

				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('ScaleUp', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['slide_scaleup'])) {
						$slide_scaleup = $slider_settings['slide_scaleup'];
					} else {
						$slide_scaleup = 'true';
					}
					?>
					<input type="radio" class="form-control" id="slide_scaleup1" name="slide_scaleup" value="true" <?php
					if ($slide_scaleup == 'true') {
						echo 'checked';
					}
					?>>
					<label for="slide_scaleup1">
						<?php esc_html_e('Yes', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="slide_scaleup2" name="slide_scaleup" value="false"
						<?php
						if ($slide_scaleup == 'false') {
							echo 'checked';
						}
						?>>
					<label for="slide_scaleup2">
						<?php esc_html_e('No', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if the image can be scaled up more than its original size', 'media-slider'); ?>
				</p>
				</p>
				</p>

				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Auto Slide Size', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['slide_autoslidesize'])) {
						$slide_autoslidesize = $slider_settings['slide_autoslidesize'];
					} else {
						$slide_autoslidesize = 'false';
					}
					?>
					<input type="radio" class="form-control" id="slide_autoslidesize1" name="slide_autoslidesize"
						value="true" <?php
						if ($slide_autoslidesize == 'true') {
							echo 'checked';
						}
						?>>
					<label for="slide_autoslidesize1">
						<?php esc_html_e('Yes', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="slide_autoslidesize2" name="slide_autoslidesize"
						value="false" <?php
						if ($slide_autoslidesize == 'false') {
							echo 'checked';
						}
						?>>
					<label for="slide_autoslidesize2">
						<?php esc_html_e('No', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if all the slides are at the same height but will allow the width of the slides to be variable', 'media-slider'); ?>
				</p>
				</p>
				</p>

				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Shuffle', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['shuffle_slide'])) {
						$shuffle_slide = $slider_settings['shuffle_slide'];
					} else {
						$shuffle_slide = 'false';
					}
					?>
					<input type="radio" class="form-control" id="shuffle_slide1" name="shuffle_slide" value="true" <?php
					if ($shuffle_slide == 'true') {
						echo 'checked';
					}
					?>>
					<label for="shuffle_slide1">
						<?php esc_html_e('On', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="shuffle_slide2" name="shuffle_slide" value="false"
						<?php
						if ($shuffle_slide == 'false') {
							echo 'checked';
						}
						?>>
					<label for="shuffle_slide2">
						<?php esc_html_e('Off', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if the slides will be shuffled', 'media-slider'); ?>
				</p>
				</p>
				</p>

				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Slide Caption', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['slide_caption'])) {
						$slide_caption = $slider_settings['slide_caption'];
					} else {
						$slide_caption = 'true';
					}
					?>
					<input type="radio" class="form-control" id="slide_caption1" name="slide_caption" value="true" <?php
					if ($slide_caption == 'true') {
						echo 'checked';
					}
					?>>
					<label for="slide_caption1">
						<?php esc_html_e('Show', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="slide_caption2" name="slide_caption" value="false"
						<?php
						if ($slide_caption == 'false') {
							echo 'checked';
						}
						?>>
					<label for="slide_caption2">
						<?php esc_html_e('Hide', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if the slide caption will be displayed', 'media-slider'); ?>
				</p>
				</p>
				</p>

				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Slider loop', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['slide_loop'])) {
						$slide_loop = $slider_settings['slide_loop'];
					} else {
						$slide_loop = 'true';
					}
					?>
					<input type="radio" class="form-control" id="slide_loop1" name="slide_loop" value="true" <?php
					if ($slide_loop == 'true') {
						echo 'checked';
					}
					?>>
					<label for="slide_loop1">
						<?php esc_html_e('On', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="slide_loop2" name="slide_loop" value="false" <?php
					if ($slide_loop == 'false') {
						echo 'checked';
					}
					?>>
					<label for="slide_loop2">
						<?php esc_html_e('Off', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if the slider will be loopable', 'media-slider'); ?>
				</p>
				</p>
				</p>

				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Slide Visible Size (in %)', 'media-slider'); ?>
				</p></br>
				<?php
				if (isset($slider_settings['slide_visiblesize'])) {
					$slide_visiblesize = $slider_settings['slide_visiblesize'];
				} else {
					$slide_visiblesize = 'auto';
				}
				?>

				<input type="text" class="form-control" id="slide_visiblesize" name="slide_visiblesize" placeholder=""
					value="<?php echo esc_html($slide_visiblesize); ?>" style="margin-left:25px;"></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if the size of the visible area, allowing for more slides to become visible near the selected slide (Default is auto)', 'media-slider'); ?>
				</p>
				</p>

				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Wait For Layers', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['slide_waitforlayers'])) {
						$slide_waitforlayers = $slider_settings['slide_waitforlayers'];
					} else {
						$slide_waitforlayers = 'false';
					}
					?>
					<input type="radio" class="form-control" id="slide_waitforlayers1" name="slide_waitforlayers"
						value="true" <?php
						if ($slide_waitforlayers == 'true') {
							echo 'checked';
						}
						?>>
					<label for="slide_waitforlayers1">
						<?php esc_html_e('Yes', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="slide_waitforlayers2" name="slide_waitforlayers"
						value="false" <?php
						if ($slide_waitforlayers == 'false') {
							echo 'checked';
						}
						?>>
					<label for="slide_waitforlayers2">
						<?php esc_html_e('No', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if the slider will wait for the layers to disappear before going to a new slide', 'media-slider'); ?>
				</p>
				</p>
				</p>

				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Auto Scale Layers', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['slide_autoscalelayers'])) {
						$slide_autoscalelayers = $slider_settings['slide_autoscalelayers'];
					} else {
						$slide_autoscalelayers = 'true';
					}
					?>
					<input type="radio" class="form-control" id="slide_autoscalelayers1" name="slide_autoscalelayers"
						value="true" <?php
						if ($slide_autoscalelayers == 'true') {
							echo 'checked';
						}
						?>>
					<label for="slide_autoscalelayers1">
						<?php esc_html_e('Yes', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="slide_autoscalelayers2" name="slide_autoscalelayers"
						value="false" <?php
						if ($slide_autoscalelayers == 'false') {
							echo 'checked';
						}
						?>>
					<label for="slide_autoscalelayers2">
						<?php esc_html_e('No', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if the slider layers will be scaled automatically', 'media-slider'); ?>
				</p>
				</p>
				</p>

				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Custom CSS', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['custom_css'])) {
						$custom_css = $slider_settings['custom_css'];
					} else {
						$custom_css = '';
					}
					?>
					<textarea name="custom_css" id="custom_css" style="width: 98%; height: 120px;"
						placeholder="Type direct CSS code here. Don't use <style>...</style> tag."><?php echo $custom_css; ?></textarea><br>
				</p>
				</p>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading panel-heading-theme-1 icon-right" role="tab" id="heading3" data-toggle="collapse"
			data-parent="#accordion" href="#collapse3" aria-controls="collapse3">
			<h4 class="panel-title">
				<a data-toggle="collapse" data-parent="#accordion" href="#collapse3" aria-expanded="true"
					aria-controls="collapse3"><i class="fa fa-chevron-down"></i>
					<span class="pe-7s-next"></span>
					<?php esc_html_e('Autoplay Settings', 'media-slider'); ?>
				</a>
			</h4>
		</div>
		<div id="collapse3" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading3">
			<div class="panel-body">
				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Autoplay', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['slide_autoplay'])) {
						$slide_autoplay = $slider_settings['slide_autoplay'];
					} else {
						$slide_autoplay = 'true';
					}
					?>
					<input type="radio" class="form-control" id="slide_autoplay1" name="slide_autoplay" value="true"
						<?php
						if ($slide_autoplay == 'true') {
							echo 'checked';
						}
						?>>
					<label for="slide_autoplay1">
						<?php esc_html_e('On', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="slide_autoplay2" name="slide_autoplay" value="false"
						<?php
						if ($slide_autoplay == 'false') {
							echo 'checked';
						}
						?>>
					<label for="slide_autoplay2">
						<?php esc_html_e('Off', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if or not slide autoplay will be enabled', 'media-slider'); ?>
				</p>
				</p>
				</p>
				<div class="autoplay_settings">
					<p class="slider-settings range-slider">
					<p class="lower-title">
						<?php esc_html_e('Autoplay Delay', 'media-slider'); ?>
					</p></br>
					<p class="switch-field em_size_field">
						<?php
						if (isset($slider_settings['slide_autoplay_delay'])) {
							$slide_autoplay_delay = $slider_settings['slide_autoplay_delay'];
						} else {
							$slide_autoplay_delay = 5000;
						}
						?>

						<input type="range" class="range-slider__range" id="slide_autoplay_delay"
							name="slide_autoplay_delay" value="<?php echo esc_html($slide_autoplay_delay); ?>" min="0"
							max="10000" step="100" style="width: 300px !important; margin-left: 10px;">
						<span class="range-slider__value">5000</span>
					<p class="ms_comment_settings">
						<?php esc_html_e('Sets the delay/interval (in milliseconds) at which the slide autoplay will run (Default is 5000)', 'media-slider'); ?>
					</p>
					</p>
					</p>

					<p class="slider-settings">
					<p class="lower-title">
						<?php esc_html_e('Autoplay OnHover', 'media-slider'); ?>
					</p></br>
					<p class="switch-field em_size_field">
						<?php
						if (isset($slider_settings['slide_autoplay_hover'])) {
							$slide_autoplay_hover = $slider_settings['slide_autoplay_hover'];
						} else {
							$slide_autoplay_hover = 'pause';
						}
						?>
						<input type="radio" class="form-control" id="slide_autoplay_hover1" name="slide_autoplay_hover"
							value="pause" <?php
							if ($slide_autoplay_hover == 'pause') {
								echo 'checked';
							}
							?>>
						<label for="slide_autoplay_hover1">
							<?php esc_html_e('Pause', 'media-slider'); ?>
						</label>
						<input type="radio" class="form-control" id="slide_autoplay_hover2" name="slide_autoplay_hover"
							value="stop" <?php
							if ($slide_autoplay_hover == 'stop') {
								echo 'checked';
							}
							?>>
						<label for="slide_autoplay_hover2">
							<?php esc_html_e('Stop', 'media-slider'); ?>
						</label>
						<input type="radio" class="form-control" id="slide_autoplay_hover3" name="slide_autoplay_hover"
							value="none" <?php
							if ($slide_autoplay_hover == 'none') {
								echo 'checked';
							}
							?>>
						<label for="slide_autoplay_hover3">
							<?php esc_html_e('None', 'media-slider'); ?>
						</label></br></br></br>
					<p class="ms_comment_settings">
						<?php esc_html_e('Select if the slide autoplay will be paused or stopped when the slider is hovered', 'media-slider'); ?>
					</p>
					</p>
					</p>
				</div>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading panel-heading-theme-1 icon-right" role="tab" id="heading4" data-toggle="collapse"
			data-parent="#accordion" href="#collapse4" aria-controls="collapse4">
			<h4 class="panel-title">
				<a data-toggle="collapse" data-parent="#accordion" href="#collapse4" aria-expanded="true"
					aria-controls="collapse4"><i class="fa fa-chevron-down"></i>
					<span class="pe-7s-paper-plane"></span>
					<?php esc_html_e('Navigation Settings', 'media-slider'); ?>
				</a>
			</h4>
		</div>
		<div id="collapse4" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading4">
			<div class="panel-body">
				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Arrows', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['slide_arrows'])) {
						$slide_arrows = $slider_settings['slide_arrows'];
					} else {
						$slide_arrows = 'true';
					}
					?>
					<input type="radio" class="form-control" id="slide_arrows1" name="slide_arrows" value="true" <?php
					if ($slide_arrows == 'true') {
						echo 'checked';
					}
					?>>
					<label for="slide_arrows1">
						<?php esc_html_e('Show', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="slide_arrows2" name="slide_arrows" value="false" <?php
					if ($slide_arrows == 'false') {
						echo 'checked';
					}
					?>>
					<label for="slide_arrows2">
						<?php esc_html_e('Hide', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if the slider arrow buttons will be created', 'media-slider'); ?>
				</p>
				</p>
				</p>

				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('FullScreen Button', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['slide_fullscreen_btn'])) {
						$slide_fullscreen_btn = $slider_settings['slide_fullscreen_btn'];
					} else {
						$slide_fullscreen_btn = 'false';
					}
					?>
					<input type="radio" class="form-control" id="slide_fullscreen_btn1" name="slide_fullscreen_btn"
						value="true" <?php
						if ($slide_fullscreen_btn == 'true') {
							echo 'checked';
						}
						?>>
					<label for="slide_fullscreen_btn1">
						<?php esc_html_e('Show', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="slide_fullscreen_btn2" name="slide_fullscreen_btn"
						value="false" <?php
						if ($slide_fullscreen_btn == 'false') {
							echo 'checked';
						}
						?>>
					<label for="slide_fullscreen_btn2">
						<?php esc_html_e('Hide', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if the slider full-screen button is enabled', 'media-slider'); ?>
				</p>
				</p>
				</p>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading panel-heading-theme-1 icon-right" role="tab" id="heading5" data-toggle="collapse"
			data-parent="#accordion" href="#collapse5" aria-controls="collapse5">
			<h4 class="panel-title">
				<a data-toggle="collapse" data-parent="#accordion" href="#collapse5" aria-expanded="true"
					aria-controls="collapse5"><i class="fa fa-chevron-down"></i>
					<span class="pe-7s-photo"></span>
					<?php esc_html_e('Thumbnail Settings', 'media-slider'); ?>
				</a>
			</h4>
		</div>
		<div id="collapse5" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading5">
			<div class="panel-body">
				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Thumbnails', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['slide_thumb'])) {
						$slide_thumb = $slider_settings['slide_thumb'];
					} else {
						$slide_thumb = 'true';
					}
					?>
					<input type="radio" class="form-control" id="slide_thumb1" name="slide_thumb" value="true" <?php
					if ($slide_thumb == 'true') {
						echo 'checked';
					}
					?>>
					<label for="slide_thumb1">
						<?php esc_html_e('Show', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="slide_thumb2" name="slide_thumb" value="false" <?php
					if ($slide_thumb == 'false') {
						echo 'checked';
					}
					?>>
					<label for="slide_thumb2">
						<?php esc_html_e('Hide', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if the thumbnails are displayed', 'media-slider'); ?>
				</p>
				</p>
				</p>

				<div class="thumb_settings">

					<p class="slider-settings">
					<p class="lower-title">
						<?php esc_html_e('Thumbnail Width', 'media-slider'); ?>
					</p></br>
					<?php
					if (isset($slider_settings['slide_thumb_width'])) {
						$slide_thumb_width = $slider_settings['slide_thumb_width'];
					} else {
						$slide_thumb_width = 200;
					}
					?>

					<input type="text" class="form-control" id="slide_thumb_width" name="slide_thumb_width"
						value="<?php echo esc_html($slide_thumb_width); ?>" style="margin-left:25px;"></br></br>
					<p class="ms_comment_settings">
						<?php esc_html_e('Sets the width of the thumbnail(Default is 100)', 'media-slider'); ?>
					</p>

					</p>

					<p class="slider-settings">
					<p class="lower-title">
						<?php esc_html_e('Thumbnail Height', 'media-slider'); ?>
					</p></br>
					<?php
					if (isset($slider_settings['slide_thumb_height'])) {
						$slide_thumb_height = $slider_settings['slide_thumb_height'];
					} else {
						$slide_thumb_height = 100;
					}
					?>

					<input type="text" class="form-control" id="slide_thumb_height" name="slide_thumb_height"
						value="<?php echo esc_html($slide_thumb_height); ?>" style="margin-left:25px;"></br></br>
					<p class="ms_comment_settings">
						<?php esc_html_e('Sets the height of the thumbnail(Default is 80)', 'media-slider'); ?>
					</p>
					</p>

					<p class="slider-settings">
					<p class="lower-title">
						<?php esc_html_e('Thumbnail Position', 'media-slider'); ?>
					</p></br>
					<p class="switch-field em_size_field">
						<?php
						if (isset($slider_settings['slide_thumb_pos'])) {
							$slide_thumb_pos = $slider_settings['slide_thumb_pos'];
						} else {
							$slide_thumb_pos = 'top';
						}
						?>
						<input type="radio" class="form-control" id="slide_thumb_pos1" name="slide_thumb_pos"
							value="top" <?php
							if ($slide_thumb_pos == 'top') {
								echo 'checked';
							}
							?>>
						<label class="btn_st" for="slide_thumb_pos1" style="margin-left:20px;">
							<?php esc_html_e('Top', 'media-slider'); ?>
						</label></br></br></br>
						<input type="radio" class="form-control" id="slide_thumb_pos5" name="slide_thumb_pos"
							value="bottom" <?php
							if ($slide_thumb_pos == 'bottom') {
								echo 'checked';
							}
							?>>
						<label class="btn_st" for="slide_thumb_pos5" style="margin-left:20px;">
							<?php esc_html_e('bottom', 'media-slider'); ?>
						</label></br></br></br>
					<p class="ms_comment_settings">
						<?php esc_html_e('Sets the position of the thumbnail scroller', 'media-slider'); ?>
					</p>
					</p>
					</p>

					<p class="slider-settings">
					<p class="lower-title">
						<?php esc_html_e('Thumbnail Arrows', 'media-slider'); ?>
					</p></br>
					<p class="switch-field em_size_field">
						<?php
						if (isset($slider_settings['slide_thumb_arrows'])) {
							$slide_thumb_arrows = $slider_settings['slide_thumb_arrows'];
						} else {
							$slide_thumb_arrows = 'true';
						}
						?>
						<input type="radio" class="form-control" id="slide_thumb_arrows1" name="slide_thumb_arrows"
							value="true" <?php
							if ($slide_thumb_arrows == 'true') {
								echo 'checked';
							}
							?>>
						<label for="slide_thumb_arrows1">
							<?php esc_html_e('Show', 'media-slider'); ?>
						</label>
						<input type="radio" class="form-control" id="slide_thumb_arrows2" name="slide_thumb_arrows"
							value="false" <?php
							if ($slide_thumb_arrows == 'false') {
								echo 'checked';
							}
							?>>
						<label for="slide_thumb_arrows2">
							<?php esc_html_e('Hide', 'media-slider'); ?>
						</label></br></br></br>
					<p class="ms_comment_settings">
						<?php esc_html_e('Select if the thumbnail arrows will be enabled', 'media-slider'); ?>
					</p>
					</p>
					</p>

					<p class="slider-settings">
					<p class="lower-title">
						<?php esc_html_e('Thumbnail TouchSwipe', 'media-slider'); ?>
					</p></br>
					<p class="switch-field em_size_field">
						<?php
						if (isset($slider_settings['slide_thumb_touchswipe'])) {
							$slide_thumb_touchswipe = $slider_settings['slide_thumb_touchswipe'];
						} else {
							$slide_thumb_touchswipe = 'true';
						}
						?>
						<input type="radio" class="form-control" id="slide_thumb_touchswipe1"
							name="slide_thumb_touchswipe" value="true" <?php
							if ($slide_thumb_touchswipe == 'true') {
								echo 'checked';
							}
							?>>
						<label for="slide_thumb_touchswipe1">
							<?php esc_html_e('Enable', 'media-slider'); ?>
						</label>
						<input type="radio" class="form-control" id="slide_thumb_touchswipe2"
							name="slide_thumb_touchswipe" value="false" <?php
							if ($slide_thumb_touchswipe == 'false') {
								echo 'checked';
							}
							?>>
						<label for="slide_thumb_touchswipe2">
							<?php esc_html_e('Disable', 'media-slider'); ?>
						</label></br></br></br>
					<p class="ms_comment_settings">
						<?php esc_html_e('Select if the touch swipe will be enabled for thumbnails', 'media-slider'); ?>
					</p>
					</p>
					</p>
				</div>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading panel-heading-theme-1 icon-right" role="tab" id="heading6" data-toggle="collapse"
			data-parent="#accordion" href="#collapse6" aria-controls="collapse6">
			<h4 class="panel-title">
				<a data-toggle="collapse" data-parent="#accordion" href="#collapse6" aria-expanded="true"
					aria-controls="collapse6"><i class="fa fa-chevron-down"></i>
					<span class="pe-7s-video"></span>
					<?php esc_html_e('Video Settings', 'media-slider'); ?>
				</a>
			</h4>
		</div>
		<div id="collapse6" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading6">
			<div class="panel-body">
				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Video Action - Play', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['videoaction_play'])) {
						$videoaction_play = $slider_settings['videoaction_play'];
					} else {
						$videoaction_play = 'stopAutoplay';
					}
					?>
					<input type="radio" class="form-control" id="videoaction_play1" name="videoaction_play"
						value="stopAutoplay" <?php
						if ($videoaction_play == 'stopAutoplay') {
							echo 'checked';
						}
						?>>
					<label for="videoaction_play1">
						<?php esc_html_e('Stop Autoplay', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="videoaction_play2" name="videoaction_play" value="none"
						<?php
						if ($videoaction_play == 'none') {
							echo 'checked';
						}
						?>>
					<label for="videoaction_play2">
						<?php esc_html_e('None', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Sets the action that the slider will perform when the video starts playing', 'media-slider'); ?>
				</p>
				</p>
				</p>

				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Video Action - Pause', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['videoaction_pause'])) {
						$videoaction_pause = $slider_settings['videoaction_pause'];
					} else {
						$videoaction_pause = 'none';
					}
					?>
					<input type="radio" class="form-control" id="videoaction_pause1" name="videoaction_pause"
						value="startAutoplay" <?php
						if ($videoaction_pause == 'startAutoplay') {
							echo 'checked';
						}
						?>>
					<label for="videoaction_pause1">
						<?php esc_html_e('Start Autoplay', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="videoaction_pause2" name="videoaction_pause"
						value="none" <?php
						if ($videoaction_pause == 'none') {
							echo 'checked';
						}
						?>>
					<label for="videoaction_pause2">
						<?php esc_html_e('None', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Sets the action that the slider will perform when the video is paused', 'media-slider'); ?>
				</p>
				</p>
				</p>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading panel-heading-theme-1 icon-right" role="tab" id="heading7" data-toggle="collapse"
			data-parent="#accordion" href="#collapse7" aria-controls="collapse7">
			<h4 class="panel-title">
				<a data-toggle="collapse" data-parent="#accordion" href="#collapse7" aria-expanded="true"
					aria-controls="collapse7"><i class="fa fa-chevron-down"></i>
					<span class="pe-7s-note2"></span>
					<?php esc_html_e('Textarea Settings', 'media-slider'); ?>
				</a>
			</h4>
		</div>
		<div id="collapse7" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading7">
			<div class="panel-body">
				<p class="slider-settings">
				<p class="ms-title">
					<?php esc_html_e('Title / Description', 'media-slider'); ?>
				</p></br>
				<p class="switch-field em_size_field">
					<?php
					if (isset($slider_settings['slide_text'])) {
						$slide_text = $slider_settings['slide_text'];
					} else {
						$slide_text = 'true';
					}
					?>
					<input type="radio" class="form-control" id="slide_text1" name="slide_text" value="true" <?php
					if ($slide_text == 'true') {
						echo 'checked';
					}
					?>>
					<label for="slide_text1">
						<?php esc_html_e('Show', 'media-slider'); ?>
					</label>
					<input type="radio" class="form-control" id="slide_text2" name="slide_text" value="false" <?php
					if ($slide_text == 'false') {
						echo 'checked';
					}
					?>>
					<label for="slide_text2">
						<?php esc_html_e('Hide', 'media-slider'); ?>
					</label></br></br></br>
				<p class="ms_comment_settings">
					<?php esc_html_e('Select if the Title / Description are displayed', 'media-slider'); ?>
				</p>
				</p>
				</p>
				<div class="textarea_settings">
					<p class="slider-settings">
					<p class="lower-title">
						<?php esc_html_e('Title / Description Position', 'media-slider'); ?>
					</p></br>
					<p class="switch-field em_size_field">
						<?php
						if (isset($slider_settings['slide_text_pos'])) {
							$slide_text_pos = $slider_settings['slide_text_pos'];
						} else {
							$slide_text_pos = 'bottom';
						}
						?>

						<input type="radio" class="form-control" id="slide_text_pos2" name="slide_text_pos" value="top"
							<?php
							if ($slide_text_pos == 'top') {
								echo 'checked';
							}
							?>>
						<label class="btn_st" for="slide_text_pos2" style="border-radius:5px;">
							<?php esc_html_e('Top', 'media-slider'); ?>
						</label>

						<input type="radio" class="form-control" id="slide_text_pos6" name="slide_text_pos"
							value="right" <?php
							if ($slide_text_pos == 'right') {
								echo 'checked';
							}
							?>>
						<label class="btn_st" for="slide_text_pos6" style="margin-left:5px;">
							<?php esc_html_e('Right', 'media-slider'); ?>
						</label>

						<input type="radio" class="form-control" id="slide_text_pos8" name="slide_text_pos"
							value="bottom" <?php
							if ($slide_text_pos == 'bottom') {
								echo 'checked';
							}
							?>>
						<label class="btn_st" for="slide_text_pos8" style="border-radius:5px; margin-left:5px;">
							<?php esc_html_e('Bottom', 'media-slider'); ?>
						</label></br></br></br>
					<p class="ms_comment_settings">
						<?php esc_html_e('Select the position where the title / description will be displayed', 'media-slider'); ?>
					</p>
					</p>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
// syntax: wp_nonce_field( 'name_of_my_action', 'name_of_nonce_field' );
wp_nonce_field('ms_save_settings', 'ms_save_nonce');
?>
<script>
	//dropdown toggle on change effect
	jQuery(document).ready(function () {
		//accordion icon
		jQuery(function () {
			function toggleSign(e) {
				jQuery(e.target)
					.prev('.panel-heading')
					.find('i')
					.toggleClass('fa fa-chevron-down fa fa-chevron-up');
			}
			jQuery('#accordion').on('hidden.bs.collapse', toggleSign);
			jQuery('#accordion').on('shown.bs.collapse', toggleSign);

		});
	});

	// start pulse on page load
	function pulseEff() {
		jQuery('#shortcode').fadeOut(600).fadeIn(600);
	};
	var Interval;
	Interval = setInterval(pulseEff, 1500);

	// stop pulse
	function pulseOff() {
		clearInterval(Interval);
	}
	// start pulse
	function pulseStart() {
		Interval = setInterval(pulseEff, 2000);
	}

	//range slider
	var rangeSlider = function () {
		var slider = jQuery('.range-slider'),
			range = jQuery('.range-slider__range'),
			value = jQuery('.range-slider__value');

		slider.each(function () {

			value.each(function () {
				var value = jQuery(this).prev().attr('value');
				jQuery(this).html(value);
			});

			range.on('input', function () {
				jQuery(this).next(value).html(this.value);
			});
		});
	};
	rangeSlider();

	//on load 
	var slide_fade = jQuery('input[name="slide_fade"]:checked').val();
	var slide_autoplay = jQuery('input[name="slide_autoplay"]:checked').val();
	var slider_breakpoints = jQuery('input[name="slider_breakpoints"]:checked').val();
	var slide_thumb = jQuery('input[name="slide_thumb"]:checked').val();
	var slide_text = jQuery('input[name="slide_text"]:checked').val();

	if (slide_fade == "true") {
		jQuery('.fade_settings').show();
	}
	if (slide_fade == "false") {
		jQuery('.fade_settings').hide();
	}
	if (slide_autoplay == "true") {
		jQuery('.autoplay_settings').show();
	}
	if (slide_autoplay == "false") {
		jQuery('.autoplay_settings').hide();
	}
	if (slider_breakpoints == "true") {
		jQuery('.breakpoints_settings').show();
	}
	if (slider_breakpoints == "false") {
		jQuery('.breakpoints_settings').hide();
	}
	if (slide_thumb == "true") {
		jQuery('.thumb_settings').show();
	}
	if (slide_thumb == "false") {
		jQuery('.thumb_settings').hide();
	}
	if (slide_text == "true") {
		jQuery('.textarea_settings').show();
	}
	if (slide_text == "false") {
		jQuery('.textarea_settings').hide();
	}
	//on change
	jQuery(document).ready(function () {
		jQuery('input[name="slide_fade"]').change(function () {
			var slide_fade = jQuery('input[name="slide_fade"]:checked').val();
			if (slide_fade == "true") {
				jQuery('.fade_settings').show();
			}
			if (slide_fade == "false") {
				jQuery('.fade_settings').hide();
			}
		});
		jQuery('input[name="slide_autoplay"]').change(function () {
			var slide_autoplay = jQuery('input[name="slide_autoplay"]:checked').val();
			if (slide_autoplay == "true") {
				jQuery('.autoplay_settings').show();
			}
			if (slide_autoplay == "false") {
				jQuery('.autoplay_settings').hide();
			}
		});
		jQuery('input[name="slider_breakpoints"]').change(function () {
			var slider_breakpoints = jQuery('input[name="slider_breakpoints"]:checked').val();
			if (slider_breakpoints == "true") {
				jQuery('.breakpoints_settings').show();
			}
			if (slider_breakpoints == "false") {
				jQuery('.breakpoints_settings').hide();
			}
		});
		jQuery('input[name="slide_text"]').change(function () {
			var slide_text = jQuery('input[name="slide_text"]:checked').val();
			if (slide_text == "true") {
				jQuery('.textarea_settings').show();
			}
			if (slide_text == "false") {
				jQuery('.textarea_settings').hide();
			}
		});
		jQuery('input[name="slide_thumb"]').change(function () {
			var slide_thumb = jQuery('input[name="slide_thumb"]:checked').val();
			if (slide_thumb == "true") {
				jQuery('.thumb_settings').show();
			}
			if (slide_thumb == "false") {
				jQuery('.thumb_settings').hide();
			}
		});
	});
</script>

</script>
<p class="text-center">
	<br>
	<a href="https://awplife.com/wordpress-plugins/media-slider-premium/" target="_blank"
		class="button button-primary button-hero load-customize hide-if-no-customize">Buy Premium Version</a>
	<a href="https://awplife.com/demo/media-slider-premium/" target="_blank"
		class="button button-primary button-hero load-customize hide-if-no-customize">Check Live Demo</a>
	<a href="https://awplife.com/demo/media-slider-premium-admin-demo/" target="_blank"
		class="button button-primary button-hero load-customize hide-if-no-customize">Try Admin Demo</a>
</p>
<hr>
<style>
	.awp_bale_offer {
		background-image: url("<?php echo esc_url(plugin_dir_url(__FILE__) . 'image/awp-bale.jpg'); ?>") background-repeat:no-repeat;
		padding: 30px;
	}

	.awp_bale_offer h1 {
		font-size: 35px;
		color: #006B9F;
	}

	.awp_bale_offer h3 {
		font-size: 25px;
		color: #000000;
	}
</style>
<div class="row awp_bale_offer">
	<div class="">
		<h1>
			<?php esc_html_e('Plugin Bale Offer', 'media-slider'); ?>
		</h1>
		<h3>
			<?php esc_html_e('Get All Premium Plugin ( Personal Licence) in just $179', 'media-slider'); ?>
		</h3>
		<h3><strike>$399</strike>
			<?php esc_html_e('For $179 Only', 'media-slider'); ?>
		</h3>
	</div>
	<div class="">
		<a href="https://awplife.com/account/signup/all-premium-plugins" target="_blank"
			class="button button-primary button-hero load-customize hide-if-no-customize">
			<?php esc_html_e('BUY NOW', 'media-slider'); ?>
		</a>
	</div>
</div>
<hr>
<p class="">
<h2><strong>
		<?php esc_html_e('Try Our Other Plugins:', 'media-slider'); ?>
	</strong></h2>
<br>
<a href="https://wordpress.org/plugins/portfolio-filter-gallery/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Portfolio Filter Gallery</a>
<a href="https://wordpress.org/plugins/new-grid-gallery/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Grid Gallery</a>
<a href="https://wordpress.org/plugins/new-photo-gallery/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Photo Gallery</a>
<a href="https://wordpress.org/plugins/responsive-slider-gallery/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Responsive Slider Gallery</a>
<a href="https://wordpress.org/plugins/new-contact-form-widget/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Contact Form Widget</a>
<a href="https://wordpress.org/plugins/slider-responsive-slideshow/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Slider Responsive Slideshow</a>
<a href="https://wordpress.org/plugins/new-video-gallery/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Video Gallery</a>
<a href="https://wordpress.org/plugins/facebook-likebox-widget-and-shortcode/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Facebook Likebox Plugin</a>
<a href="https://wordpress.org/plugins/new-google-plus-badge/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Google Plus Badge</a><br><br>
<a href="https://wordpress.org/plugins/new-social-media-widget/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Social Media</a>
<a href="https://wordpress.org/plugins/media-slider/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Media Slider</a>
<a href="https://wordpress.org/plugins/weather-effect/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Weather Effect</a>
<a href="https://wordpress.org/plugins/modal-popup-box/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Modal Popup Box</a>
<a href="https://wordpress.org/plugins/wp-flickr-gallery/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Flickr gallery</a>
<a href="https://wordpress.org/plugins/floating-news-headline/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Floating News Headline</a><br><br>
<a href="https://wordpress.org/plugins/insta-type-gallery/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Instagram type Gallery</a>
<a href="https://wordpress.org/plugins/new-image-gallery/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Image Gallery</a>
<a href="https://wordpress.org/plugins/facebook-likebox-widget-and-shortcode/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Facebook Likebox Plugin</a>
<a href="https://wordpress.org/plugins/testimonial-maker/" target="_blank"
	class="button button-primary load-customize hide-if-no-customize">Testimonial</a>
</p>