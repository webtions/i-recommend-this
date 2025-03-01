<?php
/**
 * Class Themeist_IRecommendThis_Admin
 *
 * Handles admin-specific functionality for the I Recommend This plugin.
 *
 * @package IRecommendThis
 */
class Themeist_IRecommendThis_Admin {

	/**
	 * The path to the main plugin file.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Current tab being viewed.
	 *
	 * @var string
	 */
	private $current_tab;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_file Path to the main plugin file.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
		$this->current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
	}

	/**
	 * Add admin-specific hooks.
	 */
	public function add_admin_hooks() {
		global $pagenow;

		if ( 'plugins.php' === $pagenow ) {
			add_filter( 'plugin_action_links', array( $this, 'add_plugin_settings_link' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links' ), 10, 2 );
		}

		add_action( 'admin_menu', array( $this, 'add_settings_menu' ) );
		add_action( 'admin_init', array( $this, 'initialize_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'publish_post', array( $this, 'setup_recommends' ) );

		add_filter( 'request', array( $this, 'column_orderby' ) );
		add_filter( 'manage_edit-post_sortable_columns', array( $this, 'column_register_sortable' ) );
		add_filter( 'manage_posts_columns', array( $this, 'columns_head' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'column_content' ), 10, 2 );

		// Handle database update action
		add_action( 'admin_init', array( $this, 'handle_database_update' ) );
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'settings_page_irecommendthis-settings' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'irecommendthis-admin-settings',
			plugins_url( 'css/admin-settings.css', dirname( __FILE__ ) ),
			array(),
			THEMEIST_IRT_VERSION
		);

		wp_enqueue_script(
			'irecommendthis-admin-tabs',
			plugins_url( 'js/admin-tabs.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			THEMEIST_IRT_VERSION,
			true
		);
	}

	/**
	 * Add the I Recommend This menu item to the WordPress admin menu.
	 */
	public function add_settings_menu() {
		$page_title = __( 'I Recommend This', 'i-recommend-this' );
		$menu_title = __( 'I Recommend This', 'i-recommend-this' );
		$capability = 'manage_options';
		$menu_slug  = 'irecommendthis-settings';
		$function   = array( $this, 'render_settings_page' );

		add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function );
	}

	/**
	 * Add the settings link to the Plugins overview.
	 *
	 * @param array  $links Existing plugin action links.
	 * @param string $file  Plugin file path.
	 *
	 * @return array Modified plugin action links.
	 */
	public function add_plugin_settings_link( $links, $file ) {
		if ( plugin_basename( $this->plugin_file ) === $file ) {
			$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=irecommendthis-settings' ) ) . '">' . __( 'Settings', 'i-recommend-this' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	/**
	 * Add meta links to the plugin row in the Plugins overview.
	 *
	 * @param array  $links Existing plugin meta links.
	 * @param string $file  Plugin file path.
	 *
	 * @return array Modified plugin meta links.
	 */
	public function add_plugin_meta_links( $links, $file ) {
		if ( strpos( $file, 'i-recommend-this.php' ) !== false ) {
			$new_links = array(
				'donate'        => '<a href="https://www.paypal.me/harishchouhan" target="_blank">Donate</a>',
				'Documentation' => '<a href="https://themeist.com/docs/#utm_source=wp-plugin&utm_medium=i-recommend-this&utm_campaign=plugins-page" target="_blank">Documentation</a>',
			);

			$links = array_merge( $links, $new_links );
		}
		return $links;
	}

	/**
	 * Initialize settings and register them.
	 */
	public function initialize_settings() {
		register_setting( 'irecommendthis-settings', 'irecommendthis_settings', array( $this, 'settings_validate' ) );

		add_settings_section( 'irecommendthis', '', array( $this, 'section_intro' ), 'irecommendthis-settings' );

		add_settings_field( 'show_on', __( 'Automatically display on', 'i-recommend-this' ), array( $this, 'setting_show_on' ), 'irecommendthis-settings', 'irecommendthis' );

		add_settings_field( 'text_zero_suffix', __( 'Text after 0 Count', 'i-recommend-this' ), array( $this, 'setting_text_zero_suffix' ), 'irecommendthis-settings', 'irecommendthis' );

		add_settings_field( 'text_one_suffix', __( 'Text after 1 Count', 'i-recommend-this' ), array( $this, 'setting_text_one_suffix' ), 'irecommendthis-settings', 'irecommendthis' );

		add_settings_field( 'text_more_suffix', __( 'Text after more than 1 Count', 'i-recommend-this' ), array( $this, 'setting_text_more_suffix' ), 'irecommendthis-settings', 'irecommendthis' );

		add_settings_field( 'link_title_new', __( 'Title for New posts', 'i-recommend-this' ), array( $this, 'setting_link_title_new' ), 'irecommendthis-settings', 'irecommendthis' );

		add_settings_field( 'link_title_active', __( 'Title for already voted posts', 'i-recommend-this' ), array( $this, 'setting_link_title_active' ), 'irecommendthis-settings', 'irecommendthis' );

		add_settings_field( 'disable_css', __( 'Disable CSS', 'i-recommend-this' ), array( $this, 'setting_disable_css' ), 'irecommendthis-settings', 'irecommendthis' );

		add_settings_field( 'hide_zero', __( 'Hide Zero Count', 'i-recommend-this' ), array( $this, 'setting_hide_zero' ), 'irecommendthis-settings', 'irecommendthis' );

		add_settings_field( 'enable_unique_ip', __( 'Enable IP saving', 'i-recommend-this' ), array( $this, 'setting_enable_unique_ip' ), 'irecommendthis-settings', 'irecommendthis' );

		add_settings_field( 'recommend_style', __( 'Choose a style', 'i-recommend-this' ), array( $this, 'setting_recommend_style' ), 'irecommendthis-settings', 'irecommendthis' );
	}

	/**
	 * Display the settings page with tabs.
	 */
	public function render_settings_page() {
		$tabs = array(
			'general' => __( 'General', 'i-recommend-this' ),
			'dbtools' => __( 'DB Tools', 'i-recommend-this' ),
		);
		?>
		<div id="irecommendthis-settings" class="wrap irecommendthis-settings">
			<h1><?php esc_html_e( 'I Recommend This: Settings', 'i-recommend-this' ); ?></h1>

			<h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs as $tab => $name ) : ?>
					<a href="<?php echo esc_url( admin_url( 'options-general.php?page=irecommendthis-settings&tab=' . $tab ) ); ?>"
					   class="nav-tab <?php echo $this->current_tab === $tab ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $name ); ?>
					</a>
				<?php endforeach; ?>
			</h2>

			<div class="tab-content">
				<?php
				if ( $this->current_tab === 'general' ) {
					include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/settings-page-general.php';
				} elseif ( $this->current_tab === 'dbtools' ) {
					include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/settings-page-dbtools.php';
				}
				?>
			</div>

			<?php if ( $this->current_tab === 'general' ) : ?>
				<?php $this->plugin_review_notice(); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Introductory section text.
	 */
	public function section_intro() {
		?>
		<p><?php esc_html_e( 'This plugin allows your visitors to simply recommend or like your posts instead of commenting.', 'i-recommend-this' ); ?></p>
		<?php
	}

	/**
	 * Display the plugin review notice.
	 */
	public function plugin_review_notice() {
		echo '<p>If you enjoy using <strong>I Recommend this</strong>, please <a href="https://wordpress.org/support/view/plugin-reviews/i-recommend-this?rate=5#postform" target="_blank">leave us a ★★★★★ rating</a>. A <strong style="text-decoration: underline;">huge</strong> thank you in advance!</p>';
	}

	/**
	 * Setting: Show On.
	 */
	public function setting_show_on() {
		// Get both old and new option names for backward compatibility
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );

		if ( ! isset( $options['add_to_posts'] ) ) {
			$options['add_to_posts'] = '0';
		}
		if ( ! isset( $options['add_to_other'] ) ) {
			$options['add_to_other'] = '0';
		}

		echo '<input type="hidden" name="irecommendthis_settings[add_to_posts]" value="0" />
		<label><input type="checkbox" name="irecommendthis_settings[add_to_posts]" value="1"' . checked( $options['add_to_posts'], '1', false ) . ' />
		' . esc_html__( 'Posts', 'i-recommend-this' ) . '</label><br />
		<input type="hidden" name="irecommendthis_settings[add_to_other]" value="0" />
		<label><input type="checkbox" name="irecommendthis_settings[add_to_other]" value="1"' . checked( $options['add_to_other'], '1', false ) . ' />
		' . esc_html__( 'All other pages like Index, Archive, etc.', 'i-recommend-this' ) . '</label><br />';
	}

	/**
	 * Setting: Hide Zero Count.
	 */
	public function setting_hide_zero() {
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );
		if ( ! isset( $options['hide_zero'] ) ) {
			$options['hide_zero'] = '0';
		}

		echo '<input type="hidden" name="irecommendthis_settings[hide_zero]" value="0" />
		<label><input type="checkbox" name="irecommendthis_settings[hide_zero]" value="1"' . checked( $options['hide_zero'], '1', false ) . ' />' .
			esc_html__( 'Hide count if count is zero', 'i-recommend-this' ) . '</label>';
	}

	/**
	 * Setting: Enable Unique IP.
	 */
	public function setting_enable_unique_ip() {
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );
		if ( ! isset( $options['enable_unique_ip'] ) ) {
			$options['enable_unique_ip'] = '0';
		}

		echo '<input type="hidden" name="irecommendthis_settings[enable_unique_ip]" value="0" />
		<label><input type="checkbox" name="irecommendthis_settings[enable_unique_ip]" value="1"' . checked( $options['enable_unique_ip'], '1', false ) . ' />' .
			esc_html__( 'Enable saving of IP Address (will affect GDPR). Cookies are saved by default but enabling this option will save IP & cookies to track user votes and a user be blocked from saving same post multiple times.', 'i-recommend-this' ) . '</label>';
	}

	/**
	 * Setting: Disable CSS.
	 */
	public function setting_disable_css() {
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );
		if ( ! isset( $options['disable_css'] ) ) {
			$options['disable_css'] = '0';
		}

		echo '<input type="hidden" name="irecommendthis_settings[disable_css]" value="0" />
		<label><input type="checkbox" name="irecommendthis_settings[disable_css]" value="1"' . checked( $options['disable_css'], '1', false ) . ' />' .
			esc_html__( 'I want to use my own CSS styles', 'i-recommend-this' ) . '</label>';
	}

	/**
	 * Setting: Text Zero Suffix.
	 */
	public function setting_text_zero_suffix() {
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );
		if ( ! isset( $options['text_zero_suffix'] ) ) {
			$options['text_zero_suffix'] = '';
		}

		echo '<input type="text" name="irecommendthis_settings[text_zero_suffix]" class="regular-text" value="' . esc_attr( $options['text_zero_suffix'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Text to display after zero count. Leave blank for no text after the count.', 'i-recommend-this' ) . '</span>';
	}

	/**
	 * Setting: Text One Suffix.
	 */
	public function setting_text_one_suffix() {
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );
		if ( ! isset( $options['text_one_suffix'] ) ) {
			$options['text_one_suffix'] = '';
		}

		echo '<input type="text" name="irecommendthis_settings[text_one_suffix]" class="regular-text" value="' . esc_attr( $options['text_one_suffix'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Text to display after 1 person has recommended. Leave blank for no text after the count.', 'i-recommend-this' ) . '</span>';
	}

	/**
	 * Setting: Text More Suffix.
	 */
	public function setting_text_more_suffix() {
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );
		if ( ! isset( $options['text_more_suffix'] ) ) {
			$options['text_more_suffix'] = '';
		}

		echo '<input type="text" name="irecommendthis_settings[text_more_suffix]" class="regular-text" value="' . esc_attr( $options['text_more_suffix'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Text to display after more than 1 person have recommended. Leave blank for no text after the count.', 'i-recommend-this' ) . '</span>';
	}

	/**
	 * Setting: Link Title for New Posts.
	 */
	public function setting_link_title_new() {
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );
		if ( ! isset( $options['link_title_new'] ) ) {
			$options['link_title_new'] = '';
		}

		echo '<input type="text" name="irecommendthis_settings[link_title_new]" class="regular-text" value="' . esc_attr( $options['link_title_new'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Link Title element for posts not yet voted by a user.', 'i-recommend-this' ) . '</span>';
	}

	/**
	 * Setting: Link Title for Already Voted Posts.
	 */
	public function setting_link_title_active() {
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );
		if ( ! isset( $options['link_title_active'] ) ) {
			$options['link_title_active'] = '';
		}

		echo '<input type="text" name="irecommendthis_settings[link_title_active]" class="regular-text" value="' . esc_attr( $options['link_title_active'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Link Title element for posts already voted by a user.', 'i-recommend-this' ) . '</span>';
	}

	/**
	 * Setting: Recommend Style.
	 */
	public function setting_recommend_style() {
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );
		if ( ! isset( $options['recommend_style'] ) ) {
			$options['recommend_style'] = '0';
		}

		echo '<label><input type="radio" name="irecommendthis_settings[recommend_style]" value="0"' . checked( $options['recommend_style'], '0', false ) . ' />
		' . esc_html__( 'Default style - Thumb', 'i-recommend-this' ) . '</label><br />

		<label><input type="radio" name="irecommendthis_settings[recommend_style]" value="1"' . checked( $options['recommend_style'], '1', false ) . ' />
		' . esc_html__( 'Heart', 'i-recommend-this' ) . '</label><br />';
	}

	/**
	 * Validate settings.
	 *
	 * @param array $input The input array to validate.
	 *
	 * @return array The validated input array.
	 */
	public function settings_validate( $input ) {
		$input['show_on']           = ! empty( $input['show_on'] ) ? $input['show_on'] : '0';
		$input['text_zero_suffix']  = sanitize_text_field( $input['text_zero_suffix'] ?? '' );
		$input['text_one_suffix']   = sanitize_text_field( $input['text_one_suffix'] ?? '' );
		$input['text_more_suffix']  = sanitize_text_field( $input['text_more_suffix'] ?? '' );
		$input['link_title_new']    = sanitize_text_field( $input['link_title_new'] ?? '' );
		$input['link_title_active'] = sanitize_text_field( $input['link_title_active'] ?? '' );
		$input['disable_css']       = ! empty( $input['disable_css'] ) ? '1' : '0';
		$input['hide_zero']         = ! empty( $input['hide_zero'] ) ? '1' : '0';
		$input['enable_unique_ip']  = ! empty( $input['enable_unique_ip'] ) ? '1' : '0';
		$input['recommend_style']   = ! empty( $input['recommend_style'] ) ? '1' : '0';

		return $input;
	}

	/**
	 * Setup recommends for a post.
	 *
	 * @param int $post_id The post ID.
	 */
	public function setup_recommends( $post_id ) {
		if ( ! is_numeric( $post_id ) ) {
			return;
		}

		add_post_meta( $post_id, '_recommended', '0', true );
	}

	/**
	 * Add Likes column to post list table.
	 *
	 * @param array $defaults The existing columns.
	 *
	 * @return array The modified columns.
	 */
	public function columns_head( $defaults ) {
		$defaults['likes'] = __( 'Likes', 'i-recommend-this' );
		return $defaults;
	}

	/**
	 * Display content in the Likes column.
	 *
	 * @param string $column_name The name of the column.
	 * @param int    $post_ID     The post ID.
	 */
	public function column_content( $column_name, $post_ID ) {
		if ( 'likes' === $column_name ) {
			echo esc_html( get_post_meta( $post_ID, '_recommended', true ) ) . ' ' . esc_html__( 'like', 'i-recommend-this' );
		}
	}

	/**
	 * Make the Likes column sortable.
	 *
	 * @param array $columns The existing sortable columns.
	 *
	 * @return array The modified sortable columns.
	 */
	public function column_register_sortable( $columns ) {
		$columns['likes'] = 'likes';
		return $columns;
	}

	/**
	 * Handle sorting by the Likes column.
	 *
	 * @param array $vars The query variables.
	 *
	 * @return array The modified query variables.
	 */
	public function column_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && 'likes' === $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_recommended', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'orderby'  => 'meta_value_num',
				)
			);
		}

		return $vars;
	}

	/**
	 * Handle the database update request from form submission.
	 */
	public function handle_database_update() {
		global $themeist_i_recommend_this;

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
		$result = $themeist_i_recommend_this->update();

		// Create a nonce for the redirect.
		$updated_nonce = wp_create_nonce( 'irecommendthis_update_success' );

		// Redirect with success message and nonce.
		$redirect_url = add_query_arg(
			array(
				'page'          => 'irecommendthis-settings',
				'tab'           => 'dbtools',
				'updated'       => '1',
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
