<?php

class Themeist_IRecommendThis_Public {

	/**
	 * @param string $plugin_file
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	public function add_public_hooks() {
	    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	    add_filter( 'the_content', array( $this, 'dot_content' ) );
	    // add_shortcode( 'dot_recommends', array( $this, 'shortcode' ) );
	    // add_shortcode( 'dot_recommended_posts', array( $this, 'dot_recommended_top_posts' ) );
	}

	/*--------------------------------------------*
	 * Enqueue Scripts
	 *--------------------------------------------*/

	function enqueue_scripts()
	{
		// Get plugin settings
		$options = get_option('dot_irecommendthis_settings');

		// Validate and set default values for CSS options
		$disable_css = isset($options['disable_css']) ? intval($options['disable_css']) : 0;
		$recommend_style = isset($options['recommend_style']) ? intval($options['recommend_style']) : 0;

		// Enqueue styles if CSS is not disabled
		if ($disable_css === 0) {
			$css_file = ($recommend_style === 0) ? 'dot-irecommendthis.css' : 'dot-irecommendthis-heart.css';
			wp_enqueue_style('dot-irecommendthis', plugins_url('/css/' . $css_file, $this->plugin_file));
		}

		// Register and enqueue the main JavaScript file
		wp_register_script('dot-irecommendthis', plugins_url('/js/dot_irecommendthis.js', $this->plugin_file), array('jquery'), '2.6.0', true);
		wp_enqueue_script('dot-irecommendthis');

		// Enqueue jQuery, if not already enqueued
		wp_enqueue_script('jquery');

		// Create a nonce for secure AJAX requests and localize it
		$nonce = wp_create_nonce('dot-irecommendthis-nonce');
		wp_localize_script('dot-irecommendthis', 'dot_irecommendthis', array(
			'nonce' => $nonce,
			'ajaxurl' => admin_url('admin-ajax.php')
		));
	}

	/*--------------------------------------------*
	 * Content / Front-end view
	 *--------------------------------------------*/

	public function dot_content( $content ) {
	    if ( is_page_template() || is_page() || is_front_page() ) {
	        return $content;
	    }

	    global $wp_current_filter;
	    if ( in_array( 'get_the_excerpt', (array) $wp_current_filter, true ) ) {
	        return $content;
	    }

	    $options = get_option( 'dot_irecommendthis_settings' );
	    $add_to_posts = isset( $options['add_to_posts'] ) ? $options['add_to_posts'] : '0';
	    $add_to_other = isset( $options['add_to_other'] ) ? $options['add_to_other'] : '0';

	    if ( is_singular( 'post' ) && $add_to_posts ) {
	        $content .= Themeist_IRecommendThis_Shortcodes::dot_recommend();
	    }
	    if ( ( is_home() || is_category() || is_tag() || is_author() || is_date() || is_search() ) && $add_to_other ) {
	        $content .= Themeist_IRecommendThis_Shortcodes::dot_recommend();
	    }

	    return $content;
	}

	/*--------------------------------------------*
	 * Main Process
	 *--------------------------------------------*/
	public static function dot_recommend_this($post_id, $text_zero_suffix = false, $text_one_suffix = false, $text_more_suffix = false, $action = 'get')
	{
		global $wpdb;

		if (!is_numeric($post_id)) return;

		$text_zero_suffix = sanitize_text_field($text_zero_suffix);
		$text_one_suffix = sanitize_text_field($text_one_suffix);
		$text_more_suffix = sanitize_text_field($text_more_suffix);

		switch ($action) {
			case 'get':
				$recommended = get_post_meta($post_id, '_recommended', true);

				if (!$recommended) {
					$recommended = 0;
					add_post_meta($post_id, '_recommended', $recommended, true);
				}

				if ($recommended == 0) {
					$suffix = $text_zero_suffix;
				} elseif ($recommended == 1) {
					$suffix = $text_one_suffix;
				} else {
					$suffix = $text_more_suffix;
				}

				// Check if zero should be hidden
				$options = get_option('dot_irecommendthis_settings');
				$hide_zero = isset($options['hide_zero']) ? $options['hide_zero'] : '0';

				if ($recommended == 0 && $hide_zero == 1) {
					$output = '<span class="dot-irecommendthis-count">&nbsp;</span> <span class="dot-irecommendthis-suffix">' . esc_html($suffix) . '</span>';
				} else {
					$output = '<span class="dot-irecommendthis-count">' . esc_html($recommended) . '</span> <span class="dot-irecommendthis-suffix">' . esc_html($suffix) . '</span>';
				}

				return apply_filters('dot_irt_before_count', $output);

			case 'update':
				$recommended = get_post_meta($post_id, '_recommended', true);

				$options = get_option('dot_irecommendthis_settings');
				$enable_unique_ip = isset($options['enable_unique_ip']) ? $options['enable_unique_ip'] : '0';

				if ($enable_unique_ip != 0) {
					$ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);

					if ($_POST['unrecommend'] == 'true') {
						$sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $ip);
						$wpdb->query($sql);
					} else {
						$sql = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $ip);
						$voteStatusByIp = $wpdb->get_var($sql);
						$sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}irecommendthis_votes VALUES ('', NOW(), %d, %s )", $post_id, $ip);
						$wpdb->query($sql);
					}
				}

				if (isset($_COOKIE['dot_irecommendthis_' . $post_id]) && $_POST['unrecommend'] == 'false') {
					return $recommended;
				}

				if ($_POST['unrecommend'] == 'true') {
					if (isset($_COOKIE['dot_irecommendthis_' . $post_id])) {
						// Set the cookie to expire in the past to delete it
						setcookie('dot_irecommendthis_' . $post_id, '', time() - 3600, '/');
					}
					$recommended--;
				} else {
					setcookie('dot_irecommendthis_' . $post_id, time(), time() + 3600 * 24 * 365, '/');
					$recommended++;
				}

				update_post_meta($post_id, '_recommended', $recommended);

				if ($recommended == 0) {
					$suffix = $text_zero_suffix;
				} elseif ($recommended == 1) {
					$suffix = $text_one_suffix;
				} else {
					$suffix = $text_more_suffix;
				}

				$output = '<span class="dot-irecommendthis-count">' . esc_html($recommended) . '</span> <span class="dot-irecommendthis-suffix">' . esc_html($suffix) . '</span>';
				return apply_filters('dot_irt_before_count', $output);
		}
	}


}
