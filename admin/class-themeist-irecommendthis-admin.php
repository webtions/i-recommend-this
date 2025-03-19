<?php
/**
 * Main admin class for the I Recommend This plugin.
 *
 * Coordinates all admin functionality through composition of specialized components.
 *
 * @package IRecommendThis
 * @subpackage Admin
 * @since 4.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Main admin class that coordinates all admin functionality.
 */
class Themeist_IRecommendThis_Admin {

	/**
	 * The path to the main plugin file.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * The main plugin instance.
	 *
	 * @var Themeist_IRecommendThis
	 */
	private $plugin;

	/**
	 * Post columns component instance.
	 *
	 * @var Themeist_IRecommendThis_Admin_Post_Columns
	 */
	private $post_columns_component;

	/**
	 * Settings component instance.
	 *
	 * @var Themeist_IRecommendThis_Admin_Settings
	 */
	private $settings_component;

	/**
	 * DB Tools component instance.
	 *
	 * @var Themeist_IRecommendThis_Admin_DB_Tools
	 */
	private $db_tools_component;

	/**
	 * UI component instance.
	 *
	 * @var Themeist_IRecommendThis_Admin_UI
	 */
	private $ui_component;

	/**
	 * Plugin links component instance.
	 *
	 * @var Themeist_IRecommendThis_Admin_Plugin_Links
	 */
	private $plugin_links_component;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_file Path to the main plugin file.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;

		// Include component classes.
		$this->include_components();

		// Initialize components.
		$this->initialize_components();
	}

	/**
	 * Include component classes.
	 */
	private function include_components() {
		require_once __DIR__ . '/class-themeist-irecommendthis-admin-post-columns.php';
		require_once __DIR__ . '/class-themeist-irecommendthis-admin-settings.php';
		require_once __DIR__ . '/class-themeist-irecommendthis-admin-db-tools.php';
		require_once __DIR__ . '/class-themeist-irecommendthis-admin-ui.php';
		require_once __DIR__ . '/class-themeist-irecommendthis-admin-plugin-links.php';
	}

	/**
	 * Initialize components.
	 */
	private function initialize_components() {
		global $themeist_i_recommend_this;
		$this->plugin = $themeist_i_recommend_this;

		// Create instances of component classes.
		$this->post_columns_component = new Themeist_IRecommendThis_Admin_Post_Columns();
		$this->settings_component     = new Themeist_IRecommendThis_Admin_Settings();
		$this->db_tools_component     = new Themeist_IRecommendThis_Admin_DB_Tools( $this->plugin );
		$this->plugin_links_component = new Themeist_IRecommendThis_Admin_Plugin_Links( $this->plugin_file );

		// The UI component needs references to other components.
		$this->ui_component = new Themeist_IRecommendThis_Admin_UI(
			$this->settings_component,
			$this->db_tools_component
		);
	}

	/**
	 * Add admin hooks.
	 */
	public function add_admin_hooks() {
		// Initialize all components.
		$this->post_columns_component->initialize();
		$this->settings_component->initialize();
		$this->db_tools_component->initialize();
		$this->ui_component->initialize();
		$this->plugin_links_component->initialize();

		// Add the menu item.
		add_action( 'admin_menu', array( $this, 'add_settings_menu' ) );

		// Setup recommends for new posts.
		add_action( 'publish_post', array( $this, 'setup_recommends' ) );
	}

	/**
	 * Add the settings menu item.
	 */
	public function add_settings_menu() {
		$page_title = __( 'I Recommend This', 'i-recommend-this' );
		$menu_title = __( 'I Recommend This', 'i-recommend-this' );
		$capability = 'manage_options';
		$menu_slug  = 'irecommendthis-settings';
		$function   = array( $this->ui_component, 'render_settings_page' );

		add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function );
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
}
