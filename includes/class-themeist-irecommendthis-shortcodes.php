<?php
/**
 * Shortcodes for the I Recommend This plugin.
 *
 * @package IRecommendThis
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
		// @deprecated 4.0.0 Use 'irecommendthis' instead
		add_shortcode( 'dot_recommends', array( __CLASS__, 'shortcode_recommends' ) );

		// New shortcode name.
		add_shortcode( 'irecommendthis', array( __CLASS__, 'shortcode_recommends' ) );

		// Old shortcode name for backward compatibility.
		// @deprecated 4.0.0 Use 'irecommendthis_top_posts' instead
		add_shortcode( 'dot_recommended_top_posts', array( __CLASS__, 'shortcode_recommended_top_posts' ) );

		// New shortcode name.
		add_shortcode( 'irecommendthis_top_posts', array( __CLASS__, 'shortcode_recommended_top_posts' ) );
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
			$atts
		);

		// Convert string 'false' to boolean false
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
			$anonymized_ip    = Themeist_IRecommendThis_Public_Processor::anonymize_ip( $ip );
			$sql              = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $anonymized_ip );
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

		// Add wrapper div if requested.
		if ( $wrapper ) {
			$irt_html = '<div class="irecommendthis-wrapper">' . $irt_html . '</div>';
		}

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
			'recommended_top_posts'
		);

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

		$query = $wpdb->prepare( $sql, $params ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$posts = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

		$return = '';

		foreach ( $posts as $item ) {
			$post_title = get_the_title( $item->ID );
			$permalink  = get_permalink( $item->ID );
			$post_count = intval( $item->meta_value );

			$return .= '<' . esc_html( $container ) . '>';
			$return .= '<a href="' . esc_url( $permalink ) . '" title="' . esc_attr( $post_title ) . '" rel="nofollow">' . esc_html( $post_title ) . '</a> ';

			if ( 1 === $show_count ) {
				$return .= '<span class="votes">' . esc_html( $post_count ) . '</span> ';
			}

			$return .= '</' . esc_html( $container ) . '>';
		}

		return $return;
	}
}
