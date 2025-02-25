<?php

/**
 * Main class for the I Recommend This plugin.
 *
 * @package IRecommendThis
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
	 * Path to the main plugin file.
	 *
	 * @var string
	 */
	public $plugin_file;

	/**
	 * Constructor to initialize the plugin.
	 *
	 * @param string $plugin_file Path to the main plugin file.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
		$this->version     = defined( 'THEMEIST_IRT_VERSION' ) ? THEMEIST_IRT_VERSION : '3.8.3';
		$this->db_version  = defined( 'THEMEIST_IRT_DB_VERSION' ) ? THEMEIST_IRT_DB_VERSION : '2.6.2';
		$this->plugin_slug = 'i-recommend-this';
	}

	/**
	 * Add hooks for plugin actions and filters.
	 */
	public function add_hooks() {
		// Run this on activation / deactivation.
		register_activation_hook( $this->plugin_file, array( $this, 'activate' ) );

		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		add_action( 'init', array( $this, 'update_check' ) );
		add_action( 'admin_notices', array( $this, 'check_db_table' ) );
	}

	/**
	 * Activate the plugin.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 */
	public function activate( $network_wide ) {
		if ( is_multisite() && $network_wide ) {
			// Get all blogs in the network and activate the plugin on each one.
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$this->create_db_table();
				restore_current_blog();
			}
		} else {
			// Single site activation.
			$this->create_db_table();
		}
	}

	/**
	 * Create the database table.
	 *
	 * @return bool True if successful, false otherwise.
	 */
	private function create_db_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'irecommendthis_votes';
		$charset_collate = $wpdb->get_charset_collate();

		// Modified table structure to include indexes and make IP nullable
		$sql = "CREATE TABLE $table_name (
			id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			time TIMESTAMP NOT NULL,
			post_id BIGINT(20) NOT NULL,
			ip VARCHAR(45) NULL,
			UNIQUE KEY id (id),
			INDEX post_id_index (post_id),
			INDEX ip_index (ip)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$success = empty( $wpdb->last_error );

		if ( $success ) {
			$this->register_plugin_version();
			if ( $this->db_version ) {
				update_option( 'dot_irecommendthis_db_version', $this->db_version );
			}
			// Remove the error flag if it exists.
			delete_option( 'dot_irecommendthis_db_error' );
		} else {
			// Set an error flag.
			update_option( 'dot_irecommendthis_db_error', true );
		}

		return $success;
	}

	/**
	 * Optimize the database structure for better performance.
	 *
	 * This function adds necessary indexes and updates the IP column
	 * to be nullable, which improves query performance and supports
	 * the option to disable IP saving.
	 *
	 * @return bool True if successful, false otherwise.
	 */
	private function optimize_database_structure() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'irecommendthis_votes';

		// Check if the table exists before attempting to modify it
		$table_exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(1) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
			DB_NAME,
			$table_name
		) );

		if ( empty( $table_exists ) ) {
			return false;
		}

		// Check for existing indexes to avoid errors
		$post_id_index_exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema = %s AND table_name = %s AND index_name = %s",
			DB_NAME,
			$table_name,
			'post_id_index'
		) );

		$ip_index_exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema = %s AND table_name = %s AND index_name = %s",
			DB_NAME,
			$table_name,
			'ip_index'
		) );

		// Start collecting SQL statements
		$sql_statements = array();

		// Add post_id index if it doesn't exist
		if ( empty( $post_id_index_exists ) ) {
			$sql_statements[] = "ALTER TABLE {$table_name} ADD INDEX post_id_index (post_id)";
		}

		// Add ip index if it doesn't exist
		if ( empty( $ip_index_exists ) ) {
			$sql_statements[] = "ALTER TABLE {$table_name} ADD INDEX ip_index (ip)";
		}

		// Check IP column type and update to be nullable if needed
		$ip_column_nullable = $wpdb->get_row( $wpdb->prepare(
			"SELECT is_nullable FROM information_schema.columns WHERE table_schema = %s AND table_name = %s AND column_name = %s",
			DB_NAME,
			$table_name,
			'ip'
		) );

		if ( $ip_column_nullable && 'NO' === $ip_column_nullable->is_nullable ) {
			$sql_statements[] = "ALTER TABLE {$table_name} MODIFY ip VARCHAR(45) NULL";
		}

		// Execute the SQL statements
		$success = true;
		foreach ( $sql_statements as $sql ) {
			$result = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
			if ( false === $result ) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Check the existence of the database table and display an admin notice if it doesn't exist.
	 */
	public function check_db_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'irecommendthis_votes';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		if ( get_option( 'dot_irecommendthis_db_error' ) || $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
			echo '<div class="notice notice-error"><p>Error creating database table for I Recommend This plugin. Please check the error log for details.</p></div>';
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
	 * Check for updates and run the update script if necessary.
	 */
	public function update_check() {
		$current_db_version = get_option( 'dot_irecommendthis_db_version' );

		if ( $this->db_version !== $current_db_version ) {
			$this->update();
		}
	}

	/**
	 * Run the update script.
	 *
	 * @return bool True if update was successful, false otherwise.
	 */
	public function update() {
		// Recreate or update the database table as needed.
		$table_result = $this->create_db_table();

		// Apply optimizations to the database structure.
		$optimize_result = $this->optimize_database_structure();

		// Update the database version.
		update_option( 'dot_irecommendthis_db_version', $this->db_version );

		return ( $table_result !== false && $optimize_result !== false );
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
