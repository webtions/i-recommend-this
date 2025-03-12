<?php
/**
 * Processor component for public-facing functionality.
 *
 * Handles the processing of recommendations.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle recommendation processing.
 */
class Themeist_IRecommendThis_Public_Processor {

	/**
	 * Process a recommendation for a post.
	 *
	 * @param int    $post_id           Post ID.
	 * @param string $text_zero_suffix  Text for zero suffix.
	 * @param string $text_one_suffix   Text for one suffix.
	 * @param string $text_more_suffix  Text for more suffix.
	 * @param string $action            Action to perform: 'get' or 'update'.
	 * @param string $unrecommend       Whether this is an unrecommend action (true/false as string).
	 * @return string HTML output for the recommendation count.
	 */
	public static function process_recommendation(
		$post_id,
		$text_zero_suffix = false,
		$text_one_suffix = false,
		$text_more_suffix = false,
		$action = 'get',
		$unrecommend = 'false'
	) {
		// Validate post ID.
		if ( ! is_numeric( $post_id ) ) {
			return '';
		}

		/**
		 * Filter the post ID before processing recommendation.
		 *
		 * @since 4.0.0
		 * @param int $post_id The post ID.
		 */
		$post_id = apply_filters( 'irecommendthis_process_post_id', $post_id );

		// Sanitize suffix inputs.
		$text_zero_suffix = sanitize_text_field( $text_zero_suffix );
		$text_one_suffix  = sanitize_text_field( $text_one_suffix );
		$text_more_suffix = sanitize_text_field( $text_more_suffix );

		// Get recommendation settings and count.
		$settings    = self::get_recommendation_settings( $post_id );
		$recommended = $settings['recommended'];

		/**
		 * Filter the current recommendation count before processing.
		 *
		 * @since 4.0.0
		 * @param int    $recommended The current recommendation count.
		 * @param int    $post_id     The post ID.
		 * @param string $action      The action being performed ('get' or 'update').
		 */
		$recommended = apply_filters( 'irecommendthis_pre_process_count', $recommended, $post_id, $action );

		if ( 'get' === $action ) {
			return self::get_recommendation_count( $post_id, $recommended, $settings, $text_zero_suffix, $text_one_suffix, $text_more_suffix );
		} elseif ( 'update' === $action ) {
			return self::update_recommendation_count( $post_id, $recommended, $settings, $text_zero_suffix, $text_one_suffix, $text_more_suffix, $unrecommend );
		}

		return '';
	}

	/**
	 * Get recommendation settings for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array Settings array.
	 */
	private static function get_recommendation_settings( $post_id ) {
		// Fetch options and recommendation count.
		$options          = get_option( 'irecommendthis_settings' );
		$recommended      = (int) get_post_meta( $post_id, '_recommended', true );
		$hide_zero        = isset( $options['hide_zero'] ) ? (int) $options['hide_zero'] : 0;
		$enable_unique_ip = isset( $options['enable_unique_ip'] ) ? (int) $options['enable_unique_ip'] : 0;

		return array(
			'options'          => $options,
			'recommended'      => $recommended,
			'hide_zero'        => $hide_zero,
			'enable_unique_ip' => $enable_unique_ip,
		);
	}

	/**
	 * Get the suffix for a recommendation count.
	 *
	 * @param int    $count             Recommendation count.
	 * @param string $text_zero_suffix  Text for zero suffix.
	 * @param string $text_one_suffix   Text for one suffix.
	 * @param string $text_more_suffix  Text for more suffix.
	 * @return string Suffix text.
	 */
	private static function get_suffix( $count, $text_zero_suffix, $text_one_suffix, $text_more_suffix ) {
		if ( 0 === $count ) {
			return $text_zero_suffix;
		} elseif ( 1 === $count ) {
			return $text_one_suffix;
		} else {
			return $text_more_suffix;
		}
	}

	/**
	 * Get the HTML output for a recommendation count.
	 *
	 * @param int    $post_id           Post ID.
	 * @param int    $recommended       Recommendation count.
	 * @param array  $settings          Settings array.
	 * @param string $text_zero_suffix  Text for zero suffix.
	 * @param string $text_one_suffix   Text for one suffix.
	 * @param string $text_more_suffix  Text for more suffix.
	 * @return string HTML output.
	 */
	private static function get_recommendation_count( $post_id, $recommended, $settings, $text_zero_suffix, $text_one_suffix, $text_more_suffix ) {
		$hide_zero = $settings['hide_zero'];

		/**
		 * Action fired before getting the recommendation count.
		 *
		 * @since 4.0.0
		 * @param int $post_id     The post ID.
		 * @param int $recommended The current recommendation count.
		 */
		do_action( 'irecommendthis_before_get_recommendation', $post_id, $recommended );

		// Initialize recommendation count if not set.
		if ( ! $recommended ) {
			$recommended = 0;
			add_post_meta( $post_id, '_recommended', $recommended, true );
		}

		// Get the appropriate suffix.
		$suffix = self::get_suffix( $recommended, $text_zero_suffix, $text_one_suffix, $text_more_suffix );

		// Create output HTML.
		$output = self::generate_count_html( $recommended, $suffix, $hide_zero );

		/**
		 * Filter the recommendation count HTML output.
		 *
		 * @since 4.0.0
		 * @param string $output      The HTML output.
		 * @param int    $recommended The recommendation count.
		 * @param int    $post_id     The post ID.
		 * @param string $suffix      The suffix text.
		 */
		return apply_filters( 'irecommendthis_count_output', $output, $recommended, $post_id, $suffix );
	}

	/**
	 * Update the recommendation count for a post.
	 *
	 * @param int    $post_id           Post ID.
	 * @param int    $recommended       Recommendation count.
	 * @param array  $settings          Settings array.
	 * @param string $text_zero_suffix  Text for zero suffix.
	 * @param string $text_one_suffix   Text for one suffix.
	 * @param string $text_more_suffix  Text for more suffix.
	 * @param string $unrecommend       Whether this is an unrecommend action (true/false as string).
	 * @return string HTML output.
	 */
	private static function update_recommendation_count(
		$post_id,
		$recommended,
		$settings,
		$text_zero_suffix,
		$text_one_suffix,
		$text_more_suffix,
		$unrecommend = 'false'
	) {
		$hide_zero        = $settings['hide_zero'];
		$enable_unique_ip = $settings['enable_unique_ip'];

		/**
		 * Action fired before updating the recommendation count.
		 *
		 * @since 4.0.0
		 * @param int $post_id     The post ID.
		 * @param int $recommended The current recommendation count.
		 */
		do_action( 'irecommendthis_before_update_recommendation', $post_id, $recommended );

		// Process IP-based tracking if enabled.
		if ( 0 !== $enable_unique_ip ) {
			self::process_ip_based_recommendation( $post_id, $unrecommend );
		}

		// Process cookie-based recommendation and get updated count.
		$recommended = self::process_cookie_based_recommendation( $post_id, $recommended, $unrecommend );

		// Update the recommendation count.
		update_post_meta( $post_id, '_recommended', $recommended );

		/**
		 * Action fired after a post's recommendation count is updated.
		 *
		 * This action provides a hook point for cache clearing and other post-update actions.
		 * Useful for integration with caching plugins to refresh content when recommendations change.
		 *
		 * @since 4.0.0
		 *
		 * @param int    $post_id     The ID of the post that was recommended.
		 * @param int    $recommended The updated recommendation count.
		 * @param string $action      The action performed: 'get' or 'update'.
		 */
		do_action( 'irecommendthis_after_process_recommendation', $post_id, $recommended, 'update' );

		// Get the appropriate suffix.
		$suffix = self::get_suffix( $recommended, $text_zero_suffix, $text_one_suffix, $text_more_suffix );

		// Create output HTML.
		$output = self::generate_count_html( $recommended, $suffix, $hide_zero );

		/**
		 * Filter the recommendation count HTML output.
		 *
		 * @since 4.0.0
		 * @param string $output      The HTML output.
		 * @param int    $recommended The recommendation count.
		 * @param int    $post_id     The post ID.
		 * @param string $suffix      The suffix text.
		 */
		return apply_filters( 'irecommendthis_count_output', $output, $recommended, $post_id, $suffix );
	}

	/**
	 * Process IP-based recommendation.
	 *
	 * @param int    $post_id     Post ID.
	 * @param string $unrecommend Whether this is an unrecommend action (true/false as string).
	 */
	private static function process_ip_based_recommendation( $post_id, $unrecommend = 'false' ) {
		global $wpdb;

		$ip            = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$anonymized_ip = self::anonymize_ip( $ip );

		// Check for unrecommend action.
		if ( 'true' === $unrecommend ) {
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->prefix}irecommendthis_votes
					WHERE post_id = %d AND ip = %s",
					$post_id,
					$anonymized_ip
				)
			);

			/**
			 * Action fired after deleting an IP record.
			 *
			 * @since 4.0.0
			 * @param int    $post_id       The post ID.
			 * @param string $anonymized_ip The anonymized IP.
			 */
			do_action( 'irecommendthis_ip_record_deleted', $post_id, $anonymized_ip );
			return;
		}

		$vote_status_by_ip = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->prefix}irecommendthis_votes
				WHERE post_id = %d AND ip = %s",
				$post_id,
				$anonymized_ip
			)
		);

		// Insert only if no vote exists.
		if ( empty( $vote_status_by_ip ) || 0 === (int) $vote_status_by_ip ) {
			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$wpdb->prefix}irecommendthis_votes
					VALUES ('', NOW(), %d, %s )",
					$post_id,
					$anonymized_ip
				)
			);

			/**
			 * Action fired after adding an IP record.
			 *
			 * @since 4.0.0
			 * @param int    $post_id       The post ID.
			 * @param string $anonymized_ip The anonymized IP.
			 */
			do_action( 'irecommendthis_ip_record_added', $post_id, $anonymized_ip );
		}
	}

	/**
	 * Process cookie-based recommendation.
	 *
	 * @param int    $post_id     Post ID.
	 * @param int    $recommended Current recommendation count.
	 * @param string $unrecommend Whether this is an unrecommend action (true/false as string).
	 * @return int Updated recommendation count.
	 */
	private static function process_cookie_based_recommendation( $post_id, $recommended, $unrecommend = 'false' ) {
		$cookie_name   = 'irecommendthis_' . $post_id;
		$cookie_exists = isset( $_COOKIE[ $cookie_name ] );

		// Process unrecommend action.
		if ( 'true' === $unrecommend ) {
			// Case 1: User is unliking a post they previously liked.
			if ( $cookie_exists ) {
				// Delete the cookie (set its expiration to the past).
				setcookie( $cookie_name, '', time() - 3600, '/' );

				// Only decrement if cookie existed.
				$recommended = max( 0, $recommended - 1 );

				/**
				 * Action fired after decrementing the recommendation count.
				 *
				 * @since 4.0.0
				 * @param int  $post_id      The post ID.
				 * @param int  $recommended  The updated recommendation count.
				 * @param bool $cookie_exists Whether the cookie existed.
				 */
				do_action( 'irecommendthis_count_decremented', $post_id, $recommended, $cookie_exists );
			}
		} elseif ( 'false' === $unrecommend ) {
			// Case 2: User is liking a post they haven't liked before.
			if ( ! $cookie_exists ) {
				// Build secure cookie parameters.
				$cookie_params = array(
					'expires'  => time() + YEAR_IN_SECONDS,
					'path'     => '/',
					'domain'   => '',
					'secure'   => is_ssl(),
					'httponly' => true,
					'samesite' => 'Strict',
				);

				/**
				 * Filter cookie parameters.
				 *
				 * @since 4.0.0
				 * @param array $cookie_params Cookie parameters.
				 * @param int   $post_id       The post ID.
				 */
				$cookie_params = apply_filters( 'irecommendthis_cookie_parameters', $cookie_params, $post_id );

				// Set the cookie - using modern cookie parameters where supported.
				if ( PHP_VERSION_ID >= 70300 ) {
					setcookie(
						$cookie_name,
						(string) time(),
						$cookie_params
					);
				} else {
					// Fallback for older PHP versions.
					setcookie(
						$cookie_name,
						(string) time(),
						$cookie_params['expires'],
						$cookie_params['path']
					);
				}

				// Increment the count.
				++$recommended;

				/**
				 * Action fired after incrementing the recommendation count.
				 *
				 * @since 4.0.0
				 * @param int  $post_id      The post ID.
				 * @param int  $recommended  The updated recommendation count.
				 * @param bool $cookie_exists Whether the cookie existed.
				 */
				do_action( 'irecommendthis_count_incremented', $post_id, $recommended, $cookie_exists );
			} else {
				// User is trying to like but the cookie already exists.
				// This can happen when cookie wasn't properly cleared in a previous unlike.
				// Set cookie again to ensure state consistency.
				setcookie( $cookie_name, (string) time(), time() + YEAR_IN_SECONDS, '/' );
			}//end if
		}//end if

		return $recommended;
	}

	/**
	 * Generate HTML for the recommendation count.
	 *
	 * @param int    $recommended Count of recommendations.
	 * @param string $suffix      Suffix text.
	 * @param int    $hide_zero   Whether to hide zero count.
	 * @return string HTML output.
	 */
	private static function generate_count_html( $recommended, $suffix, $hide_zero ) {
		$classes      = array( 'irecommendthis-count' );
		$inline_style = '';

		// Add a class for zero count for styling.
		if ( 0 === $recommended ) {
			$classes[] = 'count-zero';

			// Hide the count if set in options.
			if ( 1 === $hide_zero ) {
				$inline_style = ' style="display: none;"';
			}
		}

		$class_attr = implode( ' ', $classes );

		return '<span class="' . esc_attr( $class_attr ) . '"' . $inline_style . '>' . esc_html( $recommended ) . '</span> <span class="irecommendthis-suffix">' . esc_html( $suffix ) . '</span>';
	}

	/**
	 * Anonymize an IP address for secure storage using global hashing.
	 *
	 * Creates a secure hash of the IP address using WordPress
	 * native cryptographic functions, making it impossible to
	 * recover the original IP while still allowing tracking
	 * of user activity across different posts.
	 *
	 * @since 4.0.0
	 *
	 * @param string $ip The IP address to anonymize.
	 * @return string The anonymized (hashed) IP.
	 */
	public static function anonymize_ip( $ip ) {
		if ( empty( $ip ) ) {
			$ip = 'unknown';
		}
		$auth_salt = wp_salt( 'auth' );
		$site_hash = defined( 'COOKIEHASH' ) ? COOKIEHASH : md5( site_url() );
		$hashed_ip = wp_hash( $ip . $site_hash, 'auth' );
		/**
		 * Filter the anonymized IP address.
		 *
		 * @since 4.0.0
		 * @param string $hashed_ip The anonymized IP.
		 * @param string $ip        The original IP.
		 */
		return apply_filters( 'irecommendthis_anonymized_ip', $hashed_ip, $ip );
	}
}
