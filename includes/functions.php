<?php
/**
 * Outputs or returns the recommendation button for a post.
 *
 * @param int|null $id The post ID. If null, the current post ID is used.
 * @param bool     $should_echo Whether to echo the output or return it.
 */
function irecommendthis( $id = null, $should_echo = true ) {
	// Sanitize the post ID.
	$id = ( null === $id ) ? get_the_ID() : absint( $id );

	// Ensure $should_echo is a boolean.
	$should_echo = (bool) $should_echo;

	// Get the output from the shortcode.
	$output = Themeist_IRecommendThis_Shortcodes::dot_recommend( $id, 'get' );

	// Echo or return the output based on the $should_echo parameter.
	if ( $should_echo ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $output;
	} else {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return $output;
	}
}

/**
 * Backward compatibility for the old function name.
 *
 * @param int|null $id The post ID. If null, the current post ID is used.
 * @param bool     $should_echo Whether to echo the output or return it.
 */
function dot_irecommendthis( $id = null, $should_echo = true ) {
	return irecommendthis( $id, $should_echo );
}
