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
		error_log( 'Registering shortcodes' );
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
		// Minimal implementation
		return '<p>Recommendation Button for Post ID: ' . esc_html( $id ) . '</p>';
	}

	/**
	 * Shortcode handler for displaying the top recommended posts.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Content enclosed by the shortcode (if any).
	 * @return string HTML output for the top recommended posts.
	 */
	public static function dot_recommended_top_posts( $atts, $content = null ) {

		error_log( 'Shortcode [dot_recommended_top_posts] is being processed' );

		// Additional logging for debug
		$debug_output = 'Shortcode Attributes: ' . print_r($atts, true);
		error_log($debug_output);

		// Default attributes
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

		error_log('Processed Attributes: ' . print_r($atts, true));

		// Return minimal output to confirm processing
		return '<div class="recommended-posts">This is a test output for [dot_recommended_top_posts]</div>';
	}
}

// Register the shortcodes.
add_action( 'init', array( 'Themeist_IRecommendThis_Shortcodes', 'register_shortcodes' ) );
