<?php
/**
 * Shortcodes for the I Recommend This plugin.
 *
 * @package Themeist_IRecommendThis
 */

class Themeist_IRecommendThis_Shortcodes {

	/**
	 * Register shortcodes.
	 */
	public static function register_shortcodes() {
		add_shortcode( 'dot_recommends', array( __CLASS__, 'shortcode_dot_recommends' ) );
		add_shortcode( 'dot_recommended_posts', array( __CLASS__, 'dot_recommended_top_posts' ) );
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
	 * @param int $id Post ID.
	 * @return string HTML output for the recommendation button.
	 */
	public static function dot_recommend( $id = null ) {
		global $post;

		$post_id = $id ? $id : get_the_ID();
		$options = get_option( 'dot_irecommendthis_settings' );

		$ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
		$default_options = array(
			'text_zero_suffix'  => '',
			'text_one_suffix'   => '',
			'text_more_suffix'  => '',
			'link_title_new'    => '',
			'link_title_active' => '',
			'enable_unique_ip'  => '0',
		);
		$options = wp_parse_args( $options, $default_options );

		$output = Themeist_IRecommendThis_Public::dot_recommend_this( $post_id, $options['text_zero_suffix'], $options['text_one_suffix'], $options['text_more_suffix'], 'get' );

		$voteStatusByIp = 0;
		if ( $options['enable_unique_ip'] != '0' ) {
			global $wpdb;
			$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $ip );
			$voteStatusByIp = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
		}

		if ( ! isset( $_COOKIE[ 'dot_irecommendthis_' . $post_id ] ) && $voteStatusByIp == 0 ) {
			$class = 'dot-irecommendthis';
			$title = empty( $options['link_title_new'] ) ? __( 'Recommend this', 'i-recommend-this' ) : $options['link_title_new'];
		} else {
			$class = 'dot-irecommendthis active';
			$title = empty( $options['link_title_active'] ) ? __( 'You already recommended this', 'i-recommend-this' ) : $options['link_title_active'];
		}

		$dot_irt_html  = '<a href="#" class="' . esc_attr( $class ) . '" id="dot-irecommendthis-' . $post_id . '" title="' . esc_attr( $title ) . '">';
		$dot_irt_html .= apply_filters( 'dot_irt_before_count', $output );
		$dot_irt_html .= '</a>';

		return $dot_irt_html;
	}

	/**
	 * Shortcode handler for displaying the top recommended posts.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Content enclosed by the shortcode (if any).
	 * @return string HTML output for the top recommended posts.
	 */
	public static function dot_recommended_top_posts( $atts, $content = null ) {
		$atts = shortcode_atts(
			array(
				'container'  => 'li',
				'number'     => 10,
				'post_type'  => 'post',
				'year'       => '',
				'monthnum'   => '',
				'show_count' => 1,
			),
			$atts
		);

		$atts['container']  = sanitize_text_field( $atts['container'] );
		$atts['number']     = intval( $atts['number'] );
		$atts['post_type']  = sanitize_text_field( $atts['post_type'] );
		$atts['year']       = intval( $atts['year'] );
		$atts['monthnum']   = intval( $atts['monthnum'] );
		$atts['show_count'] = intval( $atts['show_count'] );

		global $wpdb;
		$params = array();
		$sql = "SELECT * FROM $wpdb->posts, $wpdb->postmeta WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id";

		if ( $atts['year'] !== '' ) {
			$sql .= ' AND YEAR(post_date) = %d';
			$params[] = $atts['year'];
		}

		if ( $atts['monthnum'] !== '' ) {
			$sql .= ' AND MONTH(post_date) = %d';
			$params[] = $atts['monthnum'];
		}

		$sql .= " AND post_status = 'publish' AND post_type = %s AND meta_key = '_recommended'";
		$params[] = $atts['post_type'];

		$sql .= " ORDER BY {$wpdb->postmeta}.meta_value+0 DESC LIMIT %d";
		$params[] = $atts['number'];

		$query = $wpdb->prepare( $sql, $params );

		$posts = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

		$return = '';

		foreach ( $posts as $item ) {
			$post_title = get_the_title( $item->ID );
			$permalink  = get_permalink( $item->ID );
			$post_count = intval( $item->meta_value );

			$return .= '<' . esc_html( $atts['container'] ) . '>';
			$return .= '<a href="' . esc_url( $permalink ) . '" title="' . esc_html( $post_title ) . '" rel="nofollow">' . esc_html( $post_title ) . '</a> ';

			if ( $atts['show_count'] == 1 ) {
				$return .= '<span class="votes">' . esc_html( $post_count ) . '</span> ';
			}

			$return .= '</' . esc_html( $atts['container'] ) . '>';
		}

		return $return;
	}
}
