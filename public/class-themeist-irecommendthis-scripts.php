<?php

class Themeist_IRecommendThis_Scripts {

	/**
	 * Enqueue scripts and styles for the plugin.
	 */
	public static function enqueue_scripts( $plugin_file ) {
		$options = get_option( 'dot_irecommendthis_settings' );

		$disable_css = isset( $options['disable_css'] ) ? intval( $options['disable_css'] ) : 0;
		$recommend_style = isset( $options['recommend_style'] ) ? intval( $options['recommend_style'] ) : 0;

		if ( $disable_css === 0 ) {
			$css_file = ( $recommend_style === 0 ) ? 'dot-irecommendthis.css' : 'dot-irecommendthis-heart.css';
			wp_enqueue_style( 'dot-irecommendthis', plugins_url( '/css/' . $css_file, $plugin_file ) );
		}

		wp_register_script( 'dot-irecommendthis', plugins_url( '/js/dot_irecommendthis.js', $plugin_file ), array( 'jquery' ), '2.6.0', true );
		wp_enqueue_script( 'dot-irecommendthis' );

		if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery' );
		}

		$nonce = wp_create_nonce( 'dot-irecommendthis-nonce' );
		wp_localize_script(
			'dot-irecommendthis',
			'dot_irecommendthis',
			array(
				'nonce'   => $nonce,
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}
}
