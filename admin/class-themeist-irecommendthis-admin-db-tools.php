<?php
/**
 * Database Tools component for admin functionality.
 *
 * Handles database optimization, updates, and information display.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle database operations and tools.
 */
class Themeist_IRecommendThis_Admin_DB_Tools {

	/**
	 * The main plugin instance.
	 *
	 * @var Themeist_IRecommendThis
	 */
	private $plugin;

	/**
	 * Constructor.
	 *
	 * @param Themeist_IRecommendThis $plugin The main plugin instance.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Initialize the component.
	 */
	public function initialize() {
		add_action( 'admin_init', array( $this, 'handle_database_update_request' ) );
	}

	/**
	 * Handle database update request from the admin interface.
	 */
	public function handle_database_update_request() {
		// Check if this is our action.
		if ( ! isset( $_POST['irecommendthis_action'] ) || 'update_db' !== $_POST['irecommendthis_action'] ) {
			return;
		}

		// Verify user has permission.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'i-recommend-this' ) );
		}

		// Verify nonce.
		if ( ! isset( $_POST['irecommendthis_db_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['irecommendthis_db_nonce'] ) ), 'irecommendthis_update_db' ) ) {
			wp_die( esc_html__( 'Security check failed. Please try again.', 'i-recommend-this' ) );
		}

		// Run the update.
		$result = $this->plugin->update();

		// Create a nonce for the redirect.
		$updated_nonce = wp_create_nonce( 'irecommendthis_update_success' );

		// Redirect with success message and nonce.
		// Using db_updated instead of updated to avoid triggering WordPress settings notice
		$redirect_url = add_query_arg(
			array(
				'page'          => 'irecommendthis-settings',
				'tab'           => 'dbtools',
				'db_updated'    => '1',
				'updated_nonce' => $updated_nonce,
			),
			admin_url( 'options-general.php' )
		);
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Process direct URL-based update request.
	 *
	 * Usage: wp-admin/tools.php?page=irecommendthis-tools&action=update_db&nonce=GENERATED_NONCE
	 */
	public function process_direct_update() {
		// Check if this is our action.
		if ( ! isset( $_GET['page'] ) || 'irecommendthis-tools' !== $_GET['page'] || ! isset( $_GET['action'] ) || 'update_db' !== $_GET['action'] ) {
			return;
		}

		// Verify user has permission.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'i-recommend-this' ) );
		}

		// Verify nonce.
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'irecommendthis_update_db' ) ) {
			wp_die( esc_html__( 'Security check failed. The link you used has expired or is invalid. Please try again with a new link.', 'i-recommend-this' ) );
		}

		// Run the update.
		$result = $this->plugin->update();

		// Create a nonce for the redirect.
		$updated_nonce = wp_create_nonce( 'irecommendthis_update_success' );

		// Redirect with success message and nonce.
		// Using db_updated instead of updated to avoid triggering WordPress settings notice
		$redirect_url = add_query_arg(
			array(
				'page'          => 'irecommendthis-settings',
				'tab'           => 'dbtools',
				'db_updated'    => '1',
				'updated_nonce' => $updated_nonce,
			),
			admin_url( 'options-general.php' )
		);
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Display database table information.
	 */
	public function display_database_info() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'irecommendthis_votes';

		// Check if table exists.
		$table_exists = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(1) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s',
				DB_NAME,
				$table_name
			)
		);

		if ( empty( $table_exists ) ) {
			echo '<div class="notice notice-error inline"><p>' . esc_html__( 'The database table does not exist.', 'i-recommend-this' ) . '</p></div>';
			return;
		}

		// Get table structure - can't use prepare directly on table name.
		$table_name_escaped = esc_sql( $table_name );
		$structure_sql      = "DESCRIBE $table_name_escaped";
		$structure          = $wpdb->get_results( $structure_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Get indexes - can't use prepare directly on table name.
		$indexes_sql = "SHOW INDEX FROM $table_name_escaped";
		$indexes     = $wpdb->get_results( $indexes_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Group indexes by name.
		$grouped_indexes = array();
		foreach ( $indexes as $index ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( ! isset( $grouped_indexes[ $index->Key_name ] ) ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$grouped_indexes[ $index->Key_name ] = array();
			}
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$grouped_indexes[ $index->Key_name ][] = $index->Column_name;
		}

		// Database version - check new option name first, then fall back to old one
		$db_version = get_option( 'irecommendthis_db_version', get_option( 'dot_irecommendthis_db_version', 'Unknown' ) );

		echo '<p><strong>' . esc_html__( 'Current Database Version:', 'i-recommend-this' ) . '</strong> ' . esc_html( $db_version ) . '</p>';

		// Table structure.
		echo '<h3>' . esc_html__( 'Table Structure', 'i-recommend-this' ) . '</h3>';
		echo '<table class="widefat striped">';
		echo '<thead><tr><th>' . esc_html__( 'Column', 'i-recommend-this' ) . '</th><th>' . esc_html__( 'Type', 'i-recommend-this' ) . '</th><th>' . esc_html__( 'Null', 'i-recommend-this' ) . '</th><th>' . esc_html__( 'Key', 'i-recommend-this' ) . '</th></tr></thead>';
		echo '<tbody>';
		foreach ( $structure as $column ) {
			echo '<tr>';
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			echo '<td>' . esc_html( $column->Field ) . '</td>';
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			echo '<td>' . esc_html( $column->Type ) . '</td>';
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			echo '<td>' . esc_html( $column->Null ) . '</td>';
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			echo '<td>' . esc_html( $column->Key ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';

		// Indexes.
		echo '<h3>' . esc_html__( 'Table Indexes', 'i-recommend-this' ) . '</h3>';
		echo '<table class="widefat striped">';
		echo '<thead><tr><th>' . esc_html__( 'Index Name', 'i-recommend-this' ) . '</th><th>' . esc_html__( 'Columns', 'i-recommend-this' ) . '</th></tr></thead>';
		echo '<tbody>';
		foreach ( $grouped_indexes as $index_name => $columns ) {
			echo '<tr>';
			echo '<td>' . esc_html( $index_name ) . '</td>';
			echo '<td>' . esc_html( implode( ', ', $columns ) ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';

		// Count records - can't use prepare directly on table name.
		$count_sql = "SELECT COUNT(*) FROM $table_name_escaped";
		$count     = $wpdb->get_var( $count_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		echo '<p><strong>' . esc_html__( 'Total Records:', 'i-recommend-this' ) . '</strong> ' . esc_html( number_format_i18n( $count ) ) . '</p>';
	}
}
