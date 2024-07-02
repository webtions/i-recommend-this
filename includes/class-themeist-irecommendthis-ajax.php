<?php
/**
 * Handles AJAX functionality for the I Recommend This plugin.
 *
 * @package IRecommendThis
 */
class Themeist_IRecommendThis_Ajax {

	/**
	 * AJAX Callback for recommendation.
	 */
	public static function ajax_callback() {
		// Check nonce for security.
		if ( isset( $_POST['security'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'dot-irecommendthis-nonce' ) ) {
			// Get plugin options.
			$options          = get_option( 'dot_irecommendthis_settings' );
			$text_zero_suffix = isset( $options['text_zero_suffix'] ) ? sanitize_text_field( $options['text_zero_suffix'] ) : '';
			$text_one_suffix  = isset( $options['text_one_suffix'] ) ? sanitize_text_field( $options['text_one_suffix'] ) : '';
			$text_more_suffix = isset( $options['text_more_suffix'] ) ? sanitize_text_field( $options['text_more_suffix'] ) : '';

			// Check for recommend_id and update recommendation.
			if ( isset( $_POST['recommend_id'] ) ) {
				$post_id = intval( str_replace( 'dot-irecommendthis-', '', sanitize_text_field( wp_unslash( $_POST['recommend_id'] ) ) ) );
				echo wp_kses_post( Themeist_IRecommendThis_Public::dot_recommend_this( $post_id, $text_zero_suffix, $text_one_suffix, $text_more_suffix, 'update' ) );
			} elseif ( isset( $_POST['post_id'] ) ) {
				// If no recommend_id, check for post_id and get recommendation.
				$post_id = intval( str_replace( 'dot-irecommendthis-', '', sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) ) );
				echo wp_kses_post( Themeist_IRecommendThis_Public::dot_recommend_this( $post_id, $text_zero_suffix, $text_one_suffix, $text_more_suffix, 'get' ) );
			}
		} else {
			// Nonce verification failed.
			die( esc_html__( 'Nonce verification failed. This request is not valid.', 'i-recommend-this' ) );
		}

		exit;
	}

	/**
	 * Add AJAX hooks.
	 */
	public function add_ajax_hooks() {
		add_action( 'wp_ajax_dot-irecommendthis', array( __CLASS__, 'ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_dot-irecommendthis', array( __CLASS__, 'ajax_callback' ) );
	}
}
