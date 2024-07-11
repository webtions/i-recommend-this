<?php
/**
 * Block registration and render callback for 'irecommendthis/recommend' block.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Render callback for 'irecommendthis/recommend' block.
 *
 * @param array $attributes Block attributes.
 * @param string $content The block content.
 * @param WP_Block $block The block instance.
 * @return string HTML output for the block.
 */
function render_irecommendthis_recommend_block( $attributes, $content, $block ) {
    $post_id = isset( $attributes['postId'] ) ? intval( $attributes['postId'] ) : get_the_ID();

    $shortcode_output = do_shortcode( '[irecommendthis id="' . $post_id . '"]' );

    // Get the block wrapper attributes, including custom class names.
    $wrapper_attributes = get_block_wrapper_attributes();

    return sprintf(
        '<div %1$s>%2$s</div>',
        $wrapper_attributes,
        $shortcode_output
    );
}

/**
 * Register the 'irecommendthis/recommend' block.
 */
function register_irecommendthis_recommend_block() {
    register_block_type(
        'irecommendthis/recommend',
        array(
            'api_version'     => 2,
            'attributes'      => array(
                'postId' => array(
                    'type'    => 'number',
                    'default' => null,
                ),
            ),
            'render_callback' => 'render_irecommendthis_recommend_block',
        )
    );
}
add_action( 'init', 'register_irecommendthis_recommend_block' );

/**
 * Enqueue block editor assets for 'irecommendthis/recommend' block.
 */
function irecommendthis_recommend_block_editor_assets() {
    wp_enqueue_script(
        'irecommendthis-recommend-block-editor',
        plugins_url( 'build/index.js', __DIR__ ),
        array(
            'wp-blocks',
            'wp-element',
            'wp-editor',
            'wp-components',
            'wp-i18n',
        ),
        filemtime( plugin_dir_path( __DIR__ ) . 'build/index.js' ),
        true
    );
}
add_action( 'enqueue_block_editor_assets', 'irecommendthis_recommend_block_editor_assets' );
