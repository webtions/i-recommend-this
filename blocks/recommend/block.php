<?php
/**
 * Recommendation block functionality.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register the recommendation block.
 */
function irecommendthis_register_recommend_block() {
	register_block_type(
		__DIR__ . '/build',
		array(
			'render_callback' => 'irecommendthis_block_render_callback',
			'attributes'      => array(
				'postId'         => array(
					'type'    => 'number',
					'default' => null,
				),
				'alignText'      => array(
					'type'    => 'string',
					'default' => 'left',
				),
				'useCurrentPost' => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
			'supports'        => array(
				'html'            => false,
				'align'           => true,
				'alignWide'       => false,
				'customClassName' => true,
			),
		)
	);
}

/**
 * Render callback for the recommendation block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 * @return string Rendered block output.
 */
function irecommendthis_block_render_callback( $attributes, $content, $block ) {
	// Default post ID to work with - this will be refined for query loops and context.
	$post_id = null;

	// Track the source of the post ID for debug purposes.
	$id_source = '';

	// 1. Check if we're in a query loop with a block context.
	if ( isset( $block->context['postId'] ) ) {
		$post_id   = absint( $block->context['postId'] );
		$id_source = 'block_context';
	} elseif (
		isset( $attributes['useCurrentPost'] ) &&
		false === $attributes['useCurrentPost'] &&
		isset( $attributes['postId'] ) &&
		is_numeric( $attributes['postId'] )
	) {
		// 2. If block is explicitly configured to use a specific post ID.
		$post_id   = absint( $attributes['postId'] );
		$id_source = 'specific_post_setting';
	} else {
		// 3. Otherwise, get the current post ID from the main query.
		$post_id   = get_the_ID();
		$id_source = 'current_post';
	}

	// Fallback if somehow we still don't have a valid post ID.
	if ( ! $post_id ) {
		// Try to get an ID from any available source as a last resort.
		$post_id   = isset( $attributes['postId'] ) ? absint( $attributes['postId'] ) : 0;
		$post_id   = $post_id ? $post_id : get_the_ID();
		$id_source = 'fallback';
	}

	/**
	 * Filter the post ID to be used for the recommendation block.
	 *
	 * @since 4.0.0
	 * @param int    $post_id    The post ID determined.
	 * @param array  $attributes Block attributes.
	 * @param string $id_source  Source of the post ID ('block_context', 'specific_post_setting', 'current_post', 'fallback').
	 * @param object $block      The block instance.
	 */
	$post_id = apply_filters( 'irecommendthis_block_post_id', $post_id, $attributes, $id_source, $block );

	// Get the alignment class.
	$align_class = isset( $attributes['alignText'] ) ? "has-text-align-{$attributes['alignText']}" : 'has-text-align-left';

	// Get additional block classes.
	$block_class = '';
	if ( isset( $block->block_type ) && isset( $block->block_type->name ) ) {
		$block_class = 'wp-block-' . str_replace( '/', '-', $block->block_type->name );
	}

	// Allow the wrapper parameter to be controlled. We're providing our own wrapper in the block.
	$wrapper = false;

	// Generate the HTML for the recommendation button.
	$button = '';
	if ( class_exists( 'Themeist_IRecommendThis_Shortcodes' ) &&
		method_exists( 'Themeist_IRecommendThis_Shortcodes', 'recommend' )
	) {
		$button = Themeist_IRecommendThis_Shortcodes::recommend( $post_id, 'get', $wrapper );
	} else {
		// Fallback to shortcode if the class method isn't available.
		$button = do_shortcode( '[irecommendthis id="' . esc_attr( $post_id ) . '" wrapper="false"]' );
	}

	// Build block attributes.
	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class'        => "irecommendthis-wrapper {$align_class} {$block_class}",
			'data-post-id' => $post_id,
		)
	);

	// Output the block.
	return sprintf(
		'<div %1$s>%2$s</div>',
		$wrapper_attributes,
		$button
	);
}
