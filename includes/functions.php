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
 * @param int|null $id The post ID. If null, the current post ID is used.
 * @param bool     $should_echo Whether to echo the output or return it.
 * @return string|void The recommendation button HTML if $should_echo is false.
 */
function irecommendthis( $id = null, $should_echo = true ) {
	// Sanitize the post ID.
	$id = ( null === $id ) ? get_the_ID() : absint( $id );

	// Ensure $should_echo is a boolean.
	$should_echo = (bool) $should_echo;

	// Get the output from the shortcode.
	$output = Themeist_IRecommendThis_Shortcodes::recommend( $id, 'get' );

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
 * @param int|null $id The post ID. If null, the current post ID is used.
 * @param bool     $should_echo Whether to echo the output or return it.
 * @return string|void The recommendation button HTML if $should_echo is false.
 */
function dot_irecommendthis( $id = null, $should_echo = true ) {
	// Trigger deprecation notice in debug mode
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		trigger_error(
			sprintf(
				/* translators: 1: Deprecated function name, 2: Version number, 3: Alternative function name */
				esc_html__( 'Function %1$s is deprecated since version %2$s! Use %3$s instead.', 'i-recommend-this' ),
				'dot_irecommendthis()',
				'4.0.0',
				'irecommendthis()'
			),
			E_USER_DEPRECATED
		);
	}

	return irecommendthis( $id, $should_echo );
}
