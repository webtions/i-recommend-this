<?php
/**
 * Display component for public-facing functionality.
 *
 * Handles content modification for displaying the recommendation button.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle public-facing display.
 */
class Themeist_IRecommendThis_Public_Display {

	/**
	 * Initialize the component.
	 */
	public function initialize() {
		add_filter( 'the_content', array( $this, 'modify_content' ) );
	}

	/**
	 * Modify content to append recommendation button.
	 *
	 * @param string $content The original post content.
	 * @return string Modified post content with recommendation button.
	 */
	public function modify_content( $content ) {
		// Skip adding button on specific page types.
		if ( is_page_template() || is_page() || is_front_page() ) {
			return $content;
		}

		global $wp_current_filter;
		if ( in_array( 'get_the_excerpt', (array) $wp_current_filter, true ) ) {
			return $content;
		}

		// Get plugin settings.
		$options = get_option( 'irecommendthis_settings' );

		// Support both new and old setting keys.
		$add_to_posts = isset( $options['add_to_posts'] ) ? $options['add_to_posts'] : '0';
		$add_to_other = isset( $options['add_to_other'] ) ? $options['add_to_other'] : '0';

		// Append recommendation button to singular posts.
		// Pass wrapper=false since we're adding the wrapper here.
		if ( is_singular( 'post' ) && $add_to_posts ) {
			$content .= '<div class="irecommendthis-wrapper">' . Themeist_IRecommendThis_Shortcodes::recommend( null, 'get', false ) . '</div>';
		}

		// Append recommendation button to other post archive pages.
		// Pass wrapper=false since we're adding the wrapper here.
		if ( ( is_home() || is_category() || is_tag() || is_author() || is_date() || is_search() ) && $add_to_other ) {
			$content .= '<div class="irecommendthis-wrapper">' . Themeist_IRecommendThis_Shortcodes::recommend( null, 'get', false ) . '</div>';
		}

		return $content;
	}
}
