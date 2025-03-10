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
	 * @return string HTML output for the recommendation count.
	 */
	public static function process_recommendation(
		$post_id,
		$text_zero_suffix = false,
		$text_one_suffix = false,
		$text_more_suffix = false,
		$action = 'get'
	) {
		// Validate post ID.
		if ( ! is_numeric( $post_id ) ) {
			return;
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

		// Fetch options and recommendation count.
		$options          = get_option( 'irecommendthis_settings' );
		$recommended      = (int) get_post_meta( $post_id, '_recommended', true );
		$hide_zero        = isset( $options['hide_zero'] ) ? (int) $options['hide_zero'] : 0;
		$enable_unique_ip = isset( $options['enable_unique_ip'] ) ? (int) $options['enable_unique_ip'] : 0;

		/**
		 * Filter the current recommendation count before processing.
		 *
		 * @since 4.0.0
		 * @param int    $recommended The current recommendation count.
		 * @param int    $post_id     The post ID.
		 * @param string $action      The action being performed ('get' or 'update').
		 */
		$recommended = apply_filters( 'irecommendthis_pre_process_count', $recommended, $post_id, $action );

		/**
		 * Function for getting the suffix based on the recommendation count.
		 */
		$get_suffix = function ( $count ) use ( $text_zero_suffix, $text_one_suffix, $text_more_suffix ) {
			if ( 0 === $count ) {
				return $text_zero_suffix;
			} elseif ( 1 === $count ) {
				return $text_one_suffix;
			} else {
				return $text_more_suffix;
			}
		};

		// Handling the 'get' action.
		if ( 'get' === $action ) {
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

			// Output HTML for the recommendation button.
			$suffix = $get_suffix( $recommended );
			$output = ( 0 === $recommended && 1 === $hide_zero )
				? '<span class="irecommendthis-count" style="display: none;">0</span> <span class="irecommendthis-suffix">' . esc_html( $suffix ) . '</span>'
				: '<span class="irecommendthis-count">' . esc_html( $recommended ) . '</span> <span class="irecommendthis-suffix">' . esc_html( $suffix ) . '</span>';

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

		// Handling the 'update' action.
		if ( 'update' === $action ) {
			/**
			 * Action fired before updating the recommendation count.
			 *
			 * @since 4.0.0
			 * @param int $post_id     The post ID.
			 * @param int $recommended The current recommendation count.
			 */
			do_action( 'irecommendthis_before_update_recommendation', $post_id, $recommended );

			global $wpdb;

			// Process unique IP address checking if enabled.
			if ( 0 !== $enable_unique_ip ) {
				$ip            = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
				$anonymized_ip = self::anonymize_ip( $ip );

				if (
					isset( $_POST['unrecommend'] ) &&
					'true' === sanitize_text_field( wp_unslash( $_POST['unrecommend'] ) )
				) {
					// Delete the vote record for this IP and post.
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
				} else {
					// Check if user has already voted.
					$vote_status_by_ip = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT COUNT(*)
							FROM {$wpdb->prefix}irecommendthis_votes
							WHERE post_id = %d AND ip = %s",
							$post_id,
							$anonymized_ip
						)
					);

					// Only insert if no vote exists for this IP and post.
					if ( empty( $vote_status_by_ip ) || 0 === $vote_status_by_ip ) {
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
				}//end if
			}//end if

			// Check for cookie.
			$cookie_exists = isset( $_COOKIE[ 'irecommendthis_' . $post_id ] );

			// Handle the case where the user is un-recommending.
			if (
				$cookie_exists &&
				isset( $_POST['unrecommend'] ) &&
				'true' === sanitize_text_field( wp_unslash( $_POST['unrecommend'] ) )
			) {
				// Prepare secure cookie parameters
				$cookie_params = array(
					'expires'  => time() - 3600, // In the past to delete
					'path'     => '/',
					'secure'   => self::is_connection_secure(),
					'httponly' => true,
					'samesite' => 'Lax',
				);

				/**
				 * Filter cookie parameters for deletion.
				 *
				 * @since 4.0.0
				 * @param array $cookie_params Cookie parameters.
				 * @param int   $post_id       The post ID.
				 */
				$cookie_params = apply_filters( 'irecommendthis_cookie_delete_params', $cookie_params, $post_id );

				// PHP 7.3+ can use array syntax, but we need to be compatible with older versions
				if ( PHP_VERSION_ID < 70300 ) {
					setcookie(
						'irecommendthis_' . $post_id,
						'',
						$cookie_params['expires'],
						$cookie_params['path'],
						'',
						$cookie_params['secure'],
						$cookie_params['httponly']
					);
				} else {
					setcookie( 'irecommendthis_' . $post_id, '', $cookie_params );
				}

				// Decrement the count.
				$recommended = max( 0, $recommended - 1 );

				/**
				 * Action fired after decrementing recommendation count.
				 *
				 * @since 4.0.0
				 * @param int  $post_id       The post ID.
				 * @param int  $recommended   The updated recommendation count.
				 * @param bool $cookie_exists Whether a cookie existed.
				 */
				do_action( 'irecommendthis_count_decremented', $post_id, $recommended, $cookie_exists );
			} elseif (
				isset( $_POST['unrecommend'] ) &&
				'false' === sanitize_text_field( wp_unslash( $_POST['unrecommend'] ) )
			) {
				// Prepare secure cookie parameters - set for 1 year
				$cookie_params = array(
					'expires'  => time() + 31536000,
					'path'     => '/',
					'secure'   => self::is_connection_secure(),
					'httponly' => true,
					'samesite' => 'Lax',
				);

				/**
				 * Filter cookie parameters for setting.
				 *
				 * @since 4.0.0
				 * @param array $cookie_params Cookie parameters.
				 * @param int   $post_id       The post ID.
				 */
				$cookie_params = apply_filters( 'irecommendthis_cookie_set_params', $cookie_params, $post_id );

				// PHP 7.3+ can use array syntax, but we need to be compatible with older versions
				if ( PHP_VERSION_ID < 70300 ) {
					setcookie(
						'irecommendthis_' . $post_id,
						time(),
						$cookie_params['expires'],
						$cookie_params['path'],
						'',
						$cookie_params['secure'],
						$cookie_params['httponly']
					);
				} else {
					setcookie( 'irecommendthis_' . $post_id, time(), $cookie_params );
				}

				// Increment the count.
				++$recommended;

				/**
				 * Action fired after incrementing recommendation count.
				 *
				 * @since 4.0.0
				 * @param int  $post_id       The post ID.
				 * @param int  $recommended   The updated recommendation count.
				 * @param bool $cookie_exists Whether a cookie existed.
				 */
				do_action( 'irecommendthis_count_incremented', $post_id, $recommended, $cookie_exists );
			}//end if

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
			do_action( 'irecommendthis_after_process_recommendation', $post_id, $recommended, $action );

			// Output HTML for the recommendation button.
			$suffix = $get_suffix( $recommended );
			$output = ( 0 === $recommended && 1 === $hide_zero )
				? '<span class="irecommendthis-count" style="display: none;">0</span> <span class="irecommendthis-suffix">' . esc_html( $suffix ) . '</span>'
				: '<span class="irecommendthis-count">' . esc_html( $recommended ) . '</span> <span class="irecommendthis-suffix">' . esc_html( $suffix ) . '</span>';

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
		}//end if
	}

	/**
	 * Determine if the current connection is secure.
	 *
	 * Checks various server variables to determine if the connection
	 * is using HTTPS, including behind proxies and load balancers.
	 *
	 * @return bool Whether the connection is secure.
	 */
	private static function is_connection_secure() {
		// Standard SSL check
		if ( is_ssl() ) {
			return true;
		}

		// Check common proxy/load balancer headers
		if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === strtolower( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) {
			return true;
		}

		if ( isset( $_SERVER['HTTP_X_FORWARDED_SSL'] ) && ( 'on' === strtolower( $_SERVER['HTTP_X_FORWARDED_SSL'] ) || '1' === $_SERVER['HTTP_X_FORWARDED_SSL'] ) ) {
			return true;
		}

		if ( isset( $_SERVER['HTTP_FRONT_END_HTTPS'] ) && ( 'on' === strtolower( $_SERVER['HTTP_FRONT_END_HTTPS'] ) || '1' === $_SERVER['HTTP_FRONT_END_HTTPS'] ) ) {
			return true;
		}

		// Cloudflare specific
		if ( isset( $_SERVER['HTTP_CF_VISITOR'] ) && false !== strpos( $_SERVER['HTTP_CF_VISITOR'], 'https' ) ) {
			return true;
		}

		// Site configuration check - this helps when the site is configured for HTTPS but accessed via HTTP
		if ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ) {
			return true;
		}

		return false;
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
		// Empty IPs should return a consistent hash.
		if ( empty( $ip ) ) {
			$ip = 'unknown';
		}

		// Use WordPress salt for authentication.
		$auth_salt = wp_salt( 'auth' );

		// Use site-specific hash for additional entropy.
		$site_hash = defined( 'COOKIEHASH' ) ? COOKIEHASH : md5( site_url() );

		// Create the hash using WordPress hash function with site context.
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
