<?php

class Themeist_IRecommendThis {

	protected $plugin_slug;
	protected $version;
	protected $db_version;
	public $plugin_file;

	/**
	 * Constructor to initialize the plugin.
	 *
	 * @param string $plugin_file Path to the main plugin file.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;

		$this->version = defined( 'THEMEIST_IRT_VERSION' ) ? THEMEIST_IRT_VERSION : '3.8.3';
		$this->db_version = defined( 'THEMEIST_IRT_DB_VERSION' ) ? THEMEIST_IRT_DB_VERSION : '2.6.2';
		$this->plugin_slug = 'i-recommend-this';
	}

	/**
	 * Add hooks for plugin actions and filters.
	 */
	public function add_hooks() {
		// Run this on activation / deactivation
		register_activation_hook( $this->plugin_file, array( $this, 'activate' ) );

		add_action( 'init', array( $this, 'load_localisation' ), 0 );
	}

	/**
	 * Activate the plugin.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 */
	public function activate( $network_wide ) {
		global $wpdb;

		$table_name = $wpdb->prefix . "irecommendthis_votes";
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
			$sql = "CREATE TABLE $table_name (
				id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
				time TIMESTAMP NOT NULL,
				post_id BIGINT(20) NOT NULL,
				ip VARCHAR(45) NOT NULL,
				UNIQUE KEY id (id)
			);";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			$this->register_plugin_version();

			if ( $this->db_version ) {
				update_option( 'dot_irecommendthis_db_version', $this->db_version );
			}
		}
	}

	/**
	 * Register the plugin version.
	 */
	public function register_plugin_version() {
		if ( $this->version ) {
			update_option( 'dot-irecommendthis-version', $this->version );
		}
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.4.6
	 */
	public function load_localisation() {
		load_plugin_textdomain( 'i-recommend-this', false, dirname( plugin_basename( $this->plugin_file ) ) . '/languages/' );
	}

	/**
	 * Get the plugin version.
	 *
	 * @return string Plugin version.
	 */
	public function get_version() {
		return $this->version;
	}
}
