<?php
/**
 * Class to handle the top recommended posts functionality for the I Recommend This plugin.
 *
 * @package IRecommendThis
 */

class Themeist_IRecommendThis_Top_Posts {

	/**
	 * Constructor to initialize the class.
	 *
	 * @param string $plugin_file The main plugin file path.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Add hooks for the top recommended posts functionality.
	 */
	public function add_top_posts_hooks() {
		add_shortcode( 'dot_recommended_posts', array( $this, 'dot_recommended_top_posts' ) );
	}

	/**
	 * Shortcode handler for displaying the top recommended posts.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Content enclosed by the shortcode (if any).
	 * @return string HTML output for the top recommended posts.
	 */
	public function dot_recommended_top_posts( $atts, $content = null ) {
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
