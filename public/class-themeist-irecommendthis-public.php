<?php

/**
 * Main public class for I Recommend This plugin.
 *
 * @package IRecommendThis
 */
class Themeist_IRecommendThis_Public {

	/**
	 * Constructor to initialize the class.
	 *
	 * @param string $plugin_file The main plugin file path.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Add hooks for the public-facing functionality.
	 */
	public function add_public_hooks() {
		// Scripts and styles.
		add_action( 'wp_enqueue_scripts', array( 'Themeist_IRecommendThis_Scripts', 'enqueue_scripts' ) );

		// AJAX handling.
		add_action( 'wp_ajax_dot-irecommendthis', array( 'Themeist_IRecommendThis_Ajax', 'ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_dot-irecommendthis', array( 'Themeist_IRecommendThis_Ajax', 'ajax_callback' ) );

		// Content and shortcodes.
		add_filter( 'the_content', array( 'Themeist_IRecommendThis_Content', 'dot_content' ) );
		add_shortcode( 'dot_recommends', array( 'Themeist_IRecommendThis_Content', 'shortcode' ) );
		add_shortcode( 'dot_recommended_posts', array( 'Themeist_IRecommendThis_Content', 'dot_recommended_top_posts' ) );
	}
}

// Instantiate the main public class.
// $themeist_irecommendthis_public = new Themeist_IRecommendThis_Public( __FILE__ );
// $themeist_irecommendthis_public->add_public_hooks();
