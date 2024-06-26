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
	    //add_action( 'wp_ajax_dot-irecommendthis', array( 'Themeist_IRecommendThis_Ajax', 'ajax_callback' ) );
	   // add_action( 'wp_ajax_nopriv_dot-irecommendthis', array( 'Themeist_IRecommendThis_Ajax', 'ajax_callback' ) );
	    add_filter( 'the_content', array( $this, 'dot_content' ) );
	    add_shortcode( 'dot_recommends', array( $this, 'shortcode' ) );
	    add_shortcode( 'dot_recommended_posts', array( $this, 'dot_recommended_top_posts' ) );
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

	function dot_content($content)
	{
		// Don't show on custom page templates or pages
		if (is_page_template() || is_page() || is_front_page()) return $content;

		// Don't show after excerpts
		global $wp_current_filter;
		if (in_array('get_the_excerpt', (array)$wp_current_filter)) {
			return $content;
		}

		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['add_to_posts'])) $options['add_to_posts'] = '0';
		if (!isset($options['add_to_other'])) $options['add_to_other'] = '0';

		if (is_singular('post') && $options['add_to_posts']) $content .= $this->dot_recommend();
		if ((is_home() || is_category() || is_tag() || is_author() || is_date() || is_search()) && $options['add_to_other']) $content .= $this->dot_recommend();

		return $content;

	}    //dot_content

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


	/*--------------------------------------------*
	 * Shortcode
	 *--------------------------------------------*/
	function shortcode($atts) {
		extract(shortcode_atts(array('id' => null), $atts));
		return $this->dot_recommend(intval($id));

	}    //shortcode

	function dot_recommend($id = null) {
		global $wpdb, $post;

		$ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
		$post_id = $id ? $id : get_the_ID();

		$options = get_option('dot_irecommendthis_settings');
		$default_options = array(
			'text_zero_suffix' => '',
			'text_one_suffix' => '',
			'text_more_suffix' => '',
			'link_title_new' => '',
			'link_title_active' => '',
			'enable_unique_ip' => '0',
		);
		$options = wp_parse_args($options, $default_options);

		$output = $this->dot_recommend_this($post_id, $options['text_zero_suffix'], $options['text_one_suffix'], $options['text_more_suffix']);

		$voteStatusByIp = 0;
		if ($options['enable_unique_ip'] != '0') {
			$sql = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $ip);
			$voteStatusByIp = $wpdb->get_var($sql);
		}

		if (!isset($_COOKIE['dot_irecommendthis_' . $post_id]) && $voteStatusByIp == 0) {
			$class = 'dot-irecommendthis';
			$title = empty($options['link_title_new']) ? __('Recommend this', 'i-recommend-this') : $options['link_title_new'];
		} else {
			$class = 'dot-irecommendthis active';
			$title = empty($options['link_title_active']) ? __('You already recommended this', 'i-recommend-this') : $options['link_title_active'];
		}

		$dot_irt_html = '<a href="#" class="' . esc_attr($class) . '" id="dot-irecommendthis-' . $post_id . '" title="' . esc_attr($title) . '">';
		$dot_irt_html .= apply_filters('dot_irt_before_count', $output);
		$dot_irt_html .= '</a>';

		return $dot_irt_html;
	}


	/*--------------------------------------------*
	 * Shortcode //dot_recommended_top_posts
	 *--------------------------------------------*/
	function dot_recommended_top_posts($atts, $content = null)
	{
		// Define attributes and their defaults
		$atts = shortcode_atts(
			array(
				'container' => 'li',
				'number' => 10,
				'post_type' => 'post',
				'year' => '',
				'monthnum' => '',
				'show_count' => 1,
			),
			$atts
		);

		// Sanitize and validate attributes
		$atts['container'] = sanitize_text_field($atts['container']);
		$atts['number'] = intval($atts['number']);
		$atts['post_type'] = sanitize_text_field($atts['post_type']);
		$atts['year'] = intval($atts['year']);
		$atts['monthnum'] = intval($atts['monthnum']);
		$atts['show_count'] = intval($atts['show_count']);

		global $wpdb;

		// Initialize an empty array to hold parameters for the prepared statement
		$params = array();

		// Build the query string
		$sql = "SELECT * FROM $wpdb->posts, $wpdb->postmeta WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id";

		// Add year
		if ($atts['year'] !== '') {
			$sql .= ' AND YEAR(post_date) = %d';
			$params[] = $atts['year'];
		}

		// Add monthnum
		if ($atts['monthnum'] !== '') {
			$sql .= ' AND MONTH(post_date) = %d';
			$params[] = $atts['monthnum'];
		}

		// Add post WHERE
		$sql .= " AND post_status = 'publish' AND post_type = %s AND meta_key = '_recommended'";
		$params[] = $atts['post_type'];

		// Add order by and limit
		$sql .= " ORDER BY {$wpdb->postmeta}.meta_value+0 DESC LIMIT %d";
		$params[] = $atts['number'];

		// Prepare the SQL statement
		$query = $wpdb->prepare($sql, $params);

		// Execute the query
		$posts = $wpdb->get_results($query);

		$return = '';

		foreach ($posts as $item) {
			$post_title = get_the_title($item->ID);
			$permalink = get_permalink($item->ID);
			$post_count = intval($item->meta_value);

			$return .= '<' . esc_html($atts['container']) . '>';
			$return .= '<a href="' . esc_url($permalink) . '" title="' . esc_html($post_title) . '" rel="nofollow">' . esc_html($post_title) . '</a> ';

			if ($atts['show_count'] == 1) {
				$return .= '<span class="votes">' . esc_html($post_count) . '</span> ';
			}

			$return .= '</' . esc_html($atts['container']) . '>';
		}

		return $return;
	}
}
