<?php
/**
 * Block registration manager for I Recommend This plugin.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Include individual block files.
require_once __DIR__ . '/recommend/block.php';

/**
 * Register all blocks for the plugin.
 */
function irecommendthis_register_blocks() {
	// Register the recommendation block.
	irecommendthis_register_recommend_block();
}

/**
 * Enqueue block editor assets for our blocks.
 */
function irecommendthis_enqueue_editor_assets() {
	// Only enqueue if block editor is loading.
	if ( ! wp_script_is( 'wp-blocks', 'enqueued' ) ) {
		return;
	}

	// Get plugin settings.
	$options         = get_option( 'irecommendthis_settings', array() );
	$disable_css     = isset( $options['disable_css'] ) ? intval( $options['disable_css'] ) : 0;
	$recommend_style = isset( $options['recommend_style'] ) ? intval( $options['recommend_style'] ) : 0;

	// If CSS is not disabled, enqueue the appropriate style.
	if ( 0 === $disable_css ) {
		$css_file = ( 0 === $recommend_style ) ? 'irecommendthis.css' : 'irecommendthis-heart.css';

		// Use the confirmed working path - going up from blocks directory to reach assets.
		$css_url = plugins_url( '../assets/css/' . $css_file, __FILE__ );

		// Add a version parameter to prevent caching issues.
		$version = defined( 'THEMEIST_IRT_VERSION' ) ? THEMEIST_IRT_VERSION : '4.0.0';
		$css_url = add_query_arg( 'ver', $version . '-' . time(), $css_url );

		// Register and enqueue the editor style.
		wp_register_style(
			'irecommendthis-editor-style',
			$css_url,
			array(),
			$version
		);
		wp_enqueue_style( 'irecommendthis-editor-style' );

		// Add editor-specific adjustments.
		wp_add_inline_style(
			'irecommendthis-editor-style',
			'
			/* Editor-specific adjustments */
			.editor-preview .irecommendthis {
				pointer-events: none; /* Disable clicks in editor */
			}
			.block-editor-block-list__block .irecommendthis-wrapper {
				margin: 12px 0;
			}
			'
		);
	}//end if
}

// Hook into WordPress init to register blocks.
add_action( 'init', 'irecommendthis_register_blocks' );

// Add the action hook for editor assets - use higher priority to ensure it runs after core assets.
add_action( 'enqueue_block_editor_assets', 'irecommendthis_enqueue_editor_assets', 20 );

// Also hook into admin_enqueue_scripts for more reliable loading in admin.
add_action( 'admin_enqueue_scripts', 'irecommendthis_enqueue_editor_assets' );
