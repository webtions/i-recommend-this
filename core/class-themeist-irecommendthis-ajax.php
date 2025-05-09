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

		/**
		 * Action fired after AJAX hooks are registered.
		 *
		 * @since 4.0.0
		 */
		do_action( 'irecommendthis_ajax_hooks_registered' );
	}

	/**
	 * AJAX Callback for recommendation.
	 */
	public function ajax_callback() {
		// Send headers before any output or cookie operations.
		header( 'Content-Type: text/html; charset=UTF-8' );

		// Simple cookie check endpoint - only verifies cookies can be set.
		if ( isset( $_POST['cookie_check'] ) && 'true' === $_POST['cookie_check'] ) {
			wp_send_json_success( array( 'cookie_check' => 'complete' ) );
		}

		// Verify the security nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'irecommendthis-nonce' ) ) {
			wp_send_json_error(
				array(
					'message' => 'Security check failed.',
					'code'    => 'invalid_nonce',
				)
			);
		}

		// Get the post ID.
		if ( isset( $_POST['recommend_id'] ) ) {
			$post_id = intval( sanitize_text_field( wp_unslash( $_POST['recommend_id'] ) ) );
		} elseif ( isset( $_POST['post_id'] ) ) {
			$post_id = intval( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) );
		} else {
			wp_send_json_error(
				array(
					'message' => 'No valid post ID provided.',
					'code'    => 'missing_post_id',
				)
			);
		}

		/**
		 * Filter the post ID before processing.
		 *
		 * @since 4.0.0
		 * @param int $post_id The post ID.
		 */
		$post_id = apply_filters( 'irecommendthis_ajax_post_id', $post_id );

		// Check for valid post.
		if ( ! get_post( $post_id ) ) {
			wp_send_json_error(
				array(
					'message' => 'Invalid post ID.',
					'code'    => 'invalid_post_id',
				)
			);
		}

		// Process the recommendation update.
		$options = get_option( 'irecommendthis_settings' );

		$text_zero_suffix = isset( $options['text_zero_suffix'] ) ? sanitize_text_field( $options['text_zero_suffix'] ) : '';
		$text_one_suffix  = isset( $options['text_one_suffix'] ) ? sanitize_text_field( $options['text_one_suffix'] ) : '';
		$text_more_suffix = isset( $options['text_more_suffix'] ) ? sanitize_text_field( $options['text_more_suffix'] ) : '';

		// Get unrecommend flag - properly sanitized.
		$unrecommend = isset( $_POST['unrecommend'] ) ? sanitize_text_field( wp_unslash( $_POST['unrecommend'] ) ) : 'false';

		$result = Themeist_IRecommendThis_Public_Processor::process_recommendation(
			$post_id,
			$text_zero_suffix,
			$text_one_suffix,
			$text_more_suffix,
			'update',
			$unrecommend
		);

		// Return a properly escaped response.
		echo wp_kses_post( $result );
		exit;
	}
}
