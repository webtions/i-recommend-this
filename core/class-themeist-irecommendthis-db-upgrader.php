<?php
/**
 * Database upgrader class for the I Recommend This plugin.
 *
 * This class handles all database operations including:
 * - Table creation
 * - Schema updates
 * - Data migration
 * - IP anonymization
 *
 * @package IRecommendThis
 * @subpackage Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Database Upgrader class that manages database schema and data.
 */
class Themeist_IRecommendThis_DB_Upgrader {

	/**
	 * The main plugin instance.
	 *
	 * @var Themeist_IRecommendThis
	 */
	private $plugin;

	/**
	 * Database version.
	 *
	 * @var string
	 */
	private $db_version;

	/**
	 * Batch size for data processing.
	 *
	 * @var int
	 */
	private $batch_size = 1000;

	/**
	 * Constructor.
	 *
	 * @param Themeist_IRecommendThis $plugin Main plugin instance.
	 */
	public function __construct( $plugin ) {
		$this->plugin     = $plugin;
		$this->db_version = defined( 'THEMEIST_IRT_DB_VERSION' ) ? THEMEIST_IRT_DB_VERSION : '3.0.0';
	}

	/**
	 * Initialize hooks.
	 */
	public function init() {
		// Check for database updates on plugin initialization.
		add_action( 'init', array( $this, 'check_for_updates' ), 5 );

		// Handle database updates for multisite.
		add_action( 'wpmu_new_blog', array( $this, 'new_site_created' ) );
	}

	/**
	 * Create the database table on a new site in multisite.
	 *
	 * @param int $blog_id The ID of the newly created blog.
	 */
	public function new_site_created( $blog_id ) {
		if ( is_plugin_active_for_network( plugin_basename( $this->plugin->plugin_file ) ) ) {
			switch_to_blog( $blog_id );
			$this->create_table();
			restore_current_blog();
		}
	}

	/**
	 * Check if database needs an update and run the update if needed.
	 */
	public function check_for_updates() {
		$current_db_version = get_option( 'irecommendthis_db_version' );
		if ( ! $current_db_version ) {
			$current_db_version = get_option( 'dot_irecommendthis_db_version' );
		}

		// If no version is recorded or version is different, run update.
		if ( ! $current_db_version || version_compare( $current_db_version, $this->db_version, '<' ) ) {
			// Trigger action before database update.
			do_action( 'irecommendthis_before_db_update', $current_db_version, $this->db_version );

			$this->update();

			// Update version in database.
			update_option( 'irecommendthis_db_version', $this->db_version );

			// Trigger action after database update.
			do_action( 'irecommendthis_after_db_update', $current_db_version, $this->db_version );
		}
	}

	/**
	 * Create the database table.
	 *
	 * @return bool Whether the table was successfully created.
	 */
	public function create_table() {
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
			delete_option( 'irecommendthis_db_error' );
		} else {
			update_option( 'irecommendthis_db_error', true );
		}

		return $success;
	}

	/**
	 * Update the database schema and potentially migrate data.
	 *
	 * @return bool Whether the update was successful.
	 */
	public function update() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'irecommendthis_votes';

		// Ensure table exists, create if it doesn't.
		$table_exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name )
		);

		if ( ! $table_exists ) {
			return $this->create_table();
		}

		// Run each update method in sequence.
		$success = $this->update_ip_column_size( $table_name );
		$success = $success && $this->ensure_indexes( $table_name );
		$success = $success && $this->maybe_anonymize_ips( $table_name );

		return $success;
	}

	/**
	 * Update IP column size if needed.
	 *
	 * @param string $table_name The database table name.
	 * @return bool Whether the operation was successful.
	 */
	private function update_ip_column_size( $table_name ) {
		global $wpdb;

		// Check existing column size for IP.
		$column_size = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s',
				DB_NAME,
				$table_name,
				'ip'
			)
		);

		// Update IP column to 255 characters if it's smaller (to accommodate hashed IPs).
		if ( $column_size && (int) $column_size < 255 ) {
			// Note: We're using a direct query here, but with proper sanitization.
			$table_name_escaped = esc_sql( $table_name );
			$alter_sql          = "ALTER TABLE `$table_name_escaped` MODIFY ip VARCHAR(255) NOT NULL";

			// Direct query necessary for schema alterations.
			$result = $wpdb->query( $alter_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return false !== $result;
		}

		return true;
	}

	/**
	 * Ensure all necessary indexes exist on the table.
	 *
	 * @param string $table_name The database table name.
	 * @return bool Whether the operation was successful.
	 */
	private function ensure_indexes( $table_name ) {
		global $wpdb;

		$success = true;
		$indexes = array(
			'idx_post_id' => 'post_id',
			'idx_time'    => 'time',
		);

		foreach ( $indexes as $index_name => $column ) {
			$index_exists = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT 1 FROM information_schema.statistics
					WHERE table_schema = DATABASE()
					AND table_name = %s
					AND index_name = %s',
					$table_name,
					$index_name
				)
			);

			if ( ! $index_exists ) {
				// Note: Direct query necessary for adding indexes.
				$table_name_escaped = esc_sql( $table_name );
				$index_name_escaped = esc_sql( $index_name );
				$column_escaped     = esc_sql( $column );
				$add_index_sql      = "ALTER TABLE `$table_name_escaped` ADD INDEX $index_name_escaped ($column_escaped)";

				$result = $wpdb->query( $add_index_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				if ( false === $result ) {
					$success = false;
				}
			}
		}//end foreach

		return $success;
	}

	/**
	 * Anonymize IP addresses if needed.
	 *
	 * @param string $table_name The database table name.
	 * @return bool Whether the operation was successful.
	 */
	private function maybe_anonymize_ips( $table_name ) {
		global $wpdb;

		// Check if migration has already been performed.
		$migration_done = get_option( 'irecommendthis_ip_migration_complete', false );
		if ( $migration_done ) {
			return true;
		}

		// Get plugin settings to check if IP tracking is enabled.
		$options          = get_option( 'irecommendthis_settings' );
		$enable_unique_ip = isset( $options['enable_unique_ip'] ) ? (int) $options['enable_unique_ip'] : 0;

		// Only proceed if IP tracking is enabled.
		if ( 0 === $enable_unique_ip ) {
			// Mark as done even if we skip (not needed when IP tracking is disabled).
			update_option( 'irecommendthis_ip_migration_complete', true );
			return true;
		}

		// Process in batches to avoid timeouts.
		$processed = 0;
		$has_more  = true;

		while ( $has_more ) {
			// Get a batch of records to process.
			$table_name_escaped = esc_sql( $table_name );

			// First prepare the statement without the table name.
			$prepared_sql = $wpdb->prepare(
				'WHERE LENGTH(ip) < %d LIMIT %d',
				40,
				$this->batch_size
			);

			// Then combine it with the escaped table name in a separate step.
			$query = "SELECT id, ip FROM `$table_name_escaped` $prepared_sql";

			// Execute with a direct query since we can't prepare table names.
			$records = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			// No more records, we're done.
			if ( empty( $records ) ) {
				$has_more = false;
				update_option( 'irecommendthis_ip_migration_complete', true );
				break;
			}

			// Process each record in this batch.
			foreach ( $records as $record ) {
				// Skip if already looks like a hash.
				if ( strlen( $record->ip ) > 40 ) {
					continue;
				}

				$hashed_ip = $this->anonymize_ip( $record->ip );

				$wpdb->update(
					$table_name,
					array( 'ip' => $hashed_ip ),
					array( 'id' => $record->id ),
					array( '%s' ),
					array( '%d' )
				);

				++$processed;
			}//end foreach

			// If we've processed less than the batch size, we're done.
			if ( count( $records ) < $this->batch_size ) {
				$has_more = false;
				update_option( 'irecommendthis_ip_migration_complete', true );
			}

			// Give the server a brief moment to breathe if we're processing a lot of records.
			if ( $processed > 5000 ) {
				sleep( 1 );
			}
		}//end while

		return true;
	}

	/**
	 * Anonymize an IP address for secure storage using global hashing.
	 *
	 * @param string $ip The IP address to anonymize.
	 * @return string The anonymized (hashed) IP.
	 */
	private function anonymize_ip( $ip ) {
		// Empty IPs should return a consistent hash.
		if ( empty( $ip ) ) {
			$ip = 'unknown';
		}

		// Use WordPress salt for authentication.
		$auth_salt = wp_salt( 'auth' );

		// Use site-specific hash for additional entropy.
		$site_hash = defined( 'COOKIEHASH' ) ? COOKIEHASH : md5( site_url() );

		// Create the hash using WordPress hash function with site context.
		$hashed_ip = wp_hash( $ip . $site_hash, 'auth' );

		return $hashed_ip;
	}

	/**
	 * Get current database version.
	 *
	 * @return string The current database version.
	 */
	public function get_db_version() {
		return $this->db_version;
	}

	/**
	 * Check if the database table exists.
	 *
	 * @return bool Whether the table exists.
	 */
	public function table_exists() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'irecommendthis_votes';

		return $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name )
		) === $table_name;
	}

	/**
	 * Get database table information.
	 *
	 * @return array|false Table information or false if table doesn't exist.
	 */
	public function get_table_info() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'irecommendthis_votes';

		if ( ! $this->table_exists() ) {
			return false;
		}

		// Get table structure.
		$table_name_escaped = esc_sql( $table_name );
		$structure_sql      = "DESCRIBE `$table_name_escaped`";
		$structure          = $wpdb->get_results( $structure_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Get indexes.
		$indexes_sql = "SHOW INDEX FROM `$table_name_escaped`";
		$indexes     = $wpdb->get_results( $indexes_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Get record count.
		$count_sql = "SELECT COUNT(*) FROM `$table_name_escaped`";
		$count     = $wpdb->get_var( $count_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return array(
			'structure'    => $structure,
			'indexes'      => $indexes,
			'record_count' => $count,
		);
	}
}
