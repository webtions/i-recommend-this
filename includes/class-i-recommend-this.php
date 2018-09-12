<?php

class Themeist_IRecommendThis {

	protected $plugin_slug;

	protected $version;

	protected $db_version;

	public $plugin_file;

	public function __construct( $plugin_file ) {

		$this->plugin_file = $plugin_file;

		if ( defined( 'THEMEIST_IRT_VERSION' ) ) {
			$this->version = THEMEIST_IRT_VERSION;
		} else {
			$this->version = '3.8.3';
		}

		if ( defined( 'THEMEIST_IRT_DB_VERSION' ) ) {
			$this->db_version = THEMEIST_IRT_DB_VERSION;
		} else {
			$this->db_version = '2.6.2';
		}

		$this->plugin_slug = 'i-recommend-this';


	}

	public function add_hooks() {
		// Run this on activation / deactivation
		register_activation_hook($this->plugin_file, array($this, 'activate'));

		add_action('init', array($this, 'load_localisation'), 0);
		//add_action( 'plugins_loaded', 'i_recommend_this_load_plugin_textdomain' );
		//add_action( 'plugins_loaded', array( $this, 'i_recommend_this_load_plugin_textdomain' ), 0 );
	}

	/*--------------------------------------------*
	 * Activate
	 *--------------------------------------------*/

	public function activate($network_wide) {
		if (!isset($wpdb)) $wpdb = $GLOBALS['wpdb'];
		global $wpdb;

		$table_name = $wpdb->prefix . "irecommendthis_votes";
		if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
			$sql = "CREATE TABLE " . $table_name . " (
				id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
				time TIMESTAMP NOT NULL,
				post_id BIGINT(20) NOT NULL,
				ip VARCHAR(45) NOT NULL,
				UNIQUE KEY id (id)
			);";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			dbDelta($sql);

			$this->register_plugin_version();

			if ($this->db_version != '') {
				update_option('dot_irecommendthis_db_version', $this->db_version);
			}
		}
	}

	public function register_plugin_version() {
		if ($this->version != '') {
			update_option('dot-irecommendthis' . '-version', $this->version);
		}
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.4.6
	 */
	public function load_localisation() {
		load_plugin_textdomain('i-recommend-this', false, dirname(plugin_basename($this->plugin_file)) . '/languages/');
	}

	public function get_version() {
		return $this->version;
	}

}