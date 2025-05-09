<?php
/**
 * Public functions for the I Recommend This plugin.
 *
 * @package IRecommendThis
 */

/**
 * Outputs or returns the recommendation button for a post.
 *
 * This is the preferred function to use in theme templates.
 *
 * @param int|null $id          The post ID. If null, the current post ID is used.
 * @param bool     $should_echo Whether to echo the output or return it.
 * @param bool     $wrapper     Whether to wrap the output in a container div.
 * @return string|void The recommendation button HTML if $should_echo is false.
 */
function irecommendthis( $id = null, $should_echo = true, $wrapper = true ) {
	// Sanitize the post ID.
	$id = ( null === $id ) ? get_the_ID() : absint( $id );

	// Ensure $should_echo is a boolean.
	$should_echo = (bool) $should_echo;

	// Get the output from the shortcode.
	$output = Themeist_IRecommendThis_Shortcodes::recommend( $id, 'get', $wrapper );

	// Echo or return the output based on the $should_echo parameter.
	if ( $should_echo ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $output;
	} else {
		return $output;
	}
}

/**
 * Backward compatibility function for the old function name.
 *
 * @deprecated 4.0.0 Use irecommendthis() instead.
 *
 * @param int|null $id          The post ID. If null, the current post ID is used.
 * @param bool     $should_echo Whether to echo the output or return it.
 * @param bool     $wrapper     Whether to wrap the output in a container div.
 * @return string|void The recommendation button HTML if $should_echo is false.
 */
function dot_irecommendthis( $id = null, $should_echo = true, $wrapper = true ) {
	// Log deprecation message to debug.log instead of showing on frontend.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log(
			sprintf(
				'Function %1$s is deprecated since version %2$s! Use %3$s instead.',
				'dot_irecommendthis()',
				'4.0.0',
				'irecommendthis()'
			)
		);
	}
	return irecommendthis( $id, $should_echo, $wrapper );
}
