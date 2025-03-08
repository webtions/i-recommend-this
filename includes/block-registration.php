<?php
/**
 * Block registration for 'irecommendthis/recommend' block.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register the block.
 */
function register_irecommendthis_block() {
	register_block_type(
		__DIR__ . '/../build',
		array(
			'render_callback' => 'irecommendthis_block_render_callback',
		)
	);
}
add_action( 'init', 'register_irecommendthis_block' );

/**
 * Render callback for the block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 * @return string Rendered block output.
 */
function irecommendthis_block_render_callback( $attributes, $content, $block ) {
	// Default to current post ID if we're in a query loop or useCurrentPost is true.
	if (
		( isset( $attributes['useCurrentPost'] ) && $attributes['useCurrentPost'] ) ||
		( isset( $block->context['postId'] ) && ! isset( $attributes['postId'] ) )
	) {
		// Get post ID from block context if available (for query loops).
		$post_id = isset( $block->context['postId'] ) ? $block->context['postId'] : get_the_ID();
	} else {
		// Use the explicitly set post ID.
		$post_id = isset( $attributes['postId'] ) ? $attributes['postId'] : get_the_ID();
	}

	$align_class = isset( $attributes['alignText'] ) ? "has-text-align-{$attributes['alignText']}" : 'has-text-align-left';

	// Use the existing shortcode to render the content, but with wrapper=false since we'll add our own wrapper.
	$output  = '<div class="wp-block-irecommendthis-recommend irecommendthis-wrapper ' . esc_attr( $align_class ) . '">';
	$output .= do_shortcode( '[irecommendthis id="' . esc_attr( $post_id ) . '" wrapper="false"]' );
	$output .= '</div>';

	return $output;
}
