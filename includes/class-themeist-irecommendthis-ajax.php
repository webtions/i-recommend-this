<?php

class Themeist_IRecommendThis_Ajax {

	/**
	 * Add hooks for AJAX functionality.
	 */
	public function add_ajax_hooks() {
		add_action( 'wp_ajax_dot-irecommendthis', array( $this, 'ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_dot-irecommendthis', array( $this, 'ajax_callback' ) );
	}

	/**
	 * AJAX Callback for recommendation.
	 */
	public function ajax_callback() {
		if ( isset( $_POST['security'] ) && wp_verify_nonce( $_POST['security'], 'dot-irecommendthis-nonce' ) ) {
			$options = get_option( 'dot_irecommendthis_settings' );
			$text_zero_suffix = isset( $options['text_zero_suffix'] ) ? $options['text_zero_suffix'] : '';
			$text_one_suffix = isset( $options['text_one_suffix'] ) ? $options['text_one_suffix'] : '';
			$text_more_suffix = isset( $options['text_more_suffix'] ) ? $options['text_more_suffix'] : '';

			if ( isset( $_POST['recommend_id'] ) ) {
				$post_id = intval( str_replace( 'dot-irecommendthis-', '', $_POST['recommend_id'] ) );
				echo Themeist_IRecommendThis_Public::dot_recommend_this( $post_id, $text_zero_suffix, $text_one_suffix, $text_more_suffix, 'update' );
			} else {
				$post_id = intval( str_replace( 'dot-irecommendthis-', '', $_POST['post_id'] ) );
				echo Themeist_IRecommendThis_Public::dot_recommend_this( $post_id, $text_zero_suffix, $text_one_suffix, $text_more_suffix, 'get' );
			}
		} else {
			die( 'Nonce verification failed. This request is not valid.' );
		}

		exit;
	}
}
