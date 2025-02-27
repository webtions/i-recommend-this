<?php
/**
 * Admin tools for I Recommend This plugin.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle administrative tools and utilities for the plugin.
 */
class Themeist_IRecommendThis_Admin_Tools {

	/**
	 * Instance of the main plugin class.
	 *
	 * @var Themeist_IRecommendThis
	 */
	private $plugin;

	/**
	 * Constructor.
	 *
	 * @param Themeist_IRecommendThis $plugin Main plugin instance.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Register hooks for the admin tools.
	 */
	public function add_hooks() {
		add_action( 'admin_init', array( $this, 'handle_database_update' ) );
		// Add hook for URL-based updates.
		add_action( 'admin_init', array( $this, 'process_direct_update' ) );
		add_action( 'admin_menu', array( $this, 'add_tools_submenu' ) );
	}

	/**
	 * Add a submenu to the tools menu.
	 */
	public function add_tools_submenu() {
		add_submenu_page(
			'tools.php',
			__( 'I Recommend This - Database Tools', 'i-recommend-this' ),
			__( 'I Recommend This', 'i-recommend-this' ),
			'manage_options',
			'irecommendthis-tools',
			array( $this, 'render_tools_page' )
		);
	}

	/**
	 * Render the tools page.
	 */
	public function render_tools_page() {
		// Validate GET parameter.
		if ( isset( $_GET['updated'] ) && check_admin_referer( 'irecommendthis_update_success', 'updated_nonce' ) ) {
			// Check for completed update.
			if ( '1' === sanitize_text_field( wp_unslash( $_GET['updated'] ) ) ) {
				echo '<div class="notice notice-success is-dismissible"><p>' .
					esc_html__( 'Database has been updated successfully!', 'i-recommend-this' ) .
					'</p></div>';
			}
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'I Recommend This - Database Tools', 'i-recommend-this' ); ?></h1>

			<div class="card">
				<h2><?php esc_html_e( 'Database Optimization', 'i-recommend-this' ); ?></h2>
				<p><?php esc_html_e( 'This tool will optimize the database tables used by the I Recommend This plugin. It will add appropriate indexes and update the table structure for better performance.', 'i-recommend-this' ); ?></p>

				<form method="post" action="">
					<?php wp_nonce_field( 'irecommendthis_update_db', 'irecommendthis_db_nonce' ); ?>
					<input type="hidden" name="irecommendthis_action" value="update_db">
					<p>
						<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Optimize Database', 'i-recommend-this' ); ?>">
					</p>
				</form>

				<h3><?php esc_html_e( 'URL-based Update', 'i-recommend-this' ); ?></h3>
				<p><?php esc_html_e( 'You can also run the optimization via a direct URL. This is useful for automation or for including in documentation.', 'i-recommend-this' ); ?></p>
				<?php
				$nonce      = wp_create_nonce( 'irecommendthis_update_db' );
				$update_url = admin_url( 'tools.php?page=irecommendthis-tools&action=update_db&nonce=' . $nonce );
				?>
				<code style="display:block;padding:10px;background:#f6f7f7;margin-bottom:15px;"><?php echo esc_url( $update_url ); ?></code>
				<p><small><?php esc_html_e( 'Note: This URL includes a security nonce that expires after 24 hours. Generate a new URL if needed.', 'i-recommend-this' ); ?></small></p>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Database Information', 'i-recommend-this' ); ?></h2>
				<?php $this->display_database_info(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Display database table information.
	 */
	private function display_database_info() {
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

		// Database version.
		$db_version = get_option( 'dot_irecommendthis_db_version', 'Unknown' );

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

	/**
	 * Handle the database update request from form submission.
	 */
	public function handle_database_update() {
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
		$redirect_url = add_query_arg(
			array(
				'updated'       => '1',
				'updated_nonce' => $updated_nonce,
			),
			admin_url( 'tools.php?page=irecommendthis-tools' )
		);
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Process direct URL-based update request (alternative to form submission).
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
		$redirect_url = add_query_arg(
			array(
				'updated'       => '1',
				'updated_nonce' => $updated_nonce,
			),
			admin_url( 'tools.php?page=irecommendthis-tools' )
		);
		wp_safe_redirect( $redirect_url );
		exit;
	}
}
