<?php
/**
 * Functions for I Recommend This plugin
 *
 * @package IRecommendThis
 */

if ( ! function_exists( 'themeist_i_recommend_this_public' ) ) {
	/**
	 * Output the public view of the plugin.
	 *
	 * This function outputs the value of the global variable
	 * $themeist_i_recommend_this_public, which is expected to
	 * be defined and populated elsewhere in the plugin.
	 */
	function themeist_i_recommend_this_public() {
		global $themeist_i_recommend_this_public;

		// Escape the output to allow safe HTML rendering.
		echo wp_kses_post( $themeist_i_recommend_this_public );
	}
}
