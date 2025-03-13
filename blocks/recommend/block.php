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
				'inserterScope'   => 'block-editor', // Only show in post editor, not in widget screen.
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

	// Check if this is an editor preview.
	$is_editor_preview = isset( $attributes['isEditorPreview'] ) && $attributes['isEditorPreview'];

	// Check if we're in a widget context or sidebar
	$is_in_widget_context = irecommendthis_is_in_widget_context( $block );

	// If we're in a widget context, display a friendly message
	if ( $is_in_widget_context ) {
		return irecommendthis_get_widget_context_message();
	}

	// 1. Check if we're in a query loop with a block context.
	if ( isset( $block->context['postId'] ) ) {
		$post_id = absint( $block->context['postId'] );
	} elseif (
		isset( $attributes['useCurrentPost'] ) &&
		false === $attributes['useCurrentPost'] &&
		isset( $attributes['postId'] ) &&
		is_numeric( $attributes['postId'] )
	) {
		// 2. If block is explicitly configured to use a specific post ID.
		$post_id = absint( $attributes['postId'] );
	} else {
		// 3. Otherwise, get the current post ID from the main query.
		$post_id = get_the_ID();
	}

	// Fallback if somehow we still don't have a valid post ID.
	if ( ! $post_id ) {
		// Try to get an ID from any available source as a last resort.
		$post_id = isset( $attributes['postId'] ) ? absint( $attributes['postId'] ) : 0;
		$post_id = $post_id ? $post_id : get_the_ID();
	}

	// If we still don't have a valid post ID, provide an informative message
	if ( ! $post_id ) {
		return irecommendthis_get_no_post_context_message();
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

	// Get the alignment class.
	$align_class = isset( $attributes['alignText'] ) ? "has-text-align-{$attributes['alignText']}" : 'has-text-align-left';

	// Get additional block classes.
	$block_class = '';
	if ( isset( $block->block_type ) && isset( $block->block_type->name ) ) {
		$block_class = 'wp-block-' . str_replace( '/', '-', $block->block_type->name );
	}

	// Class for editor preview.
	$editor_class = $is_editor_preview ? 'editor-preview' : '';

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
			'class'        => "irecommendthis-wrapper {$align_class} {$block_class} {$editor_class}",
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
 * Detect if the block is being rendered in a widget context.
 *
 * @param WP_Block $block The block instance.
 * @return boolean Whether the block is in a widget context.
 */
function irecommendthis_is_in_widget_context( $block ) {
	// Check block ancestors for widget blocks
	if ( isset( $block->context['widget'] ) ) {
		return true;
	}

	// Check if we're in the widgets admin screen
	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		if ( $screen && 'widgets' === $screen->id ) {
			return true;
		}
	}

	// Check if the block has widget area as parent
	if ( isset( $block->context['postId'] ) && 0 === $block->context['postId'] ) {
		return true;
	}

	// Check if this is the widget editing interface
	if ( doing_action( 'enqueue_block_editor_assets' ) && isset( $_GET['page'] ) && 'gutenberg-widgets' === $_GET['page'] ) {
		return true;
	}

	// Check if we're in a sidebar
	global $wp_registered_sidebars;
	if ( doing_action( 'dynamic_sidebar' ) || doing_action( 'dynamic_sidebar_before' ) || doing_action( 'dynamic_sidebar_after' ) ) {
		return true;
	}

	// Check if we're on a page with no post context
	if ( ! is_singular() && ! get_the_ID() && ! isset( $block->context['postId'] ) ) {
		return true;
	}

	return false;
}

/**
 * Get a friendly message for widget contexts.
 *
 * @return string HTML message for widget contexts.
 */
function irecommendthis_get_widget_context_message() {
	$message = '<div class="irecommendthis-widget-notice" style="padding: 12px; background-color: #f8f9fa; border-left: 4px solid #007cba; margin: 10px 0;">';
	$message .= '<p style="margin: 0; padding: 0;"><strong>' . esc_html__( 'I Recommend This', 'i-recommend-this' ) . ':</strong> ';
	$message .= esc_html__( 'This block is designed to work within post content. For widgets, consider using the "Most Recommended Posts" widget instead.', 'i-recommend-this' );
	$message .= '</p>';
	$message .= '</div>';

	return $message;
}

/**
 * Get a message for when there is no post context.
 *
 * @return string HTML message for no post context.
 */
function irecommendthis_get_no_post_context_message() {
	$message = '<div class="irecommendthis-context-notice" style="padding: 12px; background-color: #f8f9fa; border-left: 4px solid #ffb900; margin: 10px 0;">';
	$message .= '<p style="margin: 0; padding: 0;"><strong>' . esc_html__( 'I Recommend This', 'i-recommend-this' ) . ':</strong> ';
	$message .= esc_html__( 'Unable to determine which post to recommend. This block works best within a post or page context.', 'i-recommend-this' );
	$message .= '</p>';
	$message .= '</div>';

	return $message;
}
