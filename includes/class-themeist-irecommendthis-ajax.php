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
	 * Initialize the component by adding AJAX hooks.
	 */
	public function add_ajax_hooks() {
		// Register standard AJAX hooks.
		add_action( 'wp_ajax_irecommendthis', array( $this, 'ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_irecommendthis', array( $this, 'ajax_callback' ) );
	}

	/**
	 * AJAX Callback for recommendation.
	 */
	public function ajax_callback() {
		// Get the post ID.
		if ( isset( $_POST['recommend_id'] ) ) {
			$post_id = intval( sanitize_text_field( wp_unslash( $_POST['recommend_id'] ) ) );
		} elseif ( isset( $_POST['post_id'] ) ) {
			$post_id = intval( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) );
		} else {
			wp_send_json_error( array( 'message' => 'No valid post ID provided' ) );
			exit;
		}

		// Check for nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'irecommendthis-nonce' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// In debug mode, log more detailed error.
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'I Recommend This: Nonce verification failed' );
			}

			// Get plugin settings.
			$options = get_option( 'irecommendthis_settings' );
			$text_zero_suffix = isset( $options['text_zero_suffix'] ) ? sanitize_text_field( $options['text_zero_suffix'] ) : '';
			$text_one_suffix  = isset( $options['text_one_suffix'] ) ? sanitize_text_field( $options['text_one_suffix'] ) : '';
			$text_more_suffix = isset( $options['text_more_suffix'] ) ? sanitize_text_field( $options['text_more_suffix'] ) : '';

			// Return the current count without updating (silent fail).
			echo wp_kses_post(
				Themeist_IRecommendThis_Public::process_recommendation(
					$post_id,
					$text_zero_suffix,
					$text_one_suffix,
					$text_more_suffix,
					'get'
				)
			);
			exit;
		}

		// Process the recommendation update.
		$options = get_option( 'irecommendthis_settings' );
		$text_zero_suffix = isset( $options['text_zero_suffix'] ) ? sanitize_text_field( $options['text_zero_suffix'] ) : '';
		$text_one_suffix  = isset( $options['text_one_suffix'] ) ? sanitize_text_field( $options['text_one_suffix'] ) : '';
		$text_more_suffix = isset( $options['text_more_suffix'] ) ? sanitize_text_field( $options['text_more_suffix'] ) : '';

		echo wp_kses_post(
			Themeist_IRecommendThis_Public::process_recommendation(
				$post_id,
				$text_zero_suffix,
				$text_one_suffix,
				$text_more_suffix,
				'update'
			)
		);
		exit;
	}
}
