<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/*
@package Media Slider
Plugin Name: Media Slider
Plugin URI: http://awplife.com/
Description: The best images slider plugin with image and video slideshow support.
Version: 1.5.0
Author: A WP Life
Author URI: https://awplife.com/
Text Domain: media-slider
Domain Path: /languages

Media Slider is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Media Slider is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Media Slider. If not, see https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html.
*/

if (!class_exists('Awl_Media_Slider')) {

	class Awl_Media_Slider
	{

		public function __construct()
		{
			$this->_constants();
			$this->_hooks();
		}

		protected function _constants()
		{

			// Plugin Version
			define('MS_PLUGIN_VER', '1.5.0');

			// Plugin Text Domain
			define('MSP_TXTDM', 'media-slider');

			// Plugin Name
			define('MS_PLUGIN_NAME', __('Media Slider', MSP_TXTDM));

			// Plugin Slug
			define('MS_PLUGIN_SLUG', 'media_slider');

			// Plugin Directory Path
			define('MS_PLUGIN_DIR', plugin_dir_path(__FILE__));

			// Plugin Driectory URL
			define('MS_PLUGIN_URL', plugin_dir_url(__FILE__));

			/**
			 * Create a key for the .htaccess secure download link.
			 *
			 * @uses    NONCE_KEY     Defined in the WP root config.php
			 */
			define('MSP_SECURE_KEY', md5(NONCE_KEY));

		} // end of constructor function

		/**
		 * Setup the default filters and actions
		 */
		protected function _hooks()
		{

			// Load Text Domain
			add_action('plugins_loaded', array($this, '_load_textdomain'));

			// Add Slider Menu Item
			add_action('admin_menu', array($this, '_srgallery_menu'));

			// Create Media Slider Pro Custom Post
			add_action('init', array($this, '_Media_Slider'));

			// Add Meta Box To Custom Post
			add_action('add_meta_boxes', array($this, '_ms_admin_add_meta_box'));

			add_action('wp_ajax_media_slider_js', array(&$this, 'ajax_media_slider'));

			add_action('save_post', array(&$this, '_ms_save_settings'));

			// Shortcode Compatibility in Text Widegts
			add_filter('widget_text', 'do_shortcode');

			// add ms cpt shortcode column - manage_{$post_type}_posts_columns
			add_filter('manage_media_slider_posts_columns', array(&$this, 'set_media_slider_shortcode_column_name'));

			// add ms cpt shortcode column data - manage_{$post_type}_posts_custom_column
			add_action('manage_media_slider_posts_custom_column', array(&$this, 'custom_media_slider_shodrcode_data'), 10, 2);

			add_action('wp_enqueue_scripts', array(&$this, 'media_enqueue_scripts_in_header'));

		} // end of hook function

		public function media_enqueue_scripts_in_header()
		{
			wp_enqueue_script('jquery');
		}

		// media slider cpt shortcode column before date columns
		public function set_media_slider_shortcode_column_name($defaults)
		{
			$new = array();
			$shortcode = $columns['media_slider_shortcode'];  // save the tags column
			unset($defaults['tags']);   // remove it from the columns list

			foreach ($defaults as $key => $value) {
				if ($key == 'date') {  // when we find the date column
					$new['media_slider_shortcode'] = __('Shortcode', 'media-slider');  // put the tags column before it
				}
				$new[$key] = $value;
			}
			return $new;
		}

		// media slider cpt shortcode column data
		public function custom_media_slider_shodrcode_data($column, $post_id)
		{
			switch ($column) {
				case 'media_slider_shortcode':
					echo "<input type='text' class='button button-primary' id='media-slider-shortcode-" . esc_attr($post_id) . "' value='[MDSL id=" . esc_attr($post_id) . "]' style='font-weight:bold; background-color:#32373C; color:#FFFFFF; text-align:center;' />";
					echo "<input type='button' class='button button-primary' onclick='return  MEDIACopyShortcode" . esc_attr($post_id) . "();' readonly value='Copy' style='margin-left:4px;' />";
					echo "<span id='copy-msg-" . esc_attr($post_id) . "' class='button button-primary' style='display:none; background-color:#32CD32; color:#FFFFFF; margin-left:4px; border-radius: 4px;'>copied</span>";
					echo '<script>
						function  MEDIACopyShortcode' . esc_attr($post_id) . "() {
							var copyText = document.getElementById('media-slider-shortcode-" . esc_attr($post_id) . "');
							copyText.select();
							document.execCommand('copy');
							
							//fade in and out copied message
							jQuery('#copy-msg-" . esc_attr($post_id) . "').fadeIn('1000', 'linear');
							jQuery('#copy-msg-" . esc_attr($post_id) . "').fadeOut(2500,'swing');
						}
						</script>
					";
					break;
			}
		}

		public function _load_textdomain()
		{
			load_plugin_textdomain('media-slider', false, dirname(plugin_basename(__FILE__)) . '/languages');
		}

		public function _srgallery_menu()
		{
			$help_menu = add_submenu_page('edit.php?post_type=' . MS_PLUGIN_SLUG, __('Docs', 'media-slider'), __('Docs', 'media-slider'), 'administrator', 'sr-doc-page', array($this, '_ms_doc_page'));
			$ms_featured_plugin_menu = add_submenu_page('edit.php?post_type=' . MS_PLUGIN_SLUG, __('Featured-Plugin', 'media-slider'), __('Featured Plugin', 'media-slider'), 'administrator', 'sr--media-featured-plugin-page', array($this, '_ms_featured_plugin_page'));
			$theme_menu = add_submenu_page('edit.php?post_type=' . MS_PLUGIN_SLUG, __('Our Theme', 'media-slider'), __('Our Theme', 'media-slider'), 'administrator', 'sr-theme-page', array($this, '_ms_theme_page'));
		}

		/**
		 * Media Slider Custom Post
		 * Create slider post type in admin dashboard.
		 */
		public function _Media_Slider()
		{
			$labels = array(
				'name' => _x('Media Slider', 'post type general name', 'media-slider'),
				'singular_name' => _x('Media Slider', 'post type singular name', 'media-slider'),
				'menu_name' => __('Media Slider', 'media-slider'),
				'name_admin_bar' => __('Media Slider', 'media-slider'),
				'parent_item_colon' => __('Parent Item', 'media-slider'),
				'all_items' => __('All Media Slider', 'media-slider'),
				'add_new_item' => __('Add Media Slider', 'media-slider'),
				'add_new' => __('Add Media Slider', 'media-slider'),
				'new_item' => __('Media Slider', 'media-slider'),
				'edit_item' => __('Edit Media Slider', 'media-slider'),
				'update_item' => __('Update Media Slider', 'media-slider'),
				'search_items' => __('Search Media Slider', 'media-slider'),
				'not_found' => __('Media Slider Not found', 'media-slider'),
				'not_found_in_trash' => __('Media Slider Not found in Trash', 'media-slider'),
			);

			$args = array(
				'label' => __('Media Slider', 'media-slider'),
				'description' => __('Custom Post Type For Media Slider', 'media-slider'),
				'labels' => $labels,
				'supports' => array('title'),
				'taxonomies' => array(),
				'hierarchical' => false,
				'public' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'menu_position' => 65,
				'menu_icon' => 'dashicons-images-alt2',
				'show_in_admin_bar' => true,
				'show_in_nav_menus' => true,
				'can_export' => true,
				'has_archive' => true,
				'exclude_from_search' => false,
				'publicly_queryable' => true,
				'capability_type' => 'page',
			);

			register_post_type('media_slider', $args);
		}//end _Media_Slider()

		/**
		 * Adds Meta Boxes
		 */
		public function _ms_admin_add_meta_box()
		{
			// Syntax: add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args );
			add_meta_box(__('Add Image/Poster', 'media-slider'), __('Add Image/Poster', 'media-slider'), array(&$this, 'ms_upload_multiple_images'), 'media_slider', 'normal', 'default');
			add_meta_box(__('Copy Media Slider Shortcode', 'media-slider'), __('Copy Media Slider Shortcode', 'media-slider'), array(&$this, '_ms_shortcode_left_metabox'), 'media_slider', 'side', 'default');
			add_meta_box(__('Upgrade Media Slider Pro', 'media-slider'), __('Upgrade Media Slider Pro', 'media-slider'), array(&$this, 'ms_upgrade_pro'), 'media_slider', 'side', 'default');
			add_meta_box(__('Rate Our Plugin', 'media-slider'), __('Rate Our Plugin', 'media-slider'), array(&$this, 'ms_rate_plugin'), 'media_slider', 'side', 'default');
		}

		// image gallery copy shortcode meta box under publish button
		public function _ms_shortcode_left_metabox($post)
		{ ?>
			<p class="input-text-wrap">
				<input type="text" name="shortcode" id="shortcode" value="<?php echo esc_attr("[MDSL id=" . $post->ID . "]"); ?>"
					readonly style="height: 60px; text-align: center; width:100%;  font-size: 26px; border: 2px dashed;">
			<p id="ms-copy-code">
				<?php esc_html_e('Shortcode copied to clipboard!', 'media-slider'); ?>
			</p>
			<p style="margin-top: 10px">
				<?php esc_html_e('Copy & Embed shotcode into any Page/ Post / Text Widget to display slider.', 'rmedia-slider'); ?>
			</p>
			</p>
			<span onclick="copyToClipboard('#shortcode')" class="ms-copy dashicons dashicons-clipboard"></span>
			<style>
				.ms-copy {
					position: absolute;
					top: 9px;
					right: 30px;
					font-size: 30px;
					cursor: pointer;
				}

				.ui-sortable-handle>span {
					font-size: 16px !important;
				}
			</style>
			<script>
				jQuery("#ms-copy-code").hide();
				function copyToClipboard(element) {
					var $temp = jQuery("<input>");
					jQuery("body").append($temp);
					$temp.val(jQuery(element).val()).select();
					document.execCommand("copy");
					$temp.remove();
					jQuery("#shortcode").select();
					jQuery("#ms-copy-code").fadeIn();
				}
			</script>
			<?php
		}

		// meta upgrade pro
		public function ms_upgrade_pro()
		{ ?>
			<img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'image/m.jpg'); ?>" width="250" height="280">
			<a href="https://awplife.com/demo/media-slider-premium/" target="_new" class="button button-primary button-large"
				style="background: #EF3E36; text-shadow: none; margin-top:10px"><span class="dashicons dashicons-search"
					style="line-height:1.4;"></span>
				<?php esc_html_e('Live Demo', 'media-slider'); ?>
			</a>
			<a href="https://awplife.com/account/signup/media-slider-premium" target="_new"
				class="button button-primary button-large" style="background: #EF3E36; text-shadow: none; margin-top:10px"><span
					class="dashicons dashicons-unlock" style="line-height:1.4;"></span>
				<?php esc_html_e('Upgrade Pro', 'media-slider'); ?>
			</a>
			<?php
		}
		// meta rate us
		public function ms_rate_plugin()
		{
			?>
			<div style="text-align:center">
				<p>
					<?php esc_html_e('If you like our plugin then please', 'media-slider'); ?> <b>
						<?php esc_html_e('Rate us', 'media-slider'); ?>
					</b>
					<?php esc_html_e('on WordPress', 'media-slider'); ?>
				</p>
			</div>
			<div style="text-align:center">
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
			</div>
			<br>
			<div style="text-align:center">
				<a href="https://wordpress.org/support/plugin/media-slider/reviews/?filter=5" target="_new"
					class="button button-primary button-large" style="background: #EF3E36; text-shadow: none;"><span
						class="dashicons dashicons-heart" style="line-height:1.4;"></span>
					<?php esc_html_e('Please Rate Us', 'media-slider'); ?>
				</a>
			</div>
			<?php
		}

		public function ms_upload_multiple_images($post)
		{
			wp_enqueue_script('media-upload');
			wp_enqueue_script('awl-ms-uploader.js', MS_PLUGIN_URL . 'js/awl-ms-uploader.js', array('jquery'));
			wp_enqueue_style('awl-ms-uploader-css', MS_PLUGIN_URL . 'css/awl-ms-uploader.css');
			wp_enqueue_style('style-css', MS_PLUGIN_URL . 'css/styles.css');
			wp_enqueue_style('awl-em-pe-icon-7-stroke-css', MS_PLUGIN_URL . 'css/pe-icon-7-stroke.css');
			wp_enqueue_media();
			?>
			<div id="media-slider-gallery">
				<input type="button" id="remove-all-media-slides" name="remove-all-media-slides"
					class="button button-large remove-all-media-slides" rel=""
					value="<?php esc_html_e('Delete All Images', 'media-slider'); ?>">
				<ul id="remove-media-slides" class="mediabox">
					<?php
					$post_id = esc_attr($post->ID);

					function is_ms_serialized($str)
					{
						return($str == serialize(false) || @unserialize($str) !== false);
					}

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
					if (isset($slider_settings['media-slide-ids'])) {
						$count = 0;
						foreach ($slider_settings['media-slide-ids'] as $id) {
							$thumbnail = wp_get_attachment_image_src($id, 'medium', true);
							$attachment = get_post($id);
							$slide_link = $slider_settings['media-slide-link'][$count];
							$slide_type = $slider_settings['media-slide-type'][$count];
							?>
							<li class="media-slide">
								<img class="new-media-slide" src="<?php echo esc_url($thumbnail[0]); ?>"
									alt="<?php echo esc_html(get_the_title($id)); ?>"
									style="height: 150px; width: 98%; border-radius: 8px;">
								<input type="hidden" id="media-slide-ids[]" name="media-slide-ids[]"
									value="<?php echo esc_attr($id); ?>" />
								<!-- Image Title, Caption, Alt Text-->
								<select id="media-slide-type[]" name="media-slide-type[]" class="form-control" style="width: 100%;"
									value="<?php echo esc_html($slide_type); ?>">
									<option value="i" <?php
									if ($slide_type == 'i') {
										echo 'selected=selected';
									}
									?>>
										<?php esc_html_e('Image', 'media-slider'); ?>
									</option>
									<option value="v" <?php
									if ($slide_type == 'v') {
										echo 'selected=selected';
									}
									?>>
										<?php esc_html_e('Video', 'media-slider'); ?>
									</option>
								</select>
								<input type="text" name="media-slide-link[]" id="media-slide-link[]" style="width: 100%;"
									placeholder="<?php esc_html_e('Enter URL / ID', 'media-slider'); ?>"
									value="<?php echo esc_url($slide_link); ?>">
								<input type="text" name="media-slide-title[]" id="media-slide-title[]" style="width: 100%;"
									placeholder="<?php esc_html_e('Title Here', 'media-slider'); ?>"
									value="<?php echo esc_html(get_the_title($id)); ?>">
								<textarea name="media-slide-desc[]" id="media-slide-desc[]" style="width: 100%;"
									placeholder="<?php esc_html_e('Enter Description', 'media-slider'); ?>"><?php echo esc_html($attachment->post_content); ?></textarea>
								<input type="button" name="remove-media-slide" id="remove-media-slide"
									class="button remove-single-media-slide button-danger" style="width: 100%;"
									value="<?php esc_html_e('Delete', 'media-slider'); ?>">
							</li>
							<?php
							$count++;
						} // end of foreach
					} //end of if
					?>
				</ul>
			</div>

			<!--Add New Image/Video Button-->
			<div name="add-new-media-slider" id="add-new-media-slider" class="new-media-slider"
				style="height: 145px; width: 150px; border-radius: 20px;">
				<img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'css/new-slide.png'); ?>" height="100px" width="100px;" />
			</div><br><br>
			<span class="add-text">
				<?php esc_html_e('Add Image / Poster', 'media-slider'); ?>
			</span>
			<?php wp_nonce_field('msp_add_images', 'msp_add_images_nonce'); ?>
			<div style="clear:left;"></div>
			<hr>
			<br>
			<h1 style="font-family:Geneva;">
				<?php esc_html_e('Media Slider Setting', 'media-slider'); ?>
			</h1>
			<hr>
			<?php
			require_once 'media-slider-settings.php';
		}

		public function _ms_ajax_callback_function($id)
		{
			// wp_get_attachment_image_src ( int $attachment_id, string|array $size = 'thumbnail', bool $icon = false )
			// thumb, thumbnail, medium, large, post-thumbnail
			$thumbnail = wp_get_attachment_image_src($id, 'medium', true);
			$attachment = get_post($id); // $id = attachment id
			?>
			<li class="media-slide">
				<img class="new-media-slide" src="<?php echo esc_url($thumbnail[0]); ?>"
					alt="<?php echo esc_html(get_the_title($id)); ?>" style="height: 150px; width: 98%; border-radius: 8px;">
				<input type="hidden" id="media-slide-ids[]" name="media-slide-ids[]" value="<?php echo esc_attr($id); ?> )" />
				<select id="media-slide-type[]" name="media-slide-type[]" class="form-control" style="width: 100%;"
					placeholder="Image Title" value="<?php echo esc_html($slide_type); ?>">
					<option value="i" <?php
					if ($slide_type == 'i') {
						echo 'selected=selected';
					}
					?>>
						<?php esc_html_e('Image', 'media-slider'); ?>
					</option>
					<option value="v" <?php
					if ($slide_type == 'v') {
						echo 'selected=selected';
					}
					?>>
						<?php esc_html_e('Video', 'media-slider'); ?>
					</option>
				</select>
				<input type="text" name="media-slide-link[]" id="media-slide-link[]" style="width: 100%;"
					placeholder="<?php esc_html_e('Enter Image / Video URL', 'media-slider'); ?>">
				<input type="text" name="media-slide-title[]" id="media-slide-title[]" style="width: 100%;"
					placeholder="<?php esc_html_e('Title Here', 'media-slider'); ?>"
					value="<?php echo esc_html(get_the_title($id)); ?>">
				<textarea name="media-slide-desc[]" id="media-slide-desc[]" style="width: 100%;"
					placeholder="<?php esc_html_e('Enter Description', 'media-slider'); ?>"><?php echo esc_html($attachment->post_content); ?></textarea>
				<input type="button" name="remove-media-slide" id="remove-media-slide" style="width: 100%;" class="button"
					value="<?php esc_html_e('Delete', 'media-slider'); ?>">
			</li>
			<?php
		}

		public function ajax_media_slider()
		{
			if (current_user_can('manage_options')) {
				if (isset($_POST['msp_add_images_nonce']) && wp_verify_nonce($_POST['msp_add_images_nonce'], 'msp_add_images')) {
					echo esc_attr($this->_ms_ajax_callback_function($_POST['slideId']));
				} else {
					print 'Sorry, your nonce did not verify.';
					exit;
				}
			}
		}

		public function _ms_save_settings($post_id)
		{
			if (current_user_can('manage_options')) {
				if (isset($_POST['ms_save_nonce'])) {
					if (isset($_POST['ms_save_nonce']) && wp_verify_nonce($_POST['ms_save_nonce'], 'ms_save_settings')) {

						$width = sanitize_text_field($_POST['width']);
						$height = sanitize_text_field($_POST['height']);
						$slide_autoheight = sanitize_text_field($_POST['slide_autoheight']);
						$slide_imagescalemode = sanitize_text_field($_POST['slide_imagescalemode']);
						$slide_imagecenter = sanitize_text_field($_POST['slide_imagecenter']);
						$slide_scaleup = sanitize_text_field($_POST['slide_scaleup']);
						$slide_autoslidesize = sanitize_text_field($_POST['slide_autoslidesize']);
						$shuffle_slide = sanitize_text_field($_POST['shuffle_slide']);
						$slide_caption = sanitize_text_field($_POST['slide_caption']);
						$slide_loop = sanitize_text_field($_POST['slide_loop']);
						$slide_visiblesize = sanitize_text_field($_POST['slide_visiblesize']);
						$slide_waitforlayers = sanitize_text_field($_POST['slide_waitforlayers']);
						$slide_autoscalelayers = sanitize_text_field($_POST['slide_autoscalelayers']);
						$custom_css = sanitize_text_field($_POST['custom_css']);
						$slide_autoplay = sanitize_text_field($_POST['slide_autoplay']);
						$slide_autoplay_delay = sanitize_text_field($_POST['slide_autoplay_delay']);
						$slide_autoplay_hover = sanitize_text_field($_POST['slide_autoplay_hover']);
						$slide_arrows = sanitize_text_field($_POST['slide_arrows']);
						$slide_fullscreen_btn = sanitize_text_field($_POST['slide_fullscreen_btn']);
						$slide_thumb = sanitize_text_field($_POST['slide_thumb']);
						$slide_thumb_width = sanitize_text_field($_POST['slide_thumb_width']);
						$slide_thumb_height = sanitize_text_field($_POST['slide_thumb_height']);
						$slide_thumb_pos = sanitize_text_field($_POST['slide_thumb_pos']);
						$slide_thumb_arrows = sanitize_text_field($_POST['slide_thumb_arrows']);
						$slide_thumb_touchswipe = sanitize_text_field($_POST['slide_thumb_touchswipe']);
						$videoaction_play = sanitize_text_field($_POST['videoaction_play']);
						$videoaction_pause = sanitize_text_field($_POST['videoaction_pause']);
						$slide_text = sanitize_text_field($_POST['slide_text']);
						$slide_text_pos = sanitize_text_field($_POST['slide_text_pos']);
						$i = 0;
						$image_ids = array();
						$image_titles = array();
						$image_type = array();
						$slide_link = array();
						$image_descs = array();
						$image_ids_val = isset($_POST['media-slide-ids']) ? (array) $_POST['media-slide-ids'] : array();
						$image_ids_val = array_map('sanitize_text_field', $image_ids_val);

						foreach ($image_ids_val as $image_id) {
							$image_ids[] = sanitize_text_field($_POST['media-slide-ids'][$i]);
							$image_titles[] = sanitize_text_field($_POST['media-slide-title'][$i]);
							$image_type[] = sanitize_text_field($_POST['media-slide-type'][$i]);
							$slide_link[] = sanitize_text_field($_POST['media-slide-link'][$i]);
							$image_descs[] = sanitize_text_field($_POST['media-slide-desc'][$i]);

							$single_image_update = array(
								'ID' => $image_id,
								'post_title' => $image_titles[$i],
								'post_content' => $image_descs[$i],
							);
							wp_update_post($single_image_update);
							$i++;
						}

						$slider_settings = array(
							'media-slide-ids' => $image_ids,
							'media-slide-title' => $image_titles,
							'media-slide-type' => $image_type,
							'media-slide-link' => $slide_link,
							'media-slide-desc' => $image_descs,
							'width' => $width,
							'height' => $height,
							'slide_autoheight' => $slide_autoheight,
							'slide_imagescalemode' => $slide_imagescalemode,
							'slide_imagecenter' => $slide_imagecenter,
							'slide_scaleup' => $slide_scaleup,
							'slide_autoslidesize' => $slide_autoslidesize,
							'shuffle_slide' => $shuffle_slide,
							'slide_caption' => $slide_caption,
							'slide_loop' => $slide_loop,
							'slide_visiblesize' => $slide_visiblesize,
							'slide_waitforlayers' => $slide_waitforlayers,
							'slide_autoscalelayers' => $slide_autoscalelayers,
							'custom_css' => $custom_css,
							'slide_autoplay' => $slide_autoplay,
							'slide_autoplay_delay' => $slide_autoplay_delay,
							'slide_autoplay_hover' => $slide_autoplay_hover,
							'slide_arrows' => $slide_arrows,
							'slide_fullscreen_btn' => $slide_fullscreen_btn,
							'slide_thumb' => $slide_thumb,
							'slide_thumb_width' => $slide_thumb_width,
							'slide_thumb_height' => $slide_thumb_height,
							'slide_thumb_pos' => $slide_thumb_pos,
							'slide_thumb_arrows' => $slide_thumb_arrows,
							'slide_thumb_touchswipe' => $slide_thumb_touchswipe,
							'videoaction_play' => $videoaction_play,
							'videoaction_pause' => $videoaction_pause,
							'slide_text' => $slide_text,
							'slide_text_pos' => $slide_text_pos,

						);

						$awl_media_slider_shortcode_setting = 'awl_ms_settings_' . $post_id;
						update_post_meta($post_id, $awl_media_slider_shortcode_setting, json_encode($slider_settings));
					} else {
						print 'Sorry, your nonce did not verify.';
						exit;
					}
				}
			}
		}//end _ms_save_settings()

		public function _ms_doc_page()
		{
			require_once 'docs.php';
		}

		public function _ms_featured_plugin_page()
		{
			require_once 'featured-plugins/featured-plugins.php';
		}

		// theme page
		public function _ms_theme_page()
		{
			require_once 'our-theme/awp-theme.php';
		}
	}//end class

	// register sf scripts
	function awplife_msp_register_scripts()
	{

		// css & JS
		wp_enqueue_script('jquery');
		wp_register_script('awl-ms-jquery-sliderPro-min-js', plugin_dir_url(__FILE__) . 'js/jquery.sliderPro.js');
		wp_register_style('awl-ms-slider-pro-min-css', plugin_dir_url(__FILE__) . 'css/awl-ms-slider-pro.min.css');
		wp_register_style('awl-ms-bootstrap-css', plugin_dir_url(__FILE__) . 'css/bootstrap.css');
		// css & JS
	}
	add_action('wp_enqueue_scripts', 'awplife_msp_register_scripts');

	// Plugin Recommend
	add_action('tgmpa_register', 'MSP_TXTDM_plugin_recommend');
	function MSP_TXTDM_plugin_recommend()
	{
		$plugins = array(
			array(
				'name' => 'Photostream Profile For Flickr',
				'slug' => 'wp-flickr-gallery',
				'required' => false,
			),
			array(
				'name' => 'Contact Form Widget',
				'slug' => 'new-contact-form-widget',
				'required' => false,
			),
			array(
				'name' => 'Testimonial – Customer Feedback',
				'slug' => 'testimonial-maker',
				'required' => false,
			),
		);
		tgmpa($plugins);
	}

	$ms_gallery_object = new Awl_Media_Slider();
	require_once 'shortcode.php';
	require_once 'class-tgm-plugin-activation.php';
}
?>