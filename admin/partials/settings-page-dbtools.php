<?php
/**
 * Template for the database tools tab.
 *
 * @package IRecommendThis
 */

// Prevent direct access.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Display update success message if applicable
if ( isset( $_GET['updated'] ) && check_admin_referer( 'irecommendthis_update_success', 'updated_nonce' ) ) {
	if ( '1' === sanitize_text_field( wp_unslash( $_GET['updated'] ) ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' .
			esc_html__( 'Database has been updated successfully!', 'i-recommend-this' ) .
			'</p></div>';
	}
}
?>

<div class="card">
	<h2><?php esc_html_e( 'Database Optimization', 'i-recommend-this' ); ?></h2>
	<p><?php esc_html_e( 'This tool will optimize the database tables used by the I Recommend This plugin. It will add appropriate indexes and update the table structure for better performance.', 'i-recommend-this' ); ?></p>

	<form method="post" action="">
		<?php wp_nonce_field( 'irecommendthis_update_db', 'irecommendthis_db_nonce' ); ?>
		<input type="hidden" name="irecommendthis_action" value="update_db">
		<p>
			<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Optimize Database', 'i-recommend-this' ); ?>">
		</p>
	</form>
</div>

<div class="card">
	<h2><?php esc_html_e( 'Database Information', 'i-recommend-this' ); ?></h2>
	<?php $this->display_database_info(); ?>
</div>
