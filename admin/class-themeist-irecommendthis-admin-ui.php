<?php
/**
 * UI component for admin functionality.
 *
 * Handles rendering of admin pages, tabs, and notices.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle admin UI display.
 */
class Themeist_IRecommendThis_Admin_UI {

	/**
	 * The settings component instance.
	 *
	 * @var Themeist_IRecommendThis_Admin_Settings
	 */
	private $settings_component;

	/**
	 * The DB Tools component instance.
	 *
	 * @var Themeist_IRecommendThis_Admin_DB_Tools
	 */
	private $db_tools_component;

	/**
	 * Current tab being viewed.
	 *
	 * @var string
	 */
	private $current_tab;

	/**
	 * Constructor.
	 *
	 * @param Themeist_IRecommendThis_Admin_Settings $settings_component Settings component instance.
	 * @param Themeist_IRecommendThis_Admin_DB_Tools $db_tools_component DB Tools component instance.
	 */
	public function __construct( $settings_component, $db_tools_component ) {
		$this->settings_component = $settings_component;
		$this->db_tools_component = $db_tools_component;
		$this->current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
	}

	/**
	 * Initialize the component.
	 */
	public function initialize() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'settings_page_irecommendthis_settings' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'irecommendthis-admin-settings',
			plugins_url( 'assets/css/admin-settings.csdfs', dirname( __FILE__ ) ),
			array(),
			THEMEIST_IRT_VERSION
		);

		wp_enqueue_script(
			'irecommendthis-admin-tabs',
			plugins_url( 'assets/js/admin-tabs.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			THEMEIST_IRT_VERSION,
			true
		);
	}

	/**
	 * Render the settings page.
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

			<?php
			// Display database updated notice if applicable
			if ( isset( $_GET['db_updated'] ) && check_admin_referer( 'irecommendthis_update_success', 'updated_nonce' ) ) {
				if ( '1' === sanitize_text_field( wp_unslash( $_GET['db_updated'] ) ) ) {
					?>
					<div class="notice notice-success is-dismissible">
						<p><?php esc_html_e( 'Database has been updated successfully!', 'i-recommend-this' ); ?></p>
					</div>
					<?php
				}
			}
			?>

			<div class="tab-content">
				<?php
				if ( $this->current_tab === 'general' ) {
					$this->render_general_tab();
				} elseif ( $this->current_tab === 'dbtools' ) {
					$this->render_dbtools_tab();
				}
				?>
			</div>

			<?php if ( $this->current_tab === 'general' ) : ?>
				<?php $this->display_plugin_review_notice(); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render the general settings tab.
	 */
	private function render_general_tab() {
		?>
		<form method="post" action="options.php" class="irecommendthis-settings-form">
			<?php settings_fields( 'irecommendthis_settings' ); ?>
			<?php do_settings_sections( 'irecommendthis_settings' ); ?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'i-recommend-this' ); ?>"/>
			</p>
		</form>
		<?php
	}

	/**
	 * Render the database tools tab.
	 */
	private function render_dbtools_tab() {
		?>
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
		</div>

		<div class="card">
			<h2><?php esc_html_e( 'Database Information', 'i-recommend-this' ); ?></h2>
			<?php $this->db_tools_component->display_database_info(); ?>
		</div>
		<?php
	}

	/**
	 * Display the plugin review notice.
	 */
	public function display_plugin_review_notice() {
		echo '<p>If you enjoy using <strong>I Recommend this</strong>, please <a href="https://wordpress.org/support/view/plugin-reviews/i-recommend-this?rate=5#postform" target="_blank">leave us a ★★★★★ rating</a>. A <strong style="text-decoration: underline;">huge</strong> thank you in advance!</p>';
	}
}
