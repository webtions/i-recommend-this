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
				'postId'          => array(
					'type'    => 'number',
					'default' => null,
				),
				'alignText'       => array(
					'type'    => 'string',
					'default' => 'left',
				),
				'useCurrentPost'  => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'isEditorPreview' => array(
					'type'    => 'boolean',
					'default' => false,
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
 * Override post meta for the recommendation count in editor context.
 *
 * @param mixed  $value     The value to return.
 * @param int    $object_id The object ID.
 * @param string $meta_key  The meta key.
 * @param bool   $_single   Whether to return a single value (required by filter but unused).
 * @return mixed The filtered meta value.
 */
function irecommendthis_force_zero_count_in_editor( $value, $object_id, $meta_key, $_single ) {
	// Only modify the _recommended meta key.
	if ( '_recommended' !== $meta_key ) {
		return $value;
	}

	// Return 0 to force zero count.
	return '0';
}

/**
 * Check if we're in the editor context through REST API.
 *
 * @return bool Whether we're in the editor context.
 */
function irecommendthis_is_editor_context() {
	// Check if this is a REST request for the block editor.
	if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) {
		return false;
	}

	// Safely check for context parameter.
	$context        = isset( $_GET['context'] ) ? sanitize_key( $_GET['context'] ) : '';
	$nonce_verified = false;

	// Verify nonce if available.
	if ( isset( $_REQUEST['_wpnonce'] ) ) {
		$nonce_verified = wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp_rest' );
	}

	// Either verify nonce or allow in specific contexts for backward compatibility.
	return ( $nonce_verified || 'edit' === $context );
}

/**
 * Render callback for the recommendation block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $_content   Block content (unused but required by render callback).
 * @param WP_Block $block      Block instance.
 * @return string Rendered block output.
 */
function irecommendthis_block_render_callback( $attributes, $_content, $block ) {
	// Get the post ID based on context.
	$post_id = irecommendthis_determine_post_id( $attributes, $block );

	// If we still don't have a valid post ID, provide an informative message.
	if ( ! $post_id ) {
		return '<div class="irecommendthis-context-notice" style="padding: 12px; background-color: #f8f9fa; border-left: 4px solid #ffb900; margin: 10px 0;">
			<p style="margin: 0; padding: 0;"><strong>' . esc_html__( 'I Recommend This', 'i-recommend-this' ) . ':</strong> ' .
			esc_html__( 'Unable to determine which post to recommend. This block works best within a post or page context.', 'i-recommend-this' ) . '</p>
		</div>';
	}

	/**
	 * Filter the post ID to be used for the recommendation block.
	 *
	 * @since 4.0.0
	 * @param int    $post_id    The post ID determined.
	 * @param array  $attributes Block attributes.
	 * @param object $block      The block instance.
	 */
	$post_id = apply_filters( 'irecommendthis_block_post_id', $post_id, $attributes, $block );

	// Check if this is an editor preview.
	$is_editor_preview = isset( $attributes['isEditorPreview'] ) && $attributes['isEditorPreview'];

	// Check if we're in the editor context.
	$is_editor = irecommendthis_is_editor_context();

	// Prepare CSS classes.
	$classes = irecommendthis_get_block_classes( $attributes, $block, $is_editor_preview );

	// Generate button with appropriate method.
	$button = irecommendthis_generate_button( $post_id, $is_editor );

	// Build block attributes.
	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class'        => $classes,
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

/**
 * Determine the post ID based on attributes and context.
 *
 * @param array    $attributes Block attributes.
 * @param WP_Block $block      Block instance.
 * @return int|null Post ID or null if not found.
 */
function irecommendthis_determine_post_id( $attributes, $block ) {
	// FIRST PRIORITY: Check if user explicitly set a custom post ID.
	if ( isset( $attributes['useCurrentPost'] ) &&
		false === $attributes['useCurrentPost'] &&
		isset( $attributes['postId'] ) &&
		is_numeric( $attributes['postId'] )
	) {
		// User has explicitly turned off "use current post" and provided a post ID.
		return absint( $attributes['postId'] );
	}

	// SECOND PRIORITY: If set to use current post, use context or current post.
	if ( isset( $attributes['useCurrentPost'] ) && true === $attributes['useCurrentPost'] ) {
		// Try to get post ID from block context (for query loops).
		if ( isset( $block->context['postId'] ) ) {
			return absint( $block->context['postId'] );
		}

		// Or use the current post ID from the main query.
		$current_post_id = get_the_ID();
		if ( $current_post_id ) {
			return $current_post_id;
		}
	}

	// FALLBACK: If we somehow still don't have a post ID, try to use postId attribute.
	if ( isset( $attributes['postId'] ) && is_numeric( $attributes['postId'] ) ) {
		return absint( $attributes['postId'] );
	}

	// Last resort: get current post ID.
	return get_the_ID() ? get_the_ID() : 0;
}

/**
 * Get combined CSS classes for the block.
 *
 * @param array    $attributes        Block attributes.
 * @param WP_Block $block             Block instance.
 * @param bool     $is_editor_preview Whether this is an editor preview.
 * @return string Combined CSS classes.
 */
function irecommendthis_get_block_classes( $attributes, $block, $is_editor_preview ) {
	// Base classes.
	$classes = array( 'irecommendthis-wrapper' );

	// Add alignment class.
	$align_class = isset( $attributes['alignText'] ) ? "has-text-align-{$attributes['alignText']}" : 'has-text-align-left';
	$classes[]   = $align_class;

	// Add block-specific class.
	if ( isset( $block->block_type ) && isset( $block->block_type->name ) ) {
		$classes[] = 'wp-block-' . str_replace( '/', '-', $block->block_type->name );
	}

	// Add editor class if preview.
	if ( $is_editor_preview ) {
		$classes[] = 'editor-preview';
	}

	return implode( ' ', array_filter( $classes ) );
}

/**
 * Generate the recommendation button HTML.
 *
 * @param int  $post_id   The post ID.
 * @param bool $is_editor Whether in editor context.
 * @return string Button HTML.
 */
function irecommendthis_generate_button( $post_id, $is_editor ) {
	// We'll provide our own wrapper in the block.
	$wrapper = false;
	$button  = '';

	// Special handling for editor context.
	if ( $is_editor && class_exists( 'Themeist_IRecommendThis_Public_Processor' ) ) {
		// Add filter to force a zero count in the editor.
		add_filter( 'get_post_metadata', 'irecommendthis_force_zero_count_in_editor', 10, 4 );

		$button = irecommendthis_render_button( $post_id, $wrapper );

		// Remove the filter to avoid affecting other parts of the page.
		remove_filter( 'get_post_metadata', 'irecommendthis_force_zero_count_in_editor', 10 );
	} else {
		// Normal rendering for frontend.
		$button = irecommendthis_render_button( $post_id, $wrapper );
	}

	return $button;
}

/**
 * Render the recommendation button with appropriate method.
 *
 * @param int  $post_id The post ID.
 * @param bool $wrapper Whether to include wrapper.
 * @return string Button HTML.
 */
function irecommendthis_render_button( $post_id, $wrapper ) {
	if ( class_exists( 'Themeist_IRecommendThis_Shortcodes' ) &&
		method_exists( 'Themeist_IRecommendThis_Shortcodes', 'recommend' )
	) {
		return Themeist_IRecommendThis_Shortcodes::recommend( $post_id, 'get', $wrapper );
	} elseif ( function_exists( 'do_shortcode' ) ) {
		// Fallback to shortcode if the class method isn't available.
		return do_shortcode( '[irecommendthis id="' . esc_attr( $post_id ) . '" wrapper="false"]' );
	}

	return '';
}
