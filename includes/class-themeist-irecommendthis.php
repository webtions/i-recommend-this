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
		$this->version     = defined( 'THEMEIST_IRT_VERSION' ) ? THEMEIST_IRT_VERSION : '3.10.3';
		$this->db_version  = defined( 'THEMEIST_IRT_DB_VERSION' ) ? THEMEIST_IRT_DB_VERSION : '2.6.3';
		$this->plugin_slug = 'i-recommend-this';
	}

	/**
	 * Add hooks for plugin actions and filters.
	 */
	public function add_hooks() {
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
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$this->create_db_table();
				restore_current_blog();
			}//end foreach
		} else {
			$this->create_db_table();
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

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			post_id BIGINT(20) NOT NULL,
			ip VARCHAR(45) NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$success = empty( $wpdb->last_error );

		if ( $success ) {
			$this->register_plugin_version();
			update_option( 'dot_irecommendthis_db_version', $this->db_version );
			delete_option( 'dot_irecommendthis_db_error' );
		} else {
			update_option( 'dot_irecommendthis_db_error', true );
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

		// Check and add indexes safely.
		$success        = true;
		$indexes_to_add = array(
			'idx_post_id' => "SELECT 1 FROM information_schema.statistics
				WHERE table_schema = DATABASE()
				AND table_name = %s
				AND index_name = 'idx_post_id'",
			'idx_time'    => "SELECT 1 FROM information_schema.statistics
				WHERE table_schema = DATABASE()
				AND table_name = %s
				AND index_name = 'idx_time'",
		);

		foreach ( $indexes_to_add as $index_name => $check_query ) {
			// Check if index already exists.
			$index_exists = $wpdb->get_var(
				$wpdb->prepare( $check_query, $table_name )
			);

			if ( ! $index_exists ) {
				// Add the index.
				$add_index_query = ( 'idx_post_id' === $index_name )
					? "ALTER TABLE $table_name ADD INDEX $index_name (post_id)"
					: "ALTER TABLE $table_name ADD INDEX $index_name (time)";

				$wpdb->suppress_errors( true );
				$result = $wpdb->query( $wpdb->prepare( $add_index_query ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->suppress_errors( false );

				if ( false === $result ) {
					$success = false;
				}
			}
		}//end foreach

		// Update database version.
		update_option( 'dot_irecommendthis_db_version', $this->db_version );

		return $success;
	}

	/**
	 * Check database table and display notices.
	 */
	public function check_db_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'irecommendthis_votes';

		if (
			get_option( 'dot_irecommendthis_db_error' )
			|| $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name // end condition check.
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
