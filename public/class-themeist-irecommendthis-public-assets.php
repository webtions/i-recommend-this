<?php
/**
 * Assets component for public-facing functionality.
 *
 * Handles scripts and styles for the front-end.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle public-facing assets.
 */
class Themeist_IRecommendThis_Public_Assets {
	/**
	 * Path to the main plugin file.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_file Path to the main plugin file.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Initialize the component.
	 */
	public function initialize() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts and styles for the plugin.
	 */
	public function enqueue_scripts() {
		// Get plugin settings.
		$options = get_option( 'irecommendthis_settings' );

		// Validate and set default values for CSS options.
		$disable_css     = isset( $options['disable_css'] ) ? intval( $options['disable_css'] ) : 0;
		$recommend_style = isset( $options['recommend_style'] ) ? intval( $options['recommend_style'] ) : 0;

		// Enqueue styles if CSS is not disabled.
		if ( 0 === $disable_css ) {
			$css_file = ( 0 === $recommend_style ) ? 'irecommendthis.css' : 'irecommendthis-heart.css';
			$css_path = plugin_dir_path( $this->plugin_file ) . 'assets/css/' . $css_file;
			$css_url  = plugins_url( 'assets/css/' . $css_file, $this->plugin_file );

			wp_enqueue_style( 'irecommendthis', $css_url, array(), THEMEIST_IRT_VERSION );
		}

		// Register and enqueue the main JavaScript file.
		$js_url  = plugins_url( 'assets/js/irecommendthis.js', $this->plugin_file );
		$js_path = plugin_dir_path( $this->plugin_file ) . 'assets/js/irecommendthis.js';

		wp_register_script(
			'irecommendthis',
			$js_url,
			array( 'jquery' ),
			THEMEIST_IRT_VERSION,
			true
		);

		wp_enqueue_script( 'irecommendthis' );

		// Create a nonce for secure AJAX requests and localize it.
		$nonce = wp_create_nonce( 'irecommendthis-nonce' );

		// Localize script with nonce and settings using new naming.
		wp_localize_script(
			'irecommendthis',
			'irecommendthis',
			array(
				'nonce'         => $nonce,
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'options'       => wp_json_encode( $options ),
				'removal_delay' => 250,
				// Add configurable delay for button state transitions.
			)
		);
	}
}
