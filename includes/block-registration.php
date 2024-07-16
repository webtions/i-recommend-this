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
	register_block_type( __DIR__ . '/../build' );
}
add_action( 'init', 'register_irecommendthis_block' );
