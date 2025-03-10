<?php
/**
 * Handles AJAX functionality for the I Recommend This plugin.
 *
 * @package IRecommendThis
 * @subpackage Core
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

		// Legacy hooks for backward compatibility.
		add_action( 'wp_ajax_dot-irecommendthis', array( $this, 'legacy_ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_dot-irecommendthis', array( $this, 'legacy_ajax_callback' ) );

		/**
		 * Action fired after AJAX hooks are registered.
		 *
		 * @since 4.0.0
		 */
		do_action( 'irecommendthis_ajax_hooks_registered' );
	}

	/**
	 * Legacy AJAX callback - redirects to main callback for backward compatibility.
	 */
	public function legacy_ajax_callback() {
		/**
		 * Action fired before legacy AJAX request is processed.
		 *
		 * @since 4.0.0
		 * @param array $_POST The POST data.
		 */
		do_action( 'irecommendthis_before_legacy_ajax', $_POST );

		$this->ajax_callback();
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

		/**
		 * Filter the post ID before processing.
		 *
		 * Allows modifying the post ID that will be used for the recommendation.
		 *
		 * @since 4.0.0
		 * @param int $post_id The post ID.
		 */
		$post_id = apply_filters( 'irecommendthis_ajax_post_id', $post_id );

		// Check for nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'irecommendthis-nonce' ) ) {
			/**
			 * Action fired when nonce verification fails.
			 *
			 * @since 4.0.0
			 * @param int $post_id The post ID.
			 */
			do_action( 'irecommendthis_ajax_nonce_failure', $post_id );

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// In debug mode, log more detailed error.
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'I Recommend This: Nonce verification failed' );
			}

			// Get plugin settings.
			$options          = get_option( 'irecommendthis_settings' );
			$text_zero_suffix = isset( $options['text_zero_suffix'] ) ? sanitize_text_field( $options['text_zero_suffix'] ) : '';
			$text_one_suffix  = isset( $options['text_one_suffix'] ) ? sanitize_text_field( $options['text_one_suffix'] ) : '';
			$text_more_suffix = isset( $options['text_more_suffix'] ) ? sanitize_text_field( $options['text_more_suffix'] ) : '';

			// Return the current count without updating (silent fail).
			echo wp_kses_post(
				Themeist_IRecommendThis_Public_Processor::process_recommendation(
					$post_id,
					$text_zero_suffix,
					$text_one_suffix,
					$text_more_suffix,
					'get'
				)
			);
			exit;
		}

		/**
		 * Action fired before processing AJAX recommendation request.
		 *
		 * @since 4.0.0
		 * @param int   $post_id The post ID.
		 * @param array $_POST   The POST data.
		 */
		do_action( 'irecommendthis_before_ajax_process', $post_id, $_POST );

		// Process the recommendation update.
		$options          = get_option( 'irecommendthis_settings' );
		$text_zero_suffix = isset( $options['text_zero_suffix'] ) ? sanitize_text_field( $options['text_zero_suffix'] ) : '';
		$text_one_suffix  = isset( $options['text_one_suffix'] ) ? sanitize_text_field( $options['text_one_suffix'] ) : '';
		$text_more_suffix = isset( $options['text_more_suffix'] ) ? sanitize_text_field( $options['text_more_suffix'] ) : '';

		$result = Themeist_IRecommendThis_Public_Processor::process_recommendation(
			$post_id,
			$text_zero_suffix,
			$text_one_suffix,
			$text_more_suffix,
			'update'
		);

		/**
		 * Action fired after processing AJAX recommendation request.
		 *
		 * @since 4.0.0
		 * @param int    $post_id The post ID.
		 * @param string $result  The HTML result.
		 * @param array  $_POST   The POST data.
		 */
		do_action( 'irecommendthis_after_ajax_process', $post_id, $result, $_POST );

		/**
		 * Filter the AJAX response HTML.
		 *
		 * @since 4.0.0
		 * @param string $result  The HTML result.
		 * @param int    $post_id The post ID.
		 */
		$result = apply_filters( 'irecommendthis_ajax_response', $result, $post_id );

		echo wp_kses_post( $result );
		exit;
	}
}
