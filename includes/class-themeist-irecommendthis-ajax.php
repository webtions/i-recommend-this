<?php
/**
 * Handles AJAX functionality for the I Recommend This plugin.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle AJAX functionality.
 */
class Themeist_IRecommendThis_Ajax {

	/**
	 * Initialize the component.
	 */
	public function add_ajax_hooks() {
		// Register both action names for compatibility
		add_action( 'wp_ajax_irecommendthis', array( $this, 'ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_irecommendthis', array( $this, 'ajax_callback' ) );

		add_action( 'wp_ajax_dot-irecommendthis', array( $this, 'ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_dot-irecommendthis', array( $this, 'ajax_callback' ) );
	}

	/**
	 * AJAX Callback for recommendation.
	 */
	public function ajax_callback() {
		// Get the post ID
		if ( isset( $_POST['recommend_id'] ) ) {
			$post_id = intval( sanitize_text_field( wp_unslash( $_POST['recommend_id'] ) ) );
		} elseif ( isset( $_POST['post_id'] ) ) {
			$post_id = intval( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) );
		} else {
			die( esc_html__( 'Error: No valid post ID provided.', 'i-recommend-this' ) );
		}

		// Get security token
		$token = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

		// Verify the request using our security handler
		if ( ! Themeist_IRecommendThis_Security::verify_request( $token, $post_id ) ) {
			// In development mode, show detailed error
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				die( esc_html__( 'Security verification failed. Please try again.', 'i-recommend-this' ) );
			}

			// In production, just return the current count without updating
			$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );
			$text_zero_suffix = isset( $options['text_zero_suffix'] ) ? sanitize_text_field( $options['text_zero_suffix'] ) : '';
			$text_one_suffix  = isset( $options['text_one_suffix'] ) ? sanitize_text_field( $options['text_one_suffix'] ) : '';
			$text_more_suffix = isset( $options['text_more_suffix'] ) ? sanitize_text_field( $options['text_more_suffix'] ) : '';

			echo wp_kses_post( Themeist_IRecommendThis_Public_Processor::process_recommendation(
				$post_id,
				$text_zero_suffix,
				$text_one_suffix,
				$text_more_suffix,
				'get'
			) );

			exit;
		}

		// Security checks passed, process the recommendation
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );
		$text_zero_suffix = isset( $options['text_zero_suffix'] ) ? sanitize_text_field( $options['text_zero_suffix'] ) : '';
		$text_one_suffix  = isset( $options['text_one_suffix'] ) ? sanitize_text_field( $options['text_one_suffix'] ) : '';
		$text_more_suffix = isset( $options['text_more_suffix'] ) ? sanitize_text_field( $options['text_more_suffix'] ) : '';

		echo wp_kses_post( Themeist_IRecommendThis_Public_Processor::process_recommendation(
			$post_id,
			$text_zero_suffix,
			$text_one_suffix,
			$text_more_suffix,
			'update'
		) );

		exit;
	}
}
