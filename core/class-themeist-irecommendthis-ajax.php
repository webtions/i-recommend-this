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
		// Simple cookie check endpoint - only verifies cookies can be set
		if (isset($_POST['cookie_check']) && $_POST['cookie_check'] === 'true') {
			wp_send_json(array('cookie_check' => 'complete'));
			exit;
		}

		// Get the post ID.
		if (isset($_POST['recommend_id'])) {
			$post_id = intval(sanitize_text_field(wp_unslash($_POST['recommend_id'])));
		} elseif (isset($_POST['post_id'])) {
			$post_id = intval(sanitize_text_field(wp_unslash($_POST['post_id'])));
		} else {
			wp_send_json_error(array('message' => 'No valid post ID provided'));
			exit;
		}

		/**
		 * Filter the post ID before processing.
		 */
		$post_id = apply_filters('irecommendthis_ajax_post_id', $post_id);

		// Check for valid post
		if (!get_post($post_id)) {
			wp_send_json_error(array('message' => 'Invalid post ID'));
			exit;
		}

		// Check for nonce
		$nonce_value = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

		if (!wp_verify_nonce($nonce_value, 'irecommendthis-nonce')) {
			/**
			 * Action fired when nonce verification fails.
			 */
			do_action('irecommendthis_ajax_nonce_failure', $post_id);

			// Get plugin settings for returning current state only
			$options = get_option('irecommendthis_settings');
			$text_zero_suffix = isset($options['text_zero_suffix']) ? sanitize_text_field($options['text_zero_suffix']) : '';
			$text_one_suffix = isset($options['text_one_suffix']) ? sanitize_text_field($options['text_one_suffix']) : '';
			$text_more_suffix = isset($options['text_more_suffix']) ? sanitize_text_field($options['text_more_suffix']) : '';

			// Return current count without processing
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

		// Check for unrecommend parameter
		$unrecommend = false;
		if (isset($_POST['unrecommend'])) {
			$unrecommend = 'true' === sanitize_text_field(wp_unslash($_POST['unrecommend']));
		}

		/**
		 * Action fired before processing AJAX recommendation request.
		 */
		do_action('irecommendthis_before_ajax_process', $post_id, $_POST);

		// Process the recommendation update.
		$options = get_option('irecommendthis_settings');
		$text_zero_suffix = isset($options['text_zero_suffix']) ? sanitize_text_field($options['text_zero_suffix']) : '';
		$text_one_suffix = isset($options['text_one_suffix']) ? sanitize_text_field($options['text_one_suffix']) : '';
		$text_more_suffix = isset($options['text_more_suffix']) ? sanitize_text_field($options['text_more_suffix']) : '';

		$result = Themeist_IRecommendThis_Public_Processor::process_recommendation(
			$post_id,
			$text_zero_suffix,
			$text_one_suffix,
			$text_more_suffix,
			'update'
		);

		/**
		 * Action fired after processing AJAX recommendation request.
		 */
		do_action('irecommendthis_after_ajax_process', $post_id, $result, $_POST);

		/**
		 * Filter the AJAX response HTML.
		 */
		$result = apply_filters('irecommendthis_ajax_response', $result, $post_id);

		echo wp_kses_post($result);
		exit;
	}
}
