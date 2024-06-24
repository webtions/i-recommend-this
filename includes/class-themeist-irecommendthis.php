<?php
/**
 * Themeist_IRecommendThis Class
 *
 * @package IRecommendThis
 */

/**
 * Main plugin class.
 */
class Themeist_IRecommendThis {

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	protected $plugin_slug;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Database version.
	 *
	 * @var string
	 */
	protected $db_version;

	/**
	 * Plugin file.
	 *
	 * @var string
	 */
	public $plugin_file;

	/**
	 * Constructor method to initialize the class.
	 *
	 * @param string $plugin_file The main plugin file path.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;

		$this->version    = defined( 'THEMEIST_IRT_VERSION' ) ? THEMEIST_IRT_VERSION : '3.8.3';
		$this->db_version = defined( 'THEMEIST_IRT_DB_VERSION' ) ? THEMEIST_IRT_DB_VERSION : '2.6.2';

		$this->plugin_slug = 'i-recommend-this';
	}

	/**
	 * Add hooks for activation, localization, etc.
	 */
	public function add_hooks() {
		// Run this on activation / deactivation.
		register_activation_hook( $this->plugin_file, array( $this, 'activate' ) );

		// Load localization.
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
	}

	/**
	 * Activate the plugin.
	 *
	 * @param bool $network_wide Whether the plugin is network-activated.
	 */
	public function activate( $network_wide ) {
		global $wpdb;

		if ( is_multisite() && $network_wide ) {
			// Get all blogs in the network and activate the plugin on each one.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				$this->single_activate();
				restore_current_blog();
			}
		} else {
			$this->single_activate();
		}
	}

	/**
	 * Single site activation.
	 */
	protected function single_activate() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'irecommendthis_votes';

		// Create the database table if it doesn't exist.
		// Note: Using direct database call here as this is a one-time setup action during activation.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.SchemaChange
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
			$sql = "CREATE TABLE $table_name (
				id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
				time TIMESTAMP NOT NULL,
				post_id BIGINT(20) NOT NULL,
				ip VARCHAR(45) NOT NULL,
				UNIQUE KEY id (id)
			);";

			// Including the upgrade script for dbDelta function.
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			// dbDelta is used to handle database schema changes in a safe way.
			dbDelta( $sql );

			$this->register_plugin_version();

			if ( '' !== $this->db_version ) {
				update_option( 'dot_irecommendthis_db_version', $this->db_version );
			}
		}
	}

	/**
	 * Register the plugin version.
	 */
	public function register_plugin_version() {
		if ( '' !== $this->version ) {
			update_option( 'dot-irecommendthis-version', $this->version );
		}
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_localisation() {
		load_plugin_textdomain( 'i-recommend-this', false, dirname( plugin_basename( $this->plugin_file ) ) . '/languages/' );
	}

	/**
	 * Get the plugin version.
	 *
	 * @return string The plugin version.
	 */
	public function get_version() {
		return $this->version;
	}
}
