<?php
/**
 * Shortcodes for the I Recommend This plugin.
 *
 * @package IRecommendThis
 */
class Themeist_IRecommendThis_Shortcodes {

	/**
	 * Register shortcodes.
	 */
	public static function register_shortcodes() {
		// Old shortcode name.
		add_shortcode( 'dot_recommends', array( __CLASS__, 'shortcode_dot_recommends' ) );

		// New shortcode name.
		add_shortcode( 'irecommendthis', array( __CLASS__, 'shortcode_dot_recommends' ) );

		// Old shortcode name.
		add_shortcode( 'dot_recommended_top_posts', array( __CLASS__, 'shortcode_dot_recommended_top_posts' ) );

		// New shortcode name.
		add_shortcode( 'irecommendthis_top_posts', array( __CLASS__, 'shortcode_dot_recommended_top_posts' ) );
	}

	/**
	 * Shortcode handler for displaying the recommendation button.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the recommendation button.
	 */
	public static function shortcode_dot_recommends( $atts ) {

		$atts = shortcode_atts( array( 'id' => null ), $atts );
		return self::dot_recommend( intval( $atts['id'] ) );
	}

	/**
	 * Display the recommendation button.
	 *
	 * @param int    $id Post ID.
	 * @param string $action Action to perform: 'get' or 'update'.
	 * @return string HTML output for the recommendation button.
	 */
	public static function dot_recommend( $id = null, $action = 'get' ) {
		global $post;

		$post_id = $id ? $id : get_the_ID();
		$options = get_option( 'dot_irecommendthis_settings' );

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

		$output = Themeist_IRecommendThis_Public::dot_recommend_this( $post_id, $options['text_zero_suffix'], $options['text_one_suffix'], $options['text_more_suffix'], $action );

		$vote_status_by_ip = 0;
		if ( '0' !== $options['enable_unique_ip'] ) {
			global $wpdb;
			$sql               = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $ip );
			$vote_status_by_ip = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
		}

		if ( isset( $_COOKIE[ 'dot_irecommendthis_' . $post_id ] ) || $vote_status_by_ip > 0 ) {
			$class = 'irecommendthis active';
			$title = empty( $options['link_title_active'] ) ? __( 'You already recommended this', 'i-recommend-this' ) : $options['link_title_active'];
		} else {
			$class = 'irecommendthis';
			$title = empty( $options['link_title_new'] ) ? __( 'Recommend this', 'i-recommend-this' ) : $options['link_title_new'];
		}

		$dot_irt_html  = '<a href="#" class="' . esc_attr( $class ) . '" id="irecommendthis-' . $post_id . '" title="' . esc_attr( $title ) . '">';
		$dot_irt_html .= apply_filters( 'irecommendthis_before_count', $output );
		$dot_irt_html .= '</a>';

		return $dot_irt_html;
	}


	/**
	 * Shortcode handler for displaying the top recommended posts.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the top recommended posts.
	 */
	public static function shortcode_dot_recommended_top_posts( $atts ) {
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
			'dot_recommended_top_posts'
		);

		return self::dot_recommended_top_posts_output( $atts );
	}

	/**
	 * Display the top recommended posts.
	 *
	 * @param array $atts Processed shortcode attributes.
	 * @return string HTML output for the top recommended posts.
	 */
	public static function dot_recommended_top_posts_output( $atts ) {
		global $wpdb;

		// Sanitize and set defaults.
		$container  = sanitize_text_field( $atts['container'] );
		$number     = intval( $atts['number'] );
		$post_type  = sanitize_text_field( $atts['post_type'] );
		$year       = intval( $atts['year'] );
		$monthnum   = intval( $atts['monthnum'] );
		$show_count = intval( $atts['show_count'] );

		$params = array();
		$sql    = "SELECT * FROM {$wpdb->posts}, {$wpdb->postmeta} WHERE {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id";

		if ( ! empty( $year ) ) {
			$sql     .= ' AND YEAR(post_date) = %d';
			$params[] = $year;
		}

		if ( ! empty( $monthnum ) ) {
			$sql     .= ' AND MONTH(post_date) = %d';
			$params[] = $monthnum;
		}

		$sql     .= " AND post_status = 'publish' AND post_type = %s AND meta_key = '_recommended'";
		$params[] = $post_type;

		$sql     .= " ORDER BY {$wpdb->postmeta}.meta_value+0 DESC LIMIT %d";
		$params[] = $number;

		$query = $wpdb->prepare( $sql, $params ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

		$posts = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

		$return = '';

		foreach ( $posts as $item ) {
			$post_title = get_the_title( $item->ID );
			$permalink  = get_permalink( $item->ID );
			$post_count = intval( $item->meta_value );

			$return .= '<' . esc_html( $container ) . '>';
			$return .= '<a href="' . esc_url( $permalink ) . '" title="' . esc_html( $post_title ) . '" rel="nofollow">' . esc_html( $post_title ) . '</a> ';

			if ( 1 === $show_count ) {
				$return .= '<span class="votes">' . esc_html( $post_count ) . '</span> ';
			}

			$return .= '</' . esc_html( $container ) . '>';
		}

		return $return;
	}
}
