<?php
/**
 * Shortcodes for the I Recommend This plugin.
 *
 * @package IRecommendThis
 * @subpackage Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle shortcodes for the plugin.
 */
class Themeist_IRecommendThis_Shortcodes {

	/**
	 * Register shortcodes.
	 */
	public static function register_shortcodes() {
		// Old shortcode name for backward compatibility.
		// @deprecated 4.0.0 Use 'irecommendthis' instead.
		add_shortcode( 'dot_recommends', array( __CLASS__, 'shortcode_recommends' ) );

		// New shortcode name.
		add_shortcode( 'irecommendthis', array( __CLASS__, 'shortcode_recommends' ) );

		// Old shortcode name for backward compatibility.
		// @deprecated 4.0.0 Use 'irecommendthis_top_posts' instead.
		add_shortcode( 'dot_recommended_top_posts', array( __CLASS__, 'shortcode_recommended_top_posts' ) );

		// New shortcode name.
		add_shortcode( 'irecommendthis_top_posts', array( __CLASS__, 'shortcode_recommended_top_posts' ) );

		/**
		 * Action fired after shortcodes are registered.
		 *
		 * @since 4.0.0
		 */
		do_action( 'irecommendthis_shortcodes_registered' );
	}

	/**
	 * Shortcode handler for displaying the recommendation button.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the recommendation button.
	 */
	public static function shortcode_recommends( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'               => null,
				'use_current_post' => false,
				'wrapper'          => true,
			),
			$atts,
			'irecommendthis'
		);

		/**
		 * Filter the shortcode attributes before processing.
		 *
		 * @since 4.0.0
		 * @param array $atts Shortcode attributes.
		 */
		$atts = apply_filters( 'irecommendthis_shortcode_atts', $atts );

		// Convert string 'false' to boolean false.
		if ( is_string( $atts['wrapper'] ) && 'false' === strtolower( $atts['wrapper'] ) ) {
			$atts['wrapper'] = false;
		}

		// If use_current_post is true or we're in a loop and no ID is specified, use current post ID.
		if (
			( 'true' === $atts['use_current_post'] || true === $atts['use_current_post'] ) ||
			( empty( $atts['id'] ) && in_the_loop() )
		) {
			return self::recommend( get_the_ID(), 'get', $atts['wrapper'] );
		}

		return self::recommend( intval( $atts['id'] ), 'get', $atts['wrapper'] );
	}

	/**
	 * Display the recommendation button.
	 *
	 * @param int    $id      Post ID.
	 * @param string $action  Action to perform: 'get' or 'update'.
	 * @param bool   $wrapper Whether to wrap the output in a container div.
	 * @return string HTML output for the recommendation button.
	 */
	public static function recommend( $id = null, $action = 'get', $wrapper = true ) {
		global $post;

		/**
		 * Action fired before recommendation link is generated.
		 *
		 * @since 4.0.0
		 * @param int    $id      Post ID.
		 * @param string $action  Action: 'get' or 'update'.
		 * @param bool   $wrapper Whether to wrap in container.
		 */
		do_action( 'irecommendthis_before_recommend', $id, $action, $wrapper );

		$post_id = $id ? $id : get_the_ID();
		$options = get_option( 'irecommendthis_settings' );

		$ip              = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$default_options = array(
			'text_zero_suffix'  => '',
			'text_one_suffix'   => '',
			'text_more_suffix'  => '',
			'link_title_new'    => '',
			'link_title_active' => '',
			'enable_unique_ip'  => '0',
		);
		$options         = wp_parse_args( $options, $default_options );

		$output = Themeist_IRecommendThis_Public_Processor::process_recommendation(
			$post_id,
			$options['text_zero_suffix'],
			$options['text_one_suffix'],
			$options['text_more_suffix'],
			$action
		);

		$vote_status_by_ip = 0;
		if ( '0' !== $options['enable_unique_ip'] ) {
			global $wpdb;
			$anonymized_ip     = Themeist_IRecommendThis_Public_Processor::anonymize_ip( $ip );
			$sql               = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $anonymized_ip );
			$vote_status_by_ip = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
		}

		// Check cookie status.
		$cookie_exists = isset( $_COOKIE[ 'irecommendthis_' . $post_id ] );

		// Use the existing title settings for the like/unlike text.
		// Updated default text for better action clarity.
		$like_text = empty( $options['link_title_new'] )
			? __( 'Recommend this', 'i-recommend-this' )
			: $options['link_title_new'];

		$unlike_text = empty( $options['link_title_active'] )
			? __( 'Unrecommend this', 'i-recommend-this' )
			: $options['link_title_active'];

		if ( $cookie_exists || $vote_status_by_ip > 0 ) {
			$class         = 'irecommendthis active irecommendthis-post-' . $post_id;
			$title         = $unlike_text;
			$current_state = $unlike_text;
		} else {
			$class         = 'irecommendthis irecommendthis-post-' . $post_id;
			$title         = $like_text;
			$current_state = $like_text;
		}

		// Enhanced HTML with better attribute support for accessibility and JavaScript interaction.
		$irt_html  = '<a href="#" class="' . esc_attr( $class ) . '" ';
		$irt_html .= 'data-post-id="' . esc_attr( $post_id ) . '" ';
		$irt_html .= 'data-like="' . esc_attr( $like_text ) . '" ';
		$irt_html .= 'data-unlike="' . esc_attr( $unlike_text ) . '" ';
		$irt_html .= 'aria-label="' . esc_attr( $title ) . '" ';
		$irt_html .= 'title="' . esc_attr( $title ) . '">';
		$irt_html .= $output;
		$irt_html .= '</a>';

		/**
		 * Filter the recommendation HTML before wrapping.
		 *
		 * @since 4.0.0
		 * @param string $irt_html The HTML for the recommendation link.
		 * @param int    $post_id  The post ID.
		 * @param bool   $wrapper  Whether to wrap in container.
		 */
		$irt_html = apply_filters( 'irecommendthis_button_html', $irt_html, $post_id, $wrapper );

		// Add wrapper div if requested.
		if ( $wrapper ) {
			/**
			 * Filter the wrapper class name.
			 *
			 * @since 4.0.0
			 * @param string $wrapper_class The wrapper CSS class.
			 * @param int    $post_id       The post ID.
			 */
			$wrapper_class = apply_filters( 'irecommendthis_wrapper_class', 'irecommendthis-wrapper', $post_id );
			$irt_html      = '<div class="' . esc_attr( $wrapper_class ) . '">' . $irt_html . '</div>';
		}

		/**
		 * Action fired after recommendation link is generated.
		 *
		 * @since 4.0.0
		 * @param string $irt_html The final HTML.
		 * @param int    $post_id  The post ID.
		 * @param string $action   Action: 'get' or 'update'.
		 */
		do_action( 'irecommendthis_after_recommend', $irt_html, $post_id, $action );

		return $irt_html;
	}

	/**
	 * Shortcode handler for displaying the top recommended posts.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the top recommended posts.
	 */
	public static function shortcode_recommended_top_posts( $atts ) {
		$atts = shortcode_atts(
			array(
				'container'  => 'li',
				'number'     => 10,
				'post_type'  => 'post',
				'year'       => '',
				'monthnum'   => '',
				'show_count' => 1,
			),
			$atts,
			'irecommendthis_top_posts'
		);

		/**
		 * Filter the top posts shortcode attributes.
		 *
		 * @since 4.0.0
		 * @param array $atts Shortcode attributes.
		 */
		$atts = apply_filters( 'irecommendthis_top_posts_atts', $atts );

		return self::recommended_top_posts_output( $atts );
	}

	/**
	 * Display the top recommended posts.
	 *
	 * @param array $atts Processed shortcode attributes.
	 * @return string HTML output for the top recommended posts.
	 */
	public static function recommended_top_posts_output( $atts ) {
		global $wpdb;

		// Sanitize and set defaults.
		$container  = sanitize_text_field( $atts['container'] );
		$number     = intval( $atts['number'] );
		$post_type  = sanitize_text_field( $atts['post_type'] );
		$year       = intval( $atts['year'] );
		$monthnum   = intval( $atts['monthnum'] );
		$show_count = intval( $atts['show_count'] );

		/**
		 * Action fired before querying for top posts.
		 *
		 * @since 4.0.0
		 * @param array $atts The processed shortcode attributes.
		 */
		do_action( 'irecommendthis_before_top_posts_query', $atts );

		// Improved query with better joins and explicit column selection.
		$params = array();
		$sql    = "SELECT p.ID, p.post_title, pm.meta_value
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_status = 'publish'
				AND pm.meta_key = '_recommended'";

		if ( ! empty( $year ) ) {
			$sql     .= ' AND YEAR(p.post_date) = %d';
			$params[] = $year;
		}

		if ( ! empty( $monthnum ) ) {
			$sql     .= ' AND MONTH(p.post_date) = %d';
			$params[] = $monthnum;
		}

		$sql     .= ' AND p.post_type = %s';
		$params[] = $post_type;

		$sql     .= ' ORDER BY CAST(pm.meta_value AS UNSIGNED) DESC LIMIT %d';
		$params[] = $number;

		/**
		 * Filter the SQL query for top posts.
		 *
		 * @since 4.0.0
		 * @param string $sql    The SQL query.
		 * @param array  $params The query parameters.
		 * @param array  $atts   The processed shortcode attributes.
		 */
		$sql = apply_filters( 'irecommendthis_top_posts_sql', $sql, $params, $atts );

		$query = $wpdb->prepare( $sql, $params ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$posts = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

		$return = '';

		/**
		 * Action fired before rendering top posts list.
		 *
		 * @since 4.0.0
		 * @param array $posts The query results.
		 * @param array $atts  The processed shortcode attributes.
		 */
		do_action( 'irecommendthis_before_top_posts_output', $posts, $atts );

		foreach ( $posts as $item ) {
			$post_title = get_the_title( $item->ID );
			$permalink  = get_permalink( $item->ID );
			$post_count = intval( $item->meta_value );

			/**
			 * Filter the opening HTML tag for each top post item.
			 *
			 * @since 4.0.0
			 * @param string $open_tag The opening HTML tag.
			 * @param string $container The container element.
			 * @param object $item The current post item.
			 */
			$open_tag = apply_filters( 'irecommendthis_top_post_open_tag', '<' . esc_html( $container ) . '>', $container, $item );
			$return  .= $open_tag;

			/**
			 * Filter the post link HTML.
			 *
			 * @since 4.0.0
			 * @param string $link_html The link HTML.
			 * @param object $item The current post item.
			 * @param string $permalink The post permalink.
			 * @param string $post_title The post title.
			 */
			$link_html = apply_filters(
				'irecommendthis_top_post_link',
				'<a href="' . esc_url( $permalink ) . '" title="' . esc_attr( $post_title ) . '" rel="nofollow">' . esc_html( $post_title ) . '</a> ',
				$item,
				$permalink,
				$post_title
			);
			$return   .= $link_html;

			if ( 1 === $show_count ) {
				/**
				 * Filter the count display HTML.
				 *
				 * @since 4.0.0
				 * @param string $count_html The count HTML.
				 * @param int    $post_count The post count.
				 * @param object $item The current post item.
				 */
				$count_html = apply_filters(
					'irecommendthis_top_post_count',
					'<span class="votes">' . esc_html( $post_count ) . '</span> ',
					$post_count,
					$item
				);
				$return    .= $count_html;
			}

			/**
			 * Filter the closing HTML tag for each top post item.
			 *
			 * @since 4.0.0
			 * @param string $close_tag The closing HTML tag.
			 * @param string $container The container element.
			 * @param object $item The current post item.
			 */
			$close_tag = apply_filters( 'irecommendthis_top_post_close_tag', '</' . esc_html( $container ) . '>', $container, $item );
			$return   .= $close_tag;
		}//end foreach

		/**
		 * Filter the final top posts HTML.
		 *
		 * @since 4.0.0
		 * @param string $return The HTML output.
		 * @param array  $posts  The query results.
		 * @param array  $atts   The processed shortcode attributes.
		 */
		$return = apply_filters( 'irecommendthis_top_posts_html', $return, $posts, $atts );

		/**
		 * Action fired after rendering top posts list.
		 *
		 * @since 4.0.0
		 * @param string $return The HTML output.
		 * @param array  $posts  The query results.
		 * @param array  $atts   The processed shortcode attributes.
		 */
		do_action( 'irecommendthis_after_top_posts_output', $return, $posts, $atts );

		return $return;
	}
}
