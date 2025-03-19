<?php
/**
 * Main class for the I Recommend This plugin.
 *
 * @package IRecommendThis
 * @subpackage Core
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
	 * Path to the main plugin file.
	 *
	 * @var string
	 */
	public $plugin_file;

	/**
	 * Database upgrader instance.
	 *
	 * @var Themeist_IRecommendThis_DB_Upgrader
	 */
	private $db_upgrader;

	/**
	 * Constructor to initialize the plugin.
	 *
	 * @param string $plugin_file Path to the main plugin file.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
		$this->version     = defined( 'THEMEIST_IRT_VERSION' ) ? THEMEIST_IRT_VERSION : '4.0.0';
		$this->plugin_slug = 'i-recommend-this';

		// Initialize DB upgrader.
		$this->db_upgrader = new Themeist_IRecommendThis_DB_Upgrader( $this );
	}

	/**
	 * Add hooks for plugin actions and filters.
	 */
	public function add_hooks() {
		// Register activation hook.
		register_activation_hook( $this->plugin_file, array( $this, 'activate' ) );

		// Initialize the database upgrader.
		$this->db_upgrader->init();

		// Load text domain.
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		// Migrate plugin settings.
		add_action( 'init', array( $this, 'migrate_plugin_settings' ) );

		// Check for database issues.
		add_action( 'admin_notices', array( $this, 'check_db_table' ) );

		/**
		 * Action fired when the plugin has finished initializing.
		 *
		 * @since 4.0.0
		 * @param Themeist_IRecommendThis $this Plugin instance.
		 */
		do_action( 'irecommendthis_init', $this );
	}

	/**
	 * Activate the plugin.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 */
	public function activate( $network_wide ) {
		// Migrate settings first.
		$this->migrate_plugin_settings();

		/**
		 * Action fired before plugin activation process begins.
		 *
		 * @since 4.0.0
		 * @param bool $network_wide Whether the plugin is being activated network-wide.
		 */
		do_action( 'irecommendthis_before_activation', $network_wide );

		if ( is_multisite() && $network_wide ) {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$this->db_upgrader->create_table();
				restore_current_blog();
			}
		} else {
			$this->db_upgrader->create_table();
		}

		// Register plugin version.
		$this->register_plugin_version();

		/**
		 * Action fired after plugin activation is complete.
		 *
		 * @since 4.0.0
		 * @param bool $network_wide Whether the plugin is being activated network-wide.
		 */
		do_action( 'irecommendthis_after_activation', $network_wide );
	}

	/**
	 * Migrate plugin settings from old to new option keys.
	 *
	 * This method handles the transition from old option names to new ones
	 * to ensure a smooth upgrade path for existing users.
	 */
	public function migrate_plugin_settings() {
		// Check if old settings exist.
		$old_settings = get_option( 'dot_irecommendthis_settings' );

		if ( $old_settings ) {
			// Old settings exist - use them as the basis for the new settings.
			$current_settings = get_option( 'irecommendthis_settings', array() );

			// If current settings don't exist OR they're empty and old settings exist.
			if ( false === $current_settings || ( is_array( $current_settings ) && empty( $current_settings ) ) ) {
				// Copy old settings to new option.
				update_option( 'irecommendthis_settings', $old_settings );

				/**
				 * Action fired after plugin settings have been migrated from old format.
				 *
				 * @since 4.0.0
				 * @param array $old_settings The old plugin settings.
				 * @param array $new_settings The new plugin settings.
				 */
				do_action( 'irecommendthis_settings_migrated', $old_settings, $old_settings );
			}
		} else {
			// No old settings exist, ensure defaults exist for new installs.
			$current_settings = get_option( 'irecommendthis_settings' );

			if ( false === $current_settings ) {
				// Initialize with defaults for a new installation.
				$default_settings = array(
					'add_to_posts'      => '0',
					'add_to_other'      => '0',
					'text_zero_suffix'  => 'Like this',
					'text_one_suffix'   => 'Like',
					'text_more_suffix'  => 'Likes',
					'link_title_new'    => 'Like this',
					'link_title_active' => 'Unlike this',
					'disable_css'       => '0',
					'hide_zero'         => '1',
					'enable_unique_ip'  => '0',
					'recommend_style'   => '1',
				);
				add_option( 'irecommendthis_settings', $default_settings );

				/**
				 * Action fired after default plugin settings have been created.
				 *
				 * @since 4.0.0
				 * @param array $default_settings The default plugin settings.
				 */
				do_action( 'irecommendthis_default_settings_created', $default_settings );
			}//end if
		}//end if
	}

	/**
	 * Check database table and display notices.
	 */
	public function check_db_table() {
		if ( get_option( 'irecommendthis_db_error' ) || ! $this->db_upgrader->table_exists() ) {
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

	/**
	 * Get the database upgrader instance.
	 *
	 * @return Themeist_IRecommendThis_DB_Upgrader Database upgrader instance.
	 */
	public function get_db_upgrader() {
		return $this->db_upgrader;
	}
}
