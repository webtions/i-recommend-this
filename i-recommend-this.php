<?php
/**
 * Plugin Name: I Recommend This
 * Plugin URI: https://themeist.com/plugins/wordpress/i-recommend-this/#utm_source=wp-plugin&utm_medium=i-recommend-this&utm_campaign=plugins-page
 * Description: This plugin allows your visitors to recommend or like your posts.
 * Version: 3.9.1
 * Author: Harish Chouhan, Themeist
 * Author URI: https://themeist.com/
 * Author Email: support@themeist.com
 * Text Domain: i-recommend-this
 * License: GPL-3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Do nothing if class is already defined.
if ( class_exists( 'Themeist_IRecommendThis' ) ) {
	return;
}

define( 'THEMEIST_IRT_VERSION', '3.9.1' );
define( 'THEMEIST_IRT_DB_VERSION', '2.6.2' );

// Require includes.
require_once __DIR__ . '/includes/class-themeist-irecommendthis.php';
require_once __DIR__ . '/admin/class-themeist-irecommendthis-admin.php';
require_once __DIR__ . '/public/class-themeist-irecommendthis-public.php';
require_once __DIR__ . '/public/class-themeist-most-recommended-posts-widget.php';
require_once __DIR__ . '/includes/class-themeist-irecommendthis-ajax.php';
require_once __DIR__ . '/includes/class-themeist-irecommendthis-shortcodes.php';
require_once __DIR__ . '/includes/functions.php';

// Create instance of plugin class.
global $themeist_i_recommend_this;
$themeist_i_recommend_this = new Themeist_IRecommendThis( __FILE__ );
$themeist_i_recommend_this->add_hooks();

// Create instance of admin class.
global $themeist_i_recommend_this_admin;
$themeist_i_recommend_this_admin = new Themeist_IRecommendThis_Admin( __FILE__ );
$themeist_i_recommend_this_admin->add_admin_hooks();

// Create instance of public class.
global $themeist_i_recommend_this_public;
$themeist_i_recommend_this_public = new Themeist_IRecommendThis_Public( __FILE__ );
$themeist_i_recommend_this_public->add_public_hooks();

// Create instance of AJAX class and add hooks.
global $themeist_i_recommend_this_ajax;
$themeist_i_recommend_this_ajax = new Themeist_IRecommendThis_Ajax();
$themeist_i_recommend_this_ajax->add_ajax_hooks();

// Register shortcodes.
//Themeist_IRecommendThis_Shortcodes::register_shortcodes();

add_action( 'init', 'themeist_register_shortcodes' );

function themeist_register_shortcodes() {
	Themeist_IRecommendThis_Shortcodes::register_shortcodes();
}

/**
 * Enqueue block editor assets.
 */
function themeist_dot_recommends_block_editor_assets() {
	$current_screen = get_current_screen();
	if ( $current_screen && ! in_array( $current_screen->base, array( 'widgets', 'customize' ), true ) ) {
		wp_enqueue_script(
			'themeist-dot-recommends-block-editor',
			plugins_url( 'build/index.js', __FILE__ ),
			array(
				'wp-blocks',
				'wp-element',
				'wp-editor',
				'wp-components',
				'wp-i18n',
			),
			filemtime( plugin_dir_path( __FILE__ ) . 'build/index.js' ),
			true
			// Load in footer.
		);
	} else {
		wp_enqueue_script(
			'themeist-dot-recommends-block-editor',
			plugins_url( 'build/index.js', __FILE__ ),
			array(
				'wp-blocks',
				'wp-element',
				'wp-components',
				'wp-i18n',
			),
			filemtime( plugin_dir_path( __FILE__ ) . 'build/index.js' ),
			true
			// Load in footer.
		);
	}//end if

	wp_enqueue_style(
		'themeist-dot-recommends-block-editor',
		plugins_url( 'build/index.css', __FILE__ ),
		array( 'wp-edit-blocks' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'build/index.css' )
	);
}
add_action( 'enqueue_block_editor_assets', 'themeist_dot_recommends_block_editor_assets' );

/**
 * Enqueue frontend assets for 'dot-recommends' block.
 */
function themeist_dot_recommends_block_assets() {
	wp_enqueue_style(
		'themeist-dot-recommends-block',
		plugins_url( 'build/style-index.css', __FILE__ ),
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'build/style-index.css' )
	);
}
add_action( 'enqueue_block_assets', 'themeist_dot_recommends_block_assets' );

/**
 * Render callback for 'dot-recommends' block.
 *
 * @param array $attributes Block attributes.
 * @return string HTML output for the block.
 */
function render_dot_recommends_block( $attributes ) {
	$post_id = isset( $attributes['postId'] ) ? intval( $attributes['postId'] ) : get_the_ID();
	return dot_irecommendthis( $post_id );
}

add_action(
	'init',
	function () {
		register_block_type(
			'themeist/dot-recommends',
			array(
				'api_version'     => 2,
				'render_callback' => 'render_dot_recommends_block',
			)
		);
	}
);
