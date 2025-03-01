<?php
/**
 * Template for the general settings tab.
 *
 * @package IRecommendThis
 */

// Prevent direct access.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<form method="post" action="options.php" class="irecommendthis-settings-form">
	<?php settings_fields( 'irecommendthis-settings' ); ?>
	<?php do_settings_sections( 'irecommendthis-settings' ); ?>
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'i-recommend-this' ); ?>"/>
	</p>
</form>
