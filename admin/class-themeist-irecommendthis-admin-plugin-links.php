<?php
/**
 * Plugin Links component for admin functionality.
 *
 * Handles adding links to the plugin on the plugins page.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle plugin links on the plugins page.
 */
class Themeist_IRecommendThis_Admin_Plugin_Links {

	/**
	 * The path to the main plugin file.
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
		add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( $this, 'add_meta_links' ), 10, 2 );
	}

	/**
	 * Add the settings link to the Plugins page.
	 *
	 * @param array  $links Existing plugin action links.
	 * @param string $file  Plugin file path.
	 *
	 * @return array Modified plugin action links.
	 */
	public function add_settings_link( $links, $file ) {
		if ( plugin_basename( $this->plugin_file ) === $file ) {
			$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=irecommendthis_settings' ) ) . '">' . __( 'Settings', 'i-recommend-this' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	/**
	 * Add meta links to the plugin row in the Plugins page.
	 *
	 * @param array  $links Existing plugin meta links.
	 * @param string $file  Plugin file path.
	 *
	 * @return array Modified plugin meta links.
	 */
	public function add_meta_links( $links, $file ) {
		if ( strpos( $file, 'i-recommend-this.php' ) !== false ) {
			$new_links = array(
				'donate'        => '<a href="https://www.paypal.me/harishchouhan" target="_blank">Donate</a>',
				'Documentation' => '<a href="https://themeist.com/docs/#utm_source=wp-plugin&utm_medium=i-recommend-this&utm_campaign=plugins-page" target="_blank">Documentation</a>',
			);

			$links = array_merge( $links, $new_links );
		}
		return $links;
	}
}
