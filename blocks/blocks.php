<?php
/**
 * Block registration manager for I Recommend This plugin.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Include individual block files
require_once __DIR__ . '/recommend/block.php';
// require_once __DIR__ . '/top-posts/block.php';  // Future block

/**
 * Register all blocks for the plugin.
 */
function irecommendthis_register_blocks() {
	// Register the recommendation block
	irecommendthis_register_recommend_block();
	// irecommendthis_register_top_posts_block();  // Future block
}

// Hook into WordPress init to register blocks
add_action( 'init', 'irecommendthis_register_blocks' );
