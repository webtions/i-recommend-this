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
		$this->version     = defined( 'THEMEIST_IRT_VERSION' ) ? THEMEIST_IRT_VERSION : '4.0.0';
		$this->db_version  = defined( 'THEMEIST_IRT_DB_VERSION' ) ? THEMEIST_IRT_DB_VERSION : '2.7.0';
		$this->plugin_slug = 'i-recommend-this';
	}

	/**
	 * Add hooks for plugin actions and filters.
	 */
	public function add_hooks() {
		register_activation_hook( $this->plugin_file, array( $this, 'activate' ) );

		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		add_action( 'init', array( $this, 'update_check' ) );
		add_action( 'init', array( $this, 'migrate_plugin_settings' ) );
		add_action( 'admin_notices', array( $this, 'check_db_table' ) );
	}

	/**
	 * Activate the plugin.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 */
	public function activate( $network_wide ) {
		// Migrate settings first.
		$this->migrate_plugin_settings();

		if ( is_multisite() && $network_wide ) {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$this->create_db_table();
				restore_current_blog();
			}
		} else {
			$this->create_db_table();
		}
	}

	/**
	 * Migrate plugin settings from old to new option keys.
	 *
	 * This method handles the transition from old option names to new ones
	 * to ensure a smooth upgrade path for existing users.
	 */
	public function migrate_plugin_settings() {
		// Migrate settings from old to new option keys.
		$old_settings     = get_option( 'dot_irecommendthis_settings' );
		$current_settings = get_option( 'irecommendthis_settings' );

		// Only migrate if old settings exist and current settings are empty/don't exist.
		if ( $old_settings && empty( $current_settings ) ) {
			update_option( 'irecommendthis_settings', $old_settings );
			// Keep the old setting for one more version, but we'll remove this in a future version.
			// Don't delete the old settings yet to allow for rollback if needed.
		}

		// Migrate database version.
		$old_db_version = get_option( 'dot_irecommendthis_db_version' );
		if ( $old_db_version ) {
			update_option( 'irecommendthis_db_version', $old_db_version );
			// Keep the old version for one more version cycle.
			// Don't delete the old version yet to allow for rollback if needed.
		}
	}

	/**
	 * Create the database table.
	 *
	 * @return bool Whether the table was successfully created.
	 */
	private function create_db_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'irecommendthis_votes';
		$charset_collate = $wpdb->get_charset_collate();

		// Updated IP column to VARCHAR(255) to accommodate hashed IPs.
		$sql = 'CREATE TABLE IF NOT EXISTS ' . $table_name . " (
			id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			post_id BIGINT(20) NOT NULL,
			ip VARCHAR(255) NOT NULL,
			PRIMARY KEY (id),
			INDEX idx_post_id (post_id),
			INDEX idx_time (time)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$success = empty( $wpdb->last_error );

		if ( $success ) {
			$this->register_plugin_version();
			update_option( 'irecommendthis_db_version', $this->db_version );
			delete_option( 'irecommendthis_db_error' );
		} else {
			update_option( 'irecommendthis_db_error', true );
		}

		return $success;
	}

	/**
	 * Update the database table.
	 *
	 * @return bool Whether the update was successful.
	 */
	public function update() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'irecommendthis_votes';

		// Ensure table exists.
		$table_exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name )
		);

		if ( ! $table_exists ) {
			return $this->create_db_table();
		}

		// Check existing column size for IP and update if needed.
		$column_size = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = \'ip\'',
				DB_NAME,
				$table_name
			)
		);

		// Update IP column to 255 characters if it's smaller (to accommodate hashed IPs).
		if ( $column_size && (int) $column_size < 255 ) {
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE `%s` MODIFY ip VARCHAR(255) NOT NULL', $table_name ) );
		}

		// Check and add indexes safely.
		$success = true;
		$indexes = array( 'idx_post_id', 'idx_time' );

		foreach ( $indexes as $index_name ) {
			if ( 'idx_post_id' === $index_name ) {
				$index_exists = $wpdb->get_var( $wpdb->prepare( "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = %s AND index_name = 'idx_post_id'", $table_name ) );
			} else {
				$index_exists = $wpdb->get_var( $wpdb->prepare( "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = %s AND index_name = 'idx_time'", $table_name ) );
			}

			if ( ! $index_exists ) {
				if ( 'idx_post_id' === $index_name ) {
					$result = $wpdb->query( $wpdb->prepare( 'ALTER TABLE `%s` ADD INDEX %s (post_id)', $table_name, $index_name ) );
				} else {
					$result = $wpdb->query( $wpdb->prepare( 'ALTER TABLE `%s` ADD INDEX %s (time)', $table_name, $index_name ) );
				}
				if ( false === $result ) {
					$success = false;
				}
			}
		}

		// Optionally run migration to convert existing IPs to hashed format.
		$this->maybe_migrate_ip_data( $table_name );

		// Update database version.
		update_option( 'irecommendthis_db_version', $this->db_version );

		return $success;
	}

	/**
	 * Migrate existing IP data to hashed format.
	 *
	 * Converts raw IP addresses to anonymized hashes for improved privacy.
	 * Uses global hashing to allow tracking across different posts.
	 * Runs in batches to avoid timeouts on large databases.
	 *
	 * @since 4.0.0
	 *
	 * @param string $table_name The name of the table containing IP data.
	 * @return bool Whether the migration was successful.
	 */
	private function maybe_migrate_ip_data( $table_name ) {
		global $wpdb;

		// Check if migration has already been performed.
		$migration_done = get_option( 'irecommendthis_ip_migration_complete', false );
		if ( $migration_done ) {
			return true;
		}

		// Get the plugin settings.
		$options          = get_option( 'irecommendthis_settings' );
		$enable_unique_ip = isset( $options['enable_unique_ip'] ) ? (int) $options['enable_unique_ip'] : 0;

		// Only proceed if IP tracking is enabled.
		if ( 0 === $enable_unique_ip ) {
			// Mark as done even though we skipped the migration (not needed if IP tracking is disabled).
			update_option( 'irecommendthis_ip_migration_complete', true );
			return true;
		}

		$records = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT id, ip FROM %s WHERE LENGTH(ip) < %d LIMIT %d',
				$table_name,
				40,
				1000
			)
		);

		// If no records found, we're done with migration.
		if ( empty( $records ) ) {
			update_option( 'irecommendthis_ip_migration_complete', true );
			return true;
		}

		require_once __DIR__ . '/../public/class-themeist-irecommendthis-public-processor.php';

		foreach ( $records as $record ) {
			// Skip if already looks like a hash.
			if ( strlen( $record->ip ) > 40 ) {
				continue;
			}

			$hashed_ip = Themeist_IRecommendThis_Public_Processor::anonymize_ip( $record->ip );

			$wpdb->update(
				$table_name,
				array( 'ip' => $hashed_ip ),
				array( 'id' => $record->id ),
				array( '%s' ),
				array( '%d' )
			);
		}

		return true;
	}

	/**
	 * Check database table and display notices.
	 */
	public function check_db_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'irecommendthis_votes';

		if (
			get_option( 'irecommendthis_db_error' )
			|| $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name
		) {
			echo '<div class="notice notice-error"><p>' .
				esc_html__( 'Error creating database table for I Recommend This plugin. Please check your WordPress error logs.', 'i-recommend-this' ) .
				'</p></div>';
		}
	}

	/**
	 * Register the plugin version.
	 */
	public function register_plugin_version() {
		if ( $this->version ) {
			update_option( 'irecommendthis-version', $this->version );
		}
	}

	/**
	 * Check for updates and run the update script if necessary.
	 */
	public function update_check() {
		$current_db_version = get_option( 'irecommendthis_db_version' );
		if ( ! $current_db_version ) {
			$current_db_version = get_option( 'dot_irecommendthis_db_version' );
		}

		// Use Yoda condition checks.
		if ( $current_db_version !== $this->db_version ) {
			$this->update();
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
	 * @return string Plugin version.
	 */
	public function get_version() {
		return $this->version;
	}
}
