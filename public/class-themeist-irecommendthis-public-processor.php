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
	public static function process_recommendation( $post_id, $text_zero_suffix = false, $text_one_suffix = false, $text_more_suffix = false, $action = 'get' ) {
		// Validate post ID.
		if ( ! is_numeric( $post_id ) ) {
			return;
		}

		// Sanitize suffix inputs.
		$text_zero_suffix = sanitize_text_field( $text_zero_suffix );
		$text_one_suffix  = sanitize_text_field( $text_one_suffix );
		$text_more_suffix = sanitize_text_field( $text_more_suffix );

		// Fetch options and recommendation count.
		$options = get_option( 'irecommendthis_settings' );
		$recommended = (int) get_post_meta( $post_id, '_recommended', true );
		$hide_zero = isset( $options['hide_zero'] ) ? (int) $options['hide_zero'] : 0;
		$enable_unique_ip = isset( $options['enable_unique_ip'] ) ? (int) $options['enable_unique_ip'] : 0;

		// Function for getting the suffix based on the recommendation count.
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
			// Initialize recommendation count if not set.
			if ( ! $recommended ) {
				$recommended = 0;
				add_post_meta( $post_id, '_recommended', $recommended, true );
			}

			// Output HTML for the recommendation count.
			$suffix = $get_suffix( $recommended );
			$output = ( 0 === $recommended && 1 === $hide_zero )
				? '<span class="irecommendthis-count" style="display: none;">0</span> <span class="irecommendthis-suffix">' . esc_html( $suffix ) . '</span>'
				: '<span class="irecommendthis-count">' . esc_html( $recommended ) . '</span> <span class="irecommendthis-suffix">' . esc_html( $suffix ) . '</span>';

			return apply_filters( 'irecommendthis_before_count', $output );
		}

		// Handling the 'update' action.
		if ( 'update' === $action ) {
			global $wpdb;

			// Process unique IP address checking if enabled.
			if ( 0 !== $enable_unique_ip ) {
				$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
				$anonymized_ip = self::anonymize_ip( $ip );

				if ( isset( $_POST['unrecommend'] ) && 'true' === sanitize_text_field( wp_unslash( $_POST['unrecommend'] ) ) ) {
					// Delete the vote record for this IP and post
					$sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $anonymized_ip );
					$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				} else {
					// Check if user has already voted
					$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $anonymized_ip );
					$vote_status_by_ip = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

					// Only insert if no vote exists for this IP and post
					if ( empty( $vote_status_by_ip ) || $vote_status_by_ip == 0 ) {
						$sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}irecommendthis_votes VALUES ('', NOW(), %d, %s )", $post_id, $anonymized_ip );
						$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					}
				}
			}

			// Check for cookie
			$cookie_exists = isset( $_COOKIE[ 'irecommendthis_' . $post_id ] );

			// Handle the case where the user is un-recommending.
			if ( $cookie_exists && isset( $_POST['unrecommend'] ) && 'true' === sanitize_text_field( wp_unslash( $_POST['unrecommend'] ) ) ) {
				// Remove cookie for unrecommend action
				setcookie( 'irecommendthis_' . $post_id, '', time() - 3600, '/' );

				// Decrement the count
				$recommended = max( 0, $recommended - 1 );
			}
			// Handle the case where the user is recommending.
			else if ( isset( $_POST['unrecommend'] ) && 'false' === sanitize_text_field( wp_unslash( $_POST['unrecommend'] ) ) ) {
				// Set cookie for recommend action - set for 1 year
				setcookie( 'irecommendthis_' . $post_id, time(), time() + 31536000, '/' );

				// Increment the count
				++$recommended;
			}

			// Update the recommendation count.
			update_post_meta( $post_id, '_recommended', $recommended );

			// Output HTML for the recommendation button.
			$suffix = $get_suffix( $recommended );
			$output = ( 0 === $recommended && 1 === $hide_zero )
				? '<span class="irecommendthis-count" style="display: none;">0</span> <span class="irecommendthis-suffix">' . esc_html( $suffix ) . '</span>'
				: '<span class="irecommendthis-count">' . esc_html( $recommended ) . '</span> <span class="irecommendthis-suffix">' . esc_html( $suffix ) . '</span>';

			return apply_filters( 'irecommendthis_before_count', $output );
		}
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
		// Empty IPs should return a consistent hash
		if ( empty( $ip ) ) {
			$ip = 'unknown';
		}

		// Use WordPress salt for authentication
		$auth_salt = wp_salt( 'auth' );

		// Use site-specific hash for additional entropy
		$site_hash = defined( 'COOKIEHASH' ) ? COOKIEHASH : md5( site_url() );

		// Create the hash using WordPress hash function with site context
		$hashed_ip = wp_hash( $ip . $site_hash, 'auth' );

		return $hashed_ip;
	}
}
