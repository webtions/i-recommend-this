<?php

class Themeist_IRecommendThis_Public {

	/**
	 * @param string $plugin_file
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	public function add_public_hooks() {
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('init', array($this, 'add_widget_most_recommended_posts'));
		add_action('wp_ajax_dot-irecommendthis', array($this, 'ajax_callback'));
		add_action('wp_ajax_nopriv_dot-irecommendthis', array($this, 'ajax_callback'));
		add_filter('the_content', array($this, 'dot_content'));
		add_shortcode('dot_recommends', array($this, 'shortcode'));
		add_shortcode('dot_recommended_posts', array($this, 'dot_recommended_top_posts'));
	}

	/*--------------------------------------------*
	 * Enqueue Scripts
	 *--------------------------------------------*/

	function enqueue_scripts()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['disable_css'])) $options['disable_css'] = '0';
		if (!isset($options['recommend_style'])) $options['recommend_style'] = '0';

		if ($options['disable_css'] == '0') {

			if ($options['recommend_style'] == '0') {
				wp_enqueue_style( 'dot-irecommendthis', plugins_url('/css/dot-irecommendthis.css', $this->plugin_file ) );
			} else {
				wp_enqueue_style( 'dot-irecommendthis', plugins_url('/css/dot-irecommendthis-heart.css', $this->plugin_file ) );
			}
		}
		wp_register_script('dot-irecommendthis', plugins_url('/js/dot_irecommendthis.js', $this->plugin_file ), 'jquery', '2.6.0', 'in_footer');

		wp_enqueue_script('jquery');
		wp_enqueue_script('dot-irecommendthis');

		wp_localize_script('dot-irecommendthis', 'dot_irecommendthis', array('ajaxurl' => admin_url('admin-ajax.php')));

	}    //dot_enqueue_scripts



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
	 * AJAX Callback
	 *--------------------------------------------*/

	function ajax_callback($post_id)
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['add_to_posts'])) $options['add_to_posts'] = '1';
		if (!isset($options['add_to_other'])) $options['add_to_other'] = '1';
		if (!isset($options['text_zero_suffix'])) $options['text_zero_suffix'] = '';
		if (!isset($options['text_one_suffix'])) $options['text_one_suffix'] = '';
		if (!isset($options['text_more_suffix'])) $options['text_more_suffix'] = '';

		if (isset($_POST['recommend_id'])) {
			// Click event. Get and Update Count
			$post_id = str_replace('dot-irecommendthis-', '', $_POST['recommend_id']);
			echo $this->dot_recommend_this($post_id, $options['text_zero_suffix'], $options['text_one_suffix'], $options['text_more_suffix'], 'update');
		} else {
			// AJAXing data in. Get Count
			$post_id = str_replace('dot-irecommendthis-', '', $_POST['post_id']);
			echo $this->dot_recommend_this($post_id, $options['text_zero_suffix'], $options['text_one_suffix'], $options['text_more_suffix'], 'get');
		}

		exit;

	}    //ajax_callback


	/*--------------------------------------------*
	 * Main Process
	 *--------------------------------------------*/


	function dot_recommend_this($post_id, $text_zero_suffix = false, $text_one_suffix = false, $text_more_suffix = false, $action = 'get')
	{
		global $wpdb;
		if (!is_numeric($post_id)) return;
		$text_zero_suffix = strip_tags($text_zero_suffix);
		$text_one_suffix = strip_tags($text_one_suffix);
		$text_more_suffix = strip_tags($text_more_suffix);


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


				/*

				Hides the count is the count is zero.

				*/
				$options = get_option('dot_irecommendthis_settings');
				if (!isset($options['hide_zero'])) $options['hide_zero'] = '0';


				if (($recommended == 0) && $options['hide_zero'] == 1) {

					$output = '<span class="dot-irecommendthis-count">&nbsp;</span> <span class="dot-irecommendthis-suffix">' . $suffix . '</span>';

					return $output;

				} else {

					$output = '<span class="dot-irecommendthis-count">' . $recommended . '</span> <span class="dot-irecommendthis-suffix">' . $suffix . '</span>';

					return $output;

				}

				break;


			case 'update':

				$recommended = get_post_meta($post_id, '_recommended', true);

				$options = get_option('dot_irecommendthis_settings');
				if (!isset($options['disable_unique_ip'])) $options['disable_unique_ip'] = '0';

				/*

				Check if Unique IP saving is required or disabled

				*/
				if ($options['disable_unique_ip'] != 0) {

					if (isset($_COOKIE['dot_irecommendthis_' . $post_id])) {
						return $recommended;
					}

					$recommended++;
					update_post_meta($post_id, '_recommended', $recommended);
					setcookie('dot_irecommendthis_' . $post_id, time(), time() + 3600 * 24 * 365, '/');


				} else {
					$ip = $_SERVER['REMOTE_ADDR'];
					$sql = $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $ip);
					$voteStatusByIp = $wpdb->get_var($sql);

					if (isset($_COOKIE['dot_irecommendthis_' . $post_id]) || $voteStatusByIp != 0) {
						return $recommended;
					}

					$recommended++;
					update_post_meta($post_id, '_recommended', $recommended);
					setcookie('dot_irecommendthis_' . $post_id, time(), time() + 3600 * 24 * 365, '/');
					$sql = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "irecommendthis_votes VALUES ('', NOW(), %d, %s )", $post_id, $ip);
					$wpdb->query($sql);

				}


				if ($recommended == 0) {
					$suffix = $text_zero_suffix;
				} elseif ($recommended == 1) {
					$suffix = $text_one_suffix;
				} else {
					$suffix = $text_more_suffix;
				}

				$output = '<span class="dot-irecommendthis-count">' . $recommended . '</span> <span class="dot-irecommendthis-suffix">' . $suffix . '</span>';

				$dot_irt_html = apply_filters('dot_irt_before_count', $output);

				return $dot_irt_html;

				break;

		}
	}    //dot_recommend_this


	/*--------------------------------------------*
	 * Shortcode
	 *--------------------------------------------*/

	function shortcode($atts)
	{
		extract(shortcode_atts(array('id' => null), $atts));
		return $this->dot_recommend($id);

	}    //shortcode


	function dot_recommend($id = null)
	{


		global $wpdb, $post;
		$ip = $_SERVER['REMOTE_ADDR'];
		$post_id = $id ? $id : get_the_ID();

		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['text_zero_suffix'])) $options['text_zero_suffix'] = '';
		if (!isset($options['text_one_suffix'])) $options['text_one_suffix'] = '';
		if (!isset($options['text_more_suffix'])) $options['text_more_suffix'] = '';
		if (!isset($options['link_title_new'])) $options['link_title_new'] = '';
		if (!isset($options['link_title_active'])) $options['link_title_active'] = '';
		if (!isset($options['disable_unique_ip'])) $options['disable_unique_ip'] = '0'; //Check if Unique IP saving is required or disabled


		$output = $this->dot_recommend_this($post_id, $options['text_zero_suffix'], $options['text_one_suffix'], $options['text_more_suffix']);


		//if ( isset($_COOKIE['dot_irecommendthis_'. $post_id]) && $voteStatusByIp != 0 ) {

		if ($options['disable_unique_ip'] != '0') {

			if (!isset($_COOKIE['dot_irecommendthis_' . $post_id])) {
				$class = 'dot-irecommendthis';


				if ($options['link_title_new'] == '') {

					$title = __('Recommend this', 'i-recommend-this');

				} else {

					$title = $options['link_title_new'];

				}

			} else {

				$class = 'dot-irecommendthis active';

				if ($options['link_title_active'] == '') {

					$title = __('You already recommended this', 'i-recommend-this');

				} else {

					$title = $options['link_title_active'];

				}
			}

		} else {

			$sql = $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $ip);
			$voteStatusByIp = $wpdb->get_var($sql);


			if (!isset($_COOKIE['dot_irecommendthis_' . $post_id]) && $voteStatusByIp == 0) {
				$class = 'dot-irecommendthis';


				if ($options['link_title_new'] == '') {

					$title = __('Recommend this', 'i-recommend-this');

				} else {

					$title = $options['link_title_new'];

				}

			} else {

				$class = 'dot-irecommendthis active';

				if ($options['link_title_active'] == '') {

					$title = __('You already recommended this', 'i-recommend-this');

				} else {

					$title = $options['link_title_active'];

				}
			}

		}

		$dot_irt_html = '<a href="#" class="' . $class . '" id="dot-irecommendthis-' . $post_id . '" title="' . $title . '">';

		$dot_irt_html .= apply_filters('dot_irt_before_count', $output);
		$dot_irt_html .= '</a>';

		return $dot_irt_html;

		//return '<a href="#" class="'. $class .'" id="dot-irecommendthis-'. $post_ID .'" title="'. $title .'"><i class="icon-heart"></i> '. $output .'</a>';
	}



	/*--------------------------------------------*
	 * Shortcode //dot_recommended_top_posts
	 *--------------------------------------------*/

	function dot_recommended_top_posts($atts, $content = null)
	{

		// define attributes and their defaults
		// get our variable from $atts
		$atts = shortcode_atts(array(
			'container' => 'li',
			'number' => '10',
			'post_type' => 'post',
			'year' => '',
			'monthnum' => '',
			'show_count' => '1',
		), $atts);

		global $wpdb;

		// empty params array to hold params for prepared statement
		$params = array();

		// build query string
		$sql = "SELECT * FROM $wpdb->posts, $wpdb->postmeta WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id";

		// add year
		if ('' !== $atts['year']) {
			$sql .= ' AND YEAR(post_date) = %d';
			$params[] = $atts['year'];
		}

		// add monthnum
		if ('' !== $atts['monthnum']) {
			$sql .= ' AND MONTH(post_date) = %d';
			$params[] = $atts['monthnum'];
		}

		// add post WHERE
		$sql .= " AND post_status = 'publish' AND post_type = %s AND meta_key = '_recommended'";
		$params[] = $atts['post_type'];

		// add order by and limit
		$sql .= " ORDER BY {$wpdb->postmeta}.meta_value+0 DESC LIMIT %d";
		$params[] = $atts['number'];

		// prepare sql statement
		$query = $wpdb->prepare($sql, $params);

		// execute query
		$posts = $wpdb->get_results($query);

		$return = '';

		foreach ($posts as $item) {
			$post_title = stripslashes($item->post_title);
			$permalink = get_permalink($item->ID);
			$post_count = $item->meta_value;

			$return .= '<' . esc_html($atts['container']) . '>';
			$return .= '<a href="' . esc_url($permalink) . '" title="' . esc_attr($post_title) . '" rel="nofollow">' . esc_html($post_title) . '</a> ';

			if ($atts['show_count'] == '1') {
				$return .= '<span class="votes">' . esc_html($post_count) . '</span> ';
			}

			$return .= '</' . esc_html($atts['container']) . '>';

		}
		return $return;

	}    //dot_recommended_top_posts




	/*--------------------------------------------*
	 * Widget
	 *--------------------------------------------*/

	function add_widget_most_recommended_posts()
	{

		function most_recommended_posts($numberOf, $before, $after, $show_count, $post_type = "post", $raw = false)
		{
			global $wpdb;

			$request = "SELECT * FROM $wpdb->posts, $wpdb->postmeta";
			$request .= " WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id";
			$request .= " AND post_status='publish' AND post_type='$post_type' AND meta_key='_recommended'";
			$request .= " ORDER BY $wpdb->postmeta.meta_value+0 DESC LIMIT $numberOf";
			$posts = $wpdb->get_results($request);

			if ($raw):
				return $posts;
			else:
				foreach ($posts as $item) {
					$post_title = stripslashes($item->post_title);
					$permalink = get_permalink($item->ID);
					$post_count = $item->meta_value;
					echo $before . '<a href="' . $permalink . '" title="' . $post_title . '" rel="nofollow">' . $post_title . '</a>';
					echo $show_count == '1' ? ' (' . $post_count . ')' : '';
					echo $after;
				}
			endif;
		}

		function widget_most_recommended_posts($args)
		{
			extract($args);
			$options = get_option("most_recommended_posts");
			if (!is_array($options)) {
				$options = array(
					'title' => __('Most recommended posts', 'i-recommend-this'),
					'number' => __('5', 'i-recommend-this'),
					'show_count' => '0'
				);
			}
			$title = $options['title'];
			$numberOf = absint( $options['number'] );
			$show_count = $options['show_count'];

			echo $before_widget;
			echo $before_title . $title . $after_title;
			echo '<ul class="mostrecommendedposts">';

			most_recommended_posts($numberOf, '<li>', '</li>', $show_count);

			echo '</ul>';
			echo $after_widget;
		}

		wp_register_sidebar_widget('most_recommended_posts', __('Most recommended posts', 'i-recommend-this'), 'widget_most_recommended_posts');

		function options_widget_most_recommended_posts()
		{
			$options = get_option("most_recommended_posts");

			if (!is_array($options)) {
				$options = array(
					'title' => __('Most recommended posts', 'i-recommend-this'),
					'number' => __('5', 'dot'),
					'show_count' => '0'
				);
			}


			if (isset($_POST['mrp-submit'])) {
				$options['title'] = htmlspecialchars($_POST['mrp-title']);
				$options['number'] = htmlspecialchars($_POST['mrp-number']);
				$options['show_count'] = $_POST['mrp-show-count'];
				if ($options['number'] > 15) {
					$options['number'] = 15;
				}

				update_option("most_recommended_posts", $options);
			}
			?>
			<p><label for="mrp-title"><?php _e('Title:', 'i-recommend-this'); ?><br/>
					<input class="widefat" type="text" id="mrp-title" name="mrp-title"
						   value="<?php echo $options['title']; ?>"/></label></p>

			<p><label for="mrp-number"><?php _e('Number of posts to show:', 'i-recommend-this'); ?><br/>
					<input type="text" id="mrp-number" name="mrp-number" style="width: 25px;"
						   value="<?php echo $options['number']; ?>"/>
					<small>(max. 15)</small>
				</label></p>

			<p><label for="mrp-show-count"><input type="checkbox" id="mrp-show-count" name="mrp-show-count"
												  value="1"<?php if ($options['show_count'] == '1') echo 'checked="checked"'; ?> /> <?php _e('Show post count', 'i-recommend-this'); ?>
				</label></p>

			<input type="hidden" id="mrp-submit" name="mrp-submit" value="1"/>
			<?php
		}

		wp_register_widget_control('most_recommended_posts', __('Most recommended posts', 'i-recommend-this'), 'options_widget_most_recommended_posts');
	}


}