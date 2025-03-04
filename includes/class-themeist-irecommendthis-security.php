<?php
/**
 * Security functionality for the I Recommend This plugin.
 *
 * Provides a secure, reliable way to handle AJAX verification
 * while maintaining compatibility with caching plugins and other
 * WordPress features that might interfere with nonces.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle security for AJAX requests.
 */
class Themeist_IRecommendThis_Security {

	/**
	 * Generate a token for AJAX verification.
	 *
	 * This creates a standard WordPress nonce but also adds
	 * a site-specific hash component for additional verification.
	 *
	 * @return string The generated token.
	 */
	public static function generate_token() {
		// Generate a standard WordPress nonce
		$nonce = wp_create_nonce( 'irecommendthis-nonce' );

		// Get a site-specific salt for additional validation
		$site_key = defined( 'COOKIEHASH' ) ? COOKIEHASH : md5( site_url() );
		$site_key = substr( $site_key, 0, 6 );

		// Return the composite token
		return $nonce . '|' . $site_key;
	}

	/**
	 * Verify a request for AJAX operations.
	 *
	 * Uses a multi-layered approach to verification:
	 * 1. Standard nonce verification
	 * 2. Site-key verification
	 * 3. Rate limiting check
	 * 4. Referrer check as fallback
	 *
	 * @param string $token   The token to verify.
	 * @param int    $post_id The post ID being acted upon.
	 * @return bool Whether the request is valid.
	 */
	public static function verify_request( $token, $post_id ) {
		// If no token is provided, fall back to referrer check
		if ( empty( $token ) ) {
			return self::verify_referrer();
		}

		// Split the token into components
		$parts = explode( '|', $token );
		$nonce = isset( $parts[0] ) ? $parts[0] : '';
		$site_key = isset( $parts[1] ) ? $parts[1] : '';

		// Verify the site key component if provided
		if ( ! empty( $site_key ) ) {
			$valid_site_key = defined( 'COOKIEHASH' ) ? COOKIEHASH : md5( site_url() );
			$valid_site_key = substr( $valid_site_key, 0, 6 );

			if ( $site_key !== $valid_site_key ) {
				// Site key mismatch - fail unless we're in dev mode
				if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
					return false;
				}
			}
		}

		// Verify the standard WordPress nonce
		$valid_nonce = wp_verify_nonce( $nonce, 'irecommendthis-nonce' );

		// If the nonce is valid, allow the request
		if ( $valid_nonce ) {
			return true;
		}

		// If we're still here, the standard nonce failed
		// Fall back to rate limiting check
		return self::check_rate_limit( $post_id );
	}

	/**
	 * Check if the request comes from a valid referrer.
	 *
	 * This is a fallback verification method when nonces fail.
	 *
	 * @return bool Whether the referrer appears valid.
	 */
	private static function verify_referrer() {
		// Only apply this check in production
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return true;
		}

		// Check if referrer exists and comes from this site
		$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
		if ( empty( $referrer ) ) {
			return false;
		}

		$site_url = site_url();
		return strpos( $referrer, $site_url ) === 0;
	}

	/**
	 * Check rate limits to prevent abuse.
	 *
	 * This helps prevent abuse when traditional nonce verification fails.
	 *
	 * @param int $post_id The post ID being acted upon.
	 * @return bool Whether the rate limit allows this request.
	 */
	private static function check_rate_limit( $post_id ) {
		// Get IP address for rate limiting
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		// If no IP or no post ID, we can't rate limit properly
		if ( empty( $ip ) || empty( $post_id ) ) {
			return false;
		}

		// Create a transient name based on IP and post
		$transient_name = 'irt_rate_' . md5( $ip . '_' . $post_id );
		$limit_count = get_transient( $transient_name );

		// If no transient exists, create one and allow the request
		if ( false === $limit_count ) {
			set_transient( $transient_name, 1, MINUTE_IN_SECONDS * 5 ); // 5 minute window
			return true;
		}

		// If under rate limit, increment and allow
		if ( intval( $limit_count ) < 5 ) { // Max 5 actions per 5 minutes per post
			set_transient( $transient_name, intval( $limit_count ) + 1, MINUTE_IN_SECONDS * 5 );
			return true;
		}

		// Rate limit exceeded
		return false;
	}
}
