<?php
/**
 * Post Columns component for admin functionality.
 *
 * Handles the addition of custom columns to the posts list table
 * to display recommendation counts.
 *
 * @package IRecommendThis
 * @subpackage Admin
 * @since 4.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle the posts list table custom columns.
 */
class Themeist_IRecommendThis_Admin_Post_Columns {

	/**
	 * Initialize the component.
	 */
	public function initialize() {
		add_filter( 'manage_posts_columns', array( $this, 'add_column' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'display_column_content' ), 10, 2 );
		add_filter( 'manage_edit-post_sortable_columns', array( $this, 'make_column_sortable' ) );
		add_filter( 'request', array( $this, 'handle_column_sorting' ) );
	}

	/**
	 * Add the 'Likes' column to the posts list table.
	 *
	 * @param array $columns The existing columns.
	 * @return array The modified columns array.
	 */
	public function add_column( $columns ) {
		$columns['likes'] = __( 'Likes', 'i-recommend-this' );
		return $columns;
	}

	/**
	 * Display content in the 'Likes' column for each post.
	 *
	 * @param string $column_name The name of the column.
	 * @param int    $post_id     The post ID.
	 */
	public function display_column_content( $column_name, $post_id ) {
		if ( 'likes' === $column_name ) {
			$likes_count = get_post_meta( $post_id, '_recommended', true );
			$likes_count = empty( $likes_count ) ? '0' : $likes_count;
			echo esc_html( $likes_count ) . ' ' . esc_html__( 'like', 'i-recommend-this' );
		}
	}

	/**
	 * Make the 'Likes' column sortable.
	 *
	 * @param array $columns The existing sortable columns.
	 * @return array The modified sortable columns.
	 */
	public function make_column_sortable( $columns ) {
		$columns['likes'] = 'likes';
		return $columns;
	}

	/**
	 * Handle the sorting of posts by likes count.
	 *
	 * @param array $vars The query variables.
	 * @return array The modified query variables.
	 */
	public function handle_column_sorting( $vars ) {
		if ( isset( $vars['orderby'] ) && 'likes' === $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_recommended', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'orderby'  => 'meta_value_num',
				)
			);
		}

		return $vars;
	}
}
