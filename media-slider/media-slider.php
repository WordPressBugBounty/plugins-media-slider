<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/*
@package Media Slider
Plugin Name: Media Slider
Plugin URI: http://awplife.com/
Description: The best images slider plugin with image and video slideshow support.
Version: 1.6.1
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
			define('MS_PLUGIN_VER', '1.6.1');

			// Plugin Text Domain
			define('MSP_TXTDM', 'media-slider');

			// Plugin Name
			define('MS_PLUGIN_NAME', 'Media Slider');

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

		public static function get_slider_settings($post_id)
		{
			$post_id = intval($post_id);
			$encodedData = get_post_meta($post_id, 'awl_ms_settings_' . $post_id, true);
			if (empty($encodedData)) {
				return array();
			}

			// 1. Try to base64-decode the raw metadata
			$decodedData = base64_decode($encodedData, true);

			// 2. If it is serialized (legacy format), unserialize it
			if ($decodedData !== false && is_serialized($decodedData)) {
				return unserialize($decodedData);
			}

			// 3. Fallback: Check if the raw data itself is serialized
			if (is_serialized($encodedData)) {
				return unserialize($encodedData);
			}

			// 4. Otherwise, handle it as standard JSON
			$slider_settings = json_decode($encodedData, true);
			return is_array($slider_settings) ? $slider_settings : array();
		}

		/**
		 * Setup the default filters and actions
		 */
		protected function _hooks()
		{

			// Load Text Domain
			add_action('init', array($this, '_load_textdomain'));


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

			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

			add_action('admin_menu', array($this, '_srgallery_menu'));

		} // end of hook function

		public function media_enqueue_scripts_in_header()
		{
			wp_enqueue_script('jquery');
		}

		public function admin_enqueue_scripts($hook)
		{
			global $post_type;
			if ('media_slider' === $post_type) {
				wp_enqueue_style('ms-bootstrap-css', MS_PLUGIN_URL . 'css/ms-bootstrap.css');
				wp_enqueue_style('ms-font-awesome-min-css', MS_PLUGIN_URL . 'css/font-awesome.min.css');
				wp_enqueue_style('ms-styles-css', MS_PLUGIN_URL . 'css/styles.css');
				wp_enqueue_style('ms-go-to-top-css', MS_PLUGIN_URL . 'css/go-to-top.css');
				wp_enqueue_style('ms-toogle-button-css', MS_PLUGIN_URL . 'css/toogle-button.css');
				wp_enqueue_style('awl-em-pe-icon-7-stroke-css', MS_PLUGIN_URL . 'css/pe-icon-7-stroke.css');
				wp_enqueue_script('jquery');
				wp_enqueue_script('ms-bootstrap-js', MS_PLUGIN_URL . 'js/bootstrap.js', array('jquery'), '', true);
				wp_enqueue_script('ms-go-to-top-js', MS_PLUGIN_URL . 'js/go-to-top.js', array('jquery'), '', true);

				wp_enqueue_script('media-upload');
				wp_enqueue_script('awl-ms-uploader.js', MS_PLUGIN_URL . 'js/awl-ms-uploader.js', array('jquery'));
				wp_enqueue_style('awl-ms-uploader-css', MS_PLUGIN_URL . 'css/awl-ms-uploader.css');
				wp_enqueue_style('style-css', MS_PLUGIN_URL . 'css/styles.css');
				wp_enqueue_media();
			}

			if (strpos($hook, 'ms-plugins-page') !== false || strpos($hook, 'ms-themes-page') !== false) {
				wp_enqueue_style('our-plugins-style', MS_PLUGIN_URL . 'css/our-plugins-style.css');
			}
		}

		// media slider cpt shortcode column before date columns
		public function set_media_slider_shortcode_column_name($defaults)
		{
			$new = array();
			foreach ($defaults as $key => $value) {
				if ($key == 'date') {
					$new['media_slider_shortcode'] = __('Shortcode', 'media-slider');
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
					echo "<input type='button' class='button button-primary' onclick='return  MEDIACopyShortcode" . esc_attr($post_id) . "();' readonly value='" . esc_attr__( 'Copy', 'media-slider' ) . "' style='margin-left:4px;' />";
					echo "<span id='copy-msg-" . esc_attr($post_id) . "' class='button button-primary' style='display:none; background-color:#32CD32; color:#FFFFFF; margin-left:4px; border-radius: 4px;'>" . esc_html__( 'copied', 'media-slider' ) . "</span>";
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
				<?php esc_html_e('Copy & Embed shortcode into any Page / Post / Text Widget to display slider.', 'media-slider'); ?>
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
			?>
			<div id="media-slider-gallery">
				<input type="button" id="remove-all-media-slides" name="remove-all-media-slides"
					class="button button-large remove-all-media-slides" rel=""
					value="<?php esc_html_e('Delete All Images', 'media-slider'); ?>">
				<ul id="remove-media-slides" class="mediabox">
					<?php
					$post_id = intval($post->ID);
					$slider_settings = self::get_slider_settings($post_id);

					if (isset($slider_settings['media-slide-ids']) && is_array($slider_settings['media-slide-ids'])) {
						$count = 0;
						foreach ($slider_settings['media-slide-ids'] as $id) {
							$id = intval($id);
							$thumbnail = wp_get_attachment_image_src($id, 'medium', true);
							$thumbnail_url = is_array($thumbnail) ? $thumbnail[0] : '';
							$attachment = get_post($id);
							$slide_link = isset($slider_settings['media-slide-link'][$count]) ? $slider_settings['media-slide-link'][$count] : '';
							$slide_type = isset($slider_settings['media-slide-type'][$count]) ? $slider_settings['media-slide-type'][$count] : 'i';
							$slide_title = $attachment ? $attachment->post_title : (isset($slider_settings['media-slide-title'][$count]) ? $slider_settings['media-slide-title'][$count] : '');
							$slide_desc = $attachment ? $attachment->post_content : (isset($slider_settings['media-slide-desc'][$count]) ? $slider_settings['media-slide-desc'][$count] : '');
							?>
							<li class="media-slide">
								<img class="new-media-slide" src="<?php echo esc_url($thumbnail_url); ?>"
									alt="<?php echo esc_html($slide_title); ?>"
									style="height: 150px; width: 98%; border-radius: 8px;">
								<input type="hidden" name="media-slide-ids[]"
									value="<?php echo esc_attr($id); ?>" />
								<!-- Image Title, Caption, Alt Text-->
								<select name="media-slide-type[]" class="form-control" style="width: 100%;">
									<option value="i" <?php
									if ($slide_type == 'i') {
										echo 'selected="selected"';
									}
									?>>
										<?php esc_html_e('Image', 'media-slider'); ?>
									</option>
									<option value="v" <?php
									if ($slide_type == 'v') {
										echo 'selected="selected"';
									}
									?>>
										<?php esc_html_e('Video', 'media-slider'); ?>
									</option>
								</select>
								<input type="text" name="media-slide-link[]" style="width: 100%;"
									placeholder="<?php esc_html_e('Enter URL / ID', 'media-slider'); ?>"
									value="<?php echo esc_url($slide_link); ?>">
								<input type="text" name="media-slide-title[]" style="width: 100%;"
									placeholder="<?php esc_html_e('Title Here', 'media-slider'); ?>"
									value="<?php echo esc_html($slide_title); ?>">
								<textarea name="media-slide-desc[]" style="width: 100%;"
									placeholder="<?php esc_html_e('Enter Description', 'media-slider'); ?>"><?php echo esc_html($slide_desc); ?></textarea>
								<input type="button" name="remove-media-slide"
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
			require_once MS_PLUGIN_DIR . 'media-slider-settings.php';
		}

		public function _ms_ajax_callback_function($id)
		{
			// wp_get_attachment_image_src ( int $attachment_id, string|array $size = 'thumbnail', bool $icon = false )
			// thumb, thumbnail, medium, large, post-thumbnail
			$thumbnail = wp_get_attachment_image_src($id, 'medium', true);
			$attachment = get_post($id); // $id = attachment id
			$slide_type = 'i';
			?>
			<li class="media-slide">
				<img class="new-media-slide" src="<?php echo esc_url($thumbnail[0]); ?>"
					alt="<?php echo esc_html(get_the_title($id)); ?>" style="height: 150px; width: 98%; border-radius: 8px;">
				<input type="hidden" name="media-slide-ids[]" value="<?php echo esc_attr($id); ?>" />
				<select name="media-slide-type[]" class="form-control" style="width: 100%;">
					<option value="i" <?php
					if ($slide_type == 'i') {
						echo 'selected="selected"';
					}
					?>>
						<?php esc_html_e('Image', 'media-slider'); ?>
					</option>
					<option value="v" <?php
					if ($slide_type == 'v') {
						echo 'selected="selected"';
					}
					?>>
						<?php esc_html_e('Video', 'media-slider'); ?>
					</option>
				</select>
				<input type="text" name="media-slide-link[]" style="width: 100%;"
					placeholder="<?php esc_html_e('Enter Image / Video URL', 'media-slider'); ?>">
				<input type="text" name="media-slide-title[]" style="width: 100%;"
					placeholder="<?php esc_html_e('Title Here', 'media-slider'); ?>"
					value="<?php echo esc_html(get_the_title($id)); ?>">
				<textarea name="media-slide-desc[]" style="width: 100%;"
					placeholder="<?php esc_html_e('Enter Description', 'media-slider'); ?>"><?php echo esc_html($attachment ? $attachment->post_content : ''); ?></textarea>
				<input type="button" name="remove-media-slide" style="width: 100%;" class="button remove-single-media-slide button-danger"
					value="<?php esc_html_e('Delete', 'media-slider'); ?>">
			</li>
			<?php
		}

		public function ajax_media_slider()
		{
			if (current_user_can('manage_options')) {
				if (isset($_POST['msp_add_images_nonce']) && wp_verify_nonce($_POST['msp_add_images_nonce'], 'msp_add_images')) {
					$slide_id = isset($_POST['slideId']) ? intval($_POST['slideId']) : 0;
					if ($slide_id > 0) {
						$this->_ms_ajax_callback_function($slide_id);
					}
					wp_die();
				} else {
					wp_die(__('Sorry, your nonce did not verify.', 'media-slider'), '', array('response' => 403));
				}
			}
			wp_die();
		}

		public function _ms_save_settings($post_id)
		{
			// Guard against autosaves
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return;
			}

			// Verify post type is media_slider
			if (get_post_type($post_id) !== 'media_slider') {
				return;
			}

			// Verify current user permissions to edit the slider post itself
			if (!current_user_can('edit_post', $post_id)) {
				return;
			}

			if (current_user_can('manage_options')) {
				if (isset($_POST['ms_save_nonce'])) {
					if (isset($_POST['ms_save_nonce']) && wp_verify_nonce($_POST['ms_save_nonce'], 'ms_save_settings')) {

						$width = isset($_POST['width']) ? sanitize_text_field(wp_unslash($_POST['width'])) : '960';
						$height = isset($_POST['height']) ? sanitize_text_field(wp_unslash($_POST['height'])) : '540';
						$slide_autoheight = isset($_POST['slide_autoheight']) ? sanitize_text_field(wp_unslash($_POST['slide_autoheight'])) : 'true';
						$slide_imagescalemode = isset($_POST['slide_imagescalemode']) ? sanitize_text_field(wp_unslash($_POST['slide_imagescalemode'])) : 'cover';
						$slide_imagecenter = isset($_POST['slide_imagecenter']) ? sanitize_text_field(wp_unslash($_POST['slide_imagecenter'])) : 'true';
						$slide_scaleup = isset($_POST['slide_scaleup']) ? sanitize_text_field(wp_unslash($_POST['slide_scaleup'])) : 'true';
						$slide_autoslidesize = isset($_POST['slide_autoslidesize']) ? sanitize_text_field(wp_unslash($_POST['slide_autoslidesize'])) : 'false';
						$shuffle_slide = isset($_POST['shuffle_slide']) ? sanitize_text_field(wp_unslash($_POST['shuffle_slide'])) : 'false';
						$slide_caption = isset($_POST['slide_caption']) ? sanitize_text_field(wp_unslash($_POST['slide_caption'])) : 'true';
						$slide_loop = isset($_POST['slide_loop']) ? sanitize_text_field(wp_unslash($_POST['slide_loop'])) : 'true';
						$slide_visiblesize = isset($_POST['slide_visiblesize']) ? sanitize_text_field(wp_unslash($_POST['slide_visiblesize'])) : 'auto';
						$slide_waitforlayers = isset($_POST['slide_waitforlayers']) ? sanitize_text_field(wp_unslash($_POST['slide_waitforlayers'])) : 'false';
						$slide_autoscalelayers = isset($_POST['slide_autoscalelayers']) ? sanitize_text_field(wp_unslash($_POST['slide_autoscalelayers'])) : 'true';
						$custom_css = isset($_POST['custom_css']) ? sanitize_textarea_field(wp_unslash($_POST['custom_css'])) : '';
						$slide_autoplay = isset($_POST['slide_autoplay']) ? sanitize_text_field(wp_unslash($_POST['slide_autoplay'])) : 'true';
						$slide_autoplay_delay = isset($_POST['slide_autoplay_delay']) ? sanitize_text_field(wp_unslash($_POST['slide_autoplay_delay'])) : '5000';
						$slide_autoplay_hover = isset($_POST['slide_autoplay_hover']) ? sanitize_text_field(wp_unslash($_POST['slide_autoplay_hover'])) : 'pause';
						$slide_arrows = isset($_POST['slide_arrows']) ? sanitize_text_field(wp_unslash($_POST['slide_arrows'])) : 'true';
						$slide_fullscreen_btn = isset($_POST['slide_fullscreen_btn']) ? sanitize_text_field(wp_unslash($_POST['slide_fullscreen_btn'])) : 'false';
						$slide_thumb = isset($_POST['slide_thumb']) ? sanitize_text_field(wp_unslash($_POST['slide_thumb'])) : 'true';
						$slide_thumb_width = isset($_POST['slide_thumb_width']) ? sanitize_text_field(wp_unslash($_POST['slide_thumb_width'])) : '200';
						$slide_thumb_height = isset($_POST['slide_thumb_height']) ? sanitize_text_field(wp_unslash($_POST['slide_thumb_height'])) : '100';
						$slide_thumb_pos = isset($_POST['slide_thumb_pos']) ? sanitize_text_field(wp_unslash($_POST['slide_thumb_pos'])) : 'top';
						$slide_thumb_arrows = isset($_POST['slide_thumb_arrows']) ? sanitize_text_field(wp_unslash($_POST['slide_thumb_arrows'])) : 'true';
						$slide_thumb_touchswipe = isset($_POST['slide_thumb_touchswipe']) ? sanitize_text_field(wp_unslash($_POST['slide_thumb_touchswipe'])) : 'true';
						$videoaction_play = isset($_POST['videoaction_play']) ? sanitize_text_field(wp_unslash($_POST['videoaction_play'])) : 'stopAutoplay';
						$videoaction_pause = isset($_POST['videoaction_pause']) ? sanitize_text_field(wp_unslash($_POST['videoaction_pause'])) : 'none';
						$slide_text = isset($_POST['slide_text']) ? sanitize_text_field(wp_unslash($_POST['slide_text'])) : 'true';
						$slide_text_pos = isset($_POST['slide_text_pos']) ? sanitize_text_field(wp_unslash($_POST['slide_text_pos'])) : 'bottom';
						$i = 0;
						$image_ids = array();
						$image_titles = array();
						$image_type = array();
						$slide_link = array();
						$image_descs = array();
						$image_ids_val = isset($_POST['media-slide-ids']) ? (array) $_POST['media-slide-ids'] : array();
						$image_ids_val = array_map('sanitize_text_field', $image_ids_val);

						foreach ($image_ids_val as $image_id) {
							$image_id = intval($image_id);
							if ($image_id <= 0) {
								$i++;
								continue;
							}

							// Arbitrary Post Update Prevention & Capability Check
							if (get_post_type($image_id) !== 'attachment' || !current_user_can('edit_post', $image_id)) {
								$i++;
								continue;
							}

							// Retrieve unslashed and sanitized values safely
							$title = isset($_POST['media-slide-title'][$i]) ? sanitize_text_field(wp_unslash($_POST['media-slide-title'][$i])) : '';
							$type = isset($_POST['media-slide-type'][$i]) ? sanitize_text_field(wp_unslash($_POST['media-slide-type'][$i])) : 'i';
							$link = isset($_POST['media-slide-link'][$i]) ? sanitize_text_field(wp_unslash($_POST['media-slide-link'][$i])) : '';
							// For description, use wp_kses_post to support basic HTML formatting safely
							$desc = isset($_POST['media-slide-desc'][$i]) ? wp_kses_post(wp_unslash($_POST['media-slide-desc'][$i])) : '';

							$image_ids[] = $image_id;
							$image_titles[] = $title;
							$image_type[] = $type;
							$slide_link[] = $link;
							$image_descs[] = $desc;

							$single_image_update = array(
								'ID' => $image_id,
								'post_title' => $title,
								'post_content' => $desc,
							);

							// Prevent recursive save_post loop
							remove_action('save_post', array($this, '_ms_save_settings'));
							wp_update_post($single_image_update);
							add_action('save_post', array($this, '_ms_save_settings'));

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

		public function _srgallery_menu()
		{
			add_submenu_page('edit.php?post_type=' . MS_PLUGIN_SLUG, __('Our Plugins', 'media-slider'), __('Our Plugins', 'media-slider'), 'manage_options', 'ms-plugins-page', array($this, '_ms_plugins_page'));
			add_submenu_page('edit.php?post_type=' . MS_PLUGIN_SLUG, __('Our Themes', 'media-slider'), __('Our Themes', 'media-slider'), 'manage_options', 'ms-themes-page', array($this, '_ms_themes_page'));
		}

		public function _ms_plugins_page()
		{
			require_once MS_PLUGIN_DIR . 'our-plugins.php';
		}

		public function _ms_themes_page()
		{
			require_once MS_PLUGIN_DIR . 'our-themes.php';
		}


	}//end class

	// register sf scripts
	function awplife_msp_register_scripts()
	{

		// css & JS
		wp_enqueue_script('jquery');
		wp_register_script('awl-ms-jquery-sliderPro-min-js', plugin_dir_url(__FILE__) . 'js/jquery.sliderPro.js');
		wp_register_style('awl-ms-slider-pro-min-css', plugin_dir_url(__FILE__) . 'css/awl-ms-slider-pro.min.css');
		// css & JS
	}
	add_action('wp_enqueue_scripts', 'awplife_msp_register_scripts');

	$ms_gallery_object = new Awl_Media_Slider();
	require_once MS_PLUGIN_DIR . 'shortcode.php';
}
?>