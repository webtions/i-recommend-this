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
	public function process_recommendation( $post_id, $text_zero_suffix = false, $text_one_suffix = false, $text_more_suffix = false, $action = 'get' ) {
		// Validate post ID.
		if ( ! is_numeric( $post_id ) ) {
			return;
		}

		// Sanitize suffix inputs.
		$text_zero_suffix = sanitize_text_field( $text_zero_suffix );
		$text_one_suffix  = sanitize_text_field( $text_one_suffix );
		$text_more_suffix = sanitize_text_field( $text_more_suffix );

		// Fetch options and recommendation count.
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );
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

			return apply_filters( 'irecommendthis_before_count', apply_filters( 'dot_irt_before_count', $output ) );
		}

		// Handling the 'update' action.
		if ( 'update' === $action ) {
			return $this->update_recommendation( $post_id, $recommended, $hide_zero, $enable_unique_ip, $get_suffix );
		}
	}

	/**
	 * Update a recommendation for a post.
	 *
	 * @param int      $post_id          Post ID.
	 * @param int      $recommended      Current recommendation count.
	 * @param int      $hide_zero        Whether to hide zero count.
	 * @param int      $enable_unique_ip Whether to enable IP tracking.
	 * @param callable $get_suffix       Function to get suffix text.
	 * @return string HTML output for the recommendation count.
	 */
	private function update_recommendation( $post_id, $recommended, $hide_zero, $enable_unique_ip, $get_suffix ) {
		global $wpdb;

		// Process unique IP address checking if enabled.
		if ( 0 !== $enable_unique_ip ) {
			$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

			if ( isset( $_POST['unrecommend'] ) && isset( $_POST['security'] ) &&
				( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'irecommendthis-nonce' ) ||
				  wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'dot-irecommendthis-nonce' ) ) &&
				'true' === sanitize_text_field( wp_unslash( $_POST['unrecommend'] ) ) ) {
				$sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $ip );
				$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			} else {
				$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $ip );
				$vote_status_by_ip = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}irecommendthis_votes VALUES ('', NOW(), %d, %s )", $post_id, $ip );
				$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			}
		}

		// Use the improved cookie handling function
		$this->set_recommendation_cookie($post_id, isset($_POST['unrecommend']) && 'true' === sanitize_text_field(wp_unslash($_POST['unrecommend'])));

		// Check both old and new cookie names for backward compatibility
		$cookie_exists = isset( $_COOKIE[ 'irecommendthis_' . $post_id ] ) || isset( $_COOKIE[ 'dot_irecommendthis_' . $post_id ] );

		// Handle the case where the user is un-recommending.
		if ( $cookie_exists && isset( $_POST['security'] ) &&
			( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'irecommendthis-nonce' ) ||
			  wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'dot-irecommendthis-nonce' ) ) &&
			isset( $_POST['unrecommend'] ) && 'true' === sanitize_text_field( wp_unslash( $_POST['unrecommend'] ) ) ) {
			// Count already decremented by cookie function
			$recommended = max( 0, $recommended - 1 );
		}
		// Handle the case where the user is recommending.
		else if ( isset( $_POST['security'] ) &&
				( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'irecommendthis-nonce' ) ||
				  wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'dot-irecommendthis-nonce' ) ) &&
				isset( $_POST['unrecommend'] ) && 'false' === sanitize_text_field( wp_unslash( $_POST['unrecommend'] ) ) ) {
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

		return apply_filters( 'irecommendthis_before_count', apply_filters( 'dot_irt_before_count', $output ) );
	}

	/**
	 * Set a recommendation cookie with proper security and compatibility.
	 *
	 * @param int  $post_id The post ID.
	 * @param bool $remove  Whether to remove the cookie.
	 * @return bool Whether the operation was successful.
	 */
	private function set_recommendation_cookie( $post_id, $remove = false ) {
		// Use proper cookie security settings
		$secure = is_ssl();
		$http_only = true;
		$post_id = absint( $post_id );

		// Check if headers are already sent
		if ( headers_sent() ) {
			return false;
		}

		if ( $remove ) {
			setcookie( 'irecommendthis_' . $post_id, '', time() - HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, $secure, $http_only );
			// For backward compatibility
			setcookie( 'dot_irecommendthis_' . $post_id, '', time() - HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, $secure, $http_only );
			return true;
		}

		// Use WordPress constant for expiration (1 year)
		$expiration = time() + YEAR_IN_SECONDS;
		setcookie( 'irecommendthis_' . $post_id, $expiration, $expiration, COOKIEPATH, COOKIE_DOMAIN, $secure, $http_only );

		// For backward compatibility
		setcookie( 'dot_irecommendthis_' . $post_id, $expiration, $expiration, COOKIEPATH, COOKIE_DOMAIN, $secure, $http_only );
		return true;
	}
}
