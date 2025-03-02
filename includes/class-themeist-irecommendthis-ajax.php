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
	 * Processor component instance.
	 *
	 * @var Themeist_IRecommendThis_Public_Processor
	 */
	private $processor_component;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->processor_component = new Themeist_IRecommendThis_Public_Processor();
	}

	/**
	 * Add AJAX hooks.
	 */
	public function add_ajax_hooks() {
		// Register the new action name
		add_action( 'wp_ajax_irecommendthis', array( $this, 'ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_irecommendthis', array( $this, 'ajax_callback' ) );

		// Keep the old action name for backward compatibility
		add_action( 'wp_ajax_dot-irecommendthis', array( $this, 'ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_dot-irecommendthis', array( $this, 'ajax_callback' ) );
	}

	/**
	 * AJAX Callback for recommendation.
	 */
	public function ajax_callback() {
		// Check nonce for security.
		$nonce_verified = false;
		if ( isset( $_POST['security'] ) ) {
			// Check both old and new nonce names for backward compatibility
			$nonce_verified = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'irecommendthis-nonce' ) ||
			                  wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'dot-irecommendthis-nonce' );
		}

		if ( $nonce_verified ) {
			// Get plugin options.
			$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );
			$text_zero_suffix = isset( $options['text_zero_suffix'] ) ? sanitize_text_field( $options['text_zero_suffix'] ) : '';
			$text_one_suffix  = isset( $options['text_one_suffix'] ) ? sanitize_text_field( $options['text_one_suffix'] ) : '';
			$text_more_suffix = isset( $options['text_more_suffix'] ) ? sanitize_text_field( $options['text_more_suffix'] ) : '';

			// Get the post ID - CRITICAL FIX: properly handle data consistently
			if ( isset( $_POST['recommend_id'] ) ) {
				// Make sure we're getting the actual post ID, not the full element ID
				$post_id = intval( sanitize_text_field( wp_unslash( $_POST['recommend_id'] ) ) );

				// Process the recommendation
				echo wp_kses_post( $this->processor_component->process_recommendation(
					$post_id,
					$text_zero_suffix,
					$text_one_suffix,
					$text_more_suffix,
					'update'
				) );
			} elseif ( isset( $_POST['post_id'] ) ) {
				// For backward compatibility
				$post_id = intval( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) );

				echo wp_kses_post( $this->processor_component->process_recommendation(
					$post_id,
					$text_zero_suffix,
					$text_one_suffix,
					$text_more_suffix,
					'get'
				) );
			} else {
				// No valid post ID was provided
				die( esc_html__( 'Error: No valid post ID provided.', 'i-recommend-this' ) );
			}
		} else {
			// Nonce verification failed.
			die( esc_html__( 'Nonce verification failed. This request is not valid.', 'i-recommend-this' ) );
		}
		exit;
	}
}
