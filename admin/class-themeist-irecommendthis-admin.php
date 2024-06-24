<?php
/**
 * Admin functionality for the I Recommend This plugin.
 *
 * @package Themeist_IRecommendThis
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class for handling plugin functionality in the WordPress admin area.
 */
class Themeist_IRecommendThis_Admin {

	/**
	 * The main plugin file.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Constructor for the admin class. Initializes the plugin.
	 *
	 * @param string $plugin_file The main plugin file.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Adds hooks for admin-related functionality.
	 */
	public function add_admin_hooks() {
		global $pagenow;

		if ( 'plugins.php' === $pagenow ) {
			add_filter( 'plugin_action_links', array( $this, 'add_plugin_settings_link' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links' ), 10, 2 );
		}

		// Add menu and settings page for the plugin.
		add_action( 'admin_menu', array( $this, 'dot_irecommendthis_menu' ) );
		add_action( 'admin_init', array( $this, 'dot_irecommendthis_settings' ) );

		// Setup recommendation functionality on post publish.
		add_action( 'publish_post', array( $this, 'dot_setup_recommends' ) );

		// Add hooks for column and sorting.
		add_filter( 'manage_posts_columns', array( $this, 'dot_columns_head' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'dot_column_content' ), 10, 2 );
		add_filter( 'manage_edit-post_sortable_columns', array( $this, 'dot_column_register_sortable' ) );
		add_filter( 'request', array( $this, 'dot_column_orderby' ) );
	}

	/**
	 * Add the plugin's menu to the WordPress admin.
	 */
	public function dot_irecommendthis_menu() {
		$page_title = __( 'I Recommend This', 'i-recommend-this' );
		$menu_title = __( 'I Recommend This', 'i-recommend-this' );
		$capability = 'manage_options'; // Set the appropriate capability.
		$menu_slug  = 'dot-irecommendthis';
		$function   = array( $this, 'dot_settings_page' );
		add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function );
	}

	/**
	 * Add settings link to the Plugins overview for this plugin.
	 *
	 * @param array  $links Array of plugin action links.
	 * @param string $file  Plugin file name.
	 * @return array Modified array of action links.
	 */
	public function add_plugin_settings_link( $links, $file ) {
		if ( plugin_basename( $this->plugin_file ) === $file ) {

			$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=dot-irecommendthis' ) ) . '">' . __( 'Settings', 'i-recommend-this' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	/**
	 * Add custom meta links to the plugin on the Plugins overview page.
	 *
	 * @param array  $links Array of plugin meta links.
	 * @param string $file  Plugin file name.
	 * @return array Modified array of meta links.
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
	 * Setup recommended status for posts upon publishing.
	 *
	 * @param int $post_id Post ID.
	 */
	public function dot_setup_recommends( $post_id ) {
		if ( ! is_numeric( $post_id ) ) {
			return;
		}

		add_post_meta( $post_id, '_recommended', '0', true );
	}

	/**
	 * Register settings and add fields for the plugin settings page.
	 */
	public function dot_irecommendthis_settings() {
		register_setting( 'dot-irecommendthis', 'dot_irecommendthis_settings', array( $this, 'settings_validate' ) );

		add_settings_section( 'dot-irecommendthis', '', array( $this, 'section_intro' ), 'dot-irecommendthis' );

		add_settings_field( 'show_on', __( 'Automatically display on', 'i-recommend-this' ), array( $this, 'setting_show_on' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'text_zero_suffix', __( 'Text after 0 Count', 'i-recommend-this' ), array( $this, 'setting_text_zero_suffix' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'text_one_suffix', __( 'Text after 1 Count', 'i-recommend-this' ), array( $this, 'setting_text_one_suffix' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'text_more_suffix', __( 'Text after more than 1 Count', 'i-recommend-this' ), array( $this, 'setting_text_more_suffix' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'link_title_new', __( 'Title for New posts', 'i-recommend-this' ), array( $this, 'setting_link_title_new' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'link_title_active', __( 'Title for already voted posts', 'i-recommend-this' ), array( $this, 'setting_link_title_active' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'disable_css', __( 'Disable CSS', 'i-recommend-this' ), array( $this, 'setting_disable_css' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'hide_zero', __( 'Hide Zero Count', 'i-recommend-this' ), array( $this, 'setting_hide_zero' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'enable_unique_ip', __( 'Enable IP saving', 'i-recommend-this' ), array( $this, 'setting_enable_unique_ip' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'recommend_style', __( 'Choose a style', 'i-recommend-this' ), array( $this, 'setting_recommend_style' ), 'dot-irecommendthis', 'dot-irecommendthis' );
	}

	/**
	 * Render the settings page for the plugin.
	 */
	public function dot_settings_page() {
		?>
		<div id="irecommendthis-settings" class="wrap irecommendthis-settings">
			<h1>I Recommend This: 
			<?php esc_html_e( 'Settings', 'i-recommend-this' ); ?></h1>
			<form method="post" action="options.php">
			<?php
			settings_fields( 'dot-irecommendthis' );
			do_settings_sections( 'dot-irecommendthis' );
			submit_button();

			// Display footer text below the submit button.
			$this->footer_text();
			?>
			</form>
		</div>
		<?php
	}


	/**
	 * Validate plugin settings before saving.
	 *
	 * @param array $input Array of input data.
	 * @return array Sanitized input data.
	 */
	public function settings_validate( $input ) {
		$input['show_on']           = ! empty( $input['show_on'] ) ? $input['show_on'] : '0';
		$input['text_zero_suffix']  = sanitize_text_field( $input['text_zero_suffix'] );
		$input['text_one_suffix']   = sanitize_text_field( $input['text_one_suffix'] );
		$input['text_more_suffix']  = sanitize_text_field( $input['text_more_suffix'] );
		$input['link_title_new']    = sanitize_text_field( $input['link_title_new'] );
		$input['link_title_active'] = sanitize_text_field( $input['link_title_active'] );
		$input['disable_css']       = ! empty( $input['disable_css'] ) ? '1' : '0';
		$input['hide_zero']         = ! empty( $input['hide_zero'] ) ? '1' : '0';
		$input['enable_unique_ip']  = ! empty( $input['enable_unique_ip'] ) ? '1' : '0';
		$input['recommend_style']   = ! empty( $input['recommend_style'] ) ? '1' : '0';

		return $input;
	}

	/**
	 * Display an intro section on the settings page.
	 */
	public function section_intro() {
		echo '<p>' . esc_html__( 'Configure settings for the I Recommend This plugin.', 'i-recommend-this' ) . '</p>';
	}

	/**
	 * Render the "Automatically display on" setting field.
	 */
	public function setting_show_on() {
		$options = get_option( 'dot_irecommendthis_settings' );

		// Set default values if options are not set.
		$add_to_posts = isset( $options['add_to_posts'] ) ? $options['add_to_posts'] : '0';
		$add_to_other = isset( $options['add_to_other'] ) ? $options['add_to_other'] : '0';

		?>
		<input type="hidden" name="dot_irecommendthis_settings[add_to_posts]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[add_to_posts]" value="1" <?php checked( $add_to_posts, '1' ); ?> />
		<?php esc_html_e( 'Posts', 'i-recommend-this' ); ?></label><br />
		
		<input type="hidden" name="dot_irecommendthis_settings[add_to_other]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[add_to_other]" value="1" <?php checked( $add_to_other, '1' ); ?> />
		<?php esc_html_e( 'All other pages like Index, Archive, etc.', 'i-recommend-this' ); ?></label><br />
		<?php
	}

	/**
	 * Render the "Text after 0 Count" setting field.
	 */
	public function setting_text_zero_suffix() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if ( ! isset( $options['text_zero_suffix'] ) ) {
			$options['text_zero_suffix'] = '';
		}

		?>
		<input type="text" name="dot_irecommendthis_settings[text_zero_suffix]" class="regular-text" value="<?php echo esc_attr( $options['text_zero_suffix'] ); ?>" /><br />
		<span class="description"><?php esc_html_e( 'Text to display after zero count. Leave blank for no text after the count.', 'i-recommend-this' ); ?></span>
		<?php
	}

	/**
	 * Render the "Text after 1 Count" setting field.
	 */
	public function setting_text_one_suffix() {
		$options         = get_option( 'dot_irecommendthis_settings' );
		$text_one_suffix = isset( $options['text_one_suffix'] ) ? $options['text_one_suffix'] : '';

		?>
		<input type="text" name="dot_irecommendthis_settings[text_one_suffix]" class="regular-text" value="<?php echo esc_attr( $text_one_suffix ); ?>" /><br />
		<span class="description">
			<?php esc_html_e( 'Text to display after 1 person has recommended. Leave blank for no text after the count.', 'i-recommend-this' ); ?>
		</span>
		<?php
	}

	/**
	 * Render the "Text after more than 1 Count" setting field.
	 */
	public function setting_text_more_suffix() {
		$options          = get_option( 'dot_irecommendthis_settings' );
		$text_more_suffix = isset( $options['text_more_suffix'] ) ? $options['text_more_suffix'] : '';

		?>
		<input type="text" name="dot_irecommendthis_settings[text_more_suffix]" class="regular-text" value="<?php echo esc_attr( $text_more_suffix ); ?>" /><br />
		<span class="description">
			<?php esc_html_e( 'Text to display after more than 1 person have recommended. Leave blank for no text after the count.', 'i-recommend-this' ); ?>
		</span>
		<?php
	}

	/**
	 * Render the "Title for New posts" setting field.
	 */
	public function setting_link_title_new() {
		$options        = get_option( 'dot_irecommendthis_settings' );
		$link_title_new = isset( $options['link_title_new'] ) ? $options['link_title_new'] : '';

		?>
	<input type="text" name="dot_irecommendthis_settings[link_title_new]" class="regular-text" value="<?php echo esc_attr( $link_title_new ); ?>" /><br />
	<span class="description"><?php esc_html_e( 'Link Title element for posts not yet voted by a user.', 'i-recommend-this' ); ?></span>
		<?php
	}

	/**
	 * Render the "Title for already voted posts" setting field.
	 */
	public function setting_link_title_active() {
		$options           = get_option( 'dot_irecommendthis_settings' );
		$link_title_active = isset( $options['link_title_active'] ) ? $options['link_title_active'] : '';

		?>
	<input type="text" name="dot_irecommendthis_settings[link_title_active]" class="regular-text" value="<?php echo esc_attr( $link_title_active ); ?>" /><br />
	<span class="description"><?php esc_html_e( 'Link Title element for posts already voted by a user.', 'i-recommend-this' ); ?></span>
		<?php
	}

	/**
	 * Render the "Disable CSS" setting field.
	 */
	public function setting_disable_css() {
		$options     = get_option( 'dot_irecommendthis_settings' );
		$disable_css = isset( $options['disable_css'] ) ? $options['disable_css'] : '0';

		?>
	<input type="hidden" name="dot_irecommendthis_settings[disable_css]" value="0" />
	<label>
		<input type="checkbox" name="dot_irecommendthis_settings[disable_css]" value="1" <?php checked( '1', $disable_css ); ?> />
		<?php esc_html_e( 'I want to use my own CSS styles', 'i-recommend-this' ); ?>
	</label>
		<?php
	}

	/**
	 * Render the "Hide Zero Count" setting field.
	 */
	public function setting_hide_zero() {
		$options   = get_option( 'dot_irecommendthis_settings' );
		$hide_zero = isset( $options['hide_zero'] ) ? $options['hide_zero'] : '0';

		?>
	<input type="hidden" name="dot_irecommendthis_settings[hide_zero]" value="0" />
	<label>
		<input type="checkbox" name="dot_irecommendthis_settings[hide_zero]" value="1" <?php checked( '1', $hide_zero ); ?> />
		<?php esc_html_e( 'Hide count if count is zero', 'i-recommend-this' ); ?>
	</label>
		<?php
	}

	/**
	 * Render the "Enable IP saving" setting field.
	 */
	public function setting_enable_unique_ip() {
		$options          = get_option( 'dot_irecommendthis_settings' );
		$enable_unique_ip = isset( $options['enable_unique_ip'] ) ? $options['enable_unique_ip'] : '0';

		?>
	<input type="hidden" name="dot_irecommendthis_settings[enable_unique_ip]" value="0" />
	<label>
		<input type="checkbox" name="dot_irecommendthis_settings[enable_unique_ip]" value="1" <?php checked( '1', $enable_unique_ip ); ?> />
		<?php esc_html_e( 'Enable saving of IP Address (will affect GDPR). Cookies are saved by default but enabling this option will save IP & cookies to track user votes and a user be blocked from saving same post multiple times.', 'i-recommend-this' ); ?>
	</label>
		<?php
	}

	/**
	 * Render the "Choose a style" setting field.
	 */
	public function setting_recommend_style() {
		$options         = get_option( 'dot_irecommendthis_settings' );
		$recommend_style = isset( $options['recommend_style'] ) ? $options['recommend_style'] : '0';

		?>
	<label>
		<input type="radio" name="dot_irecommendthis_settings[recommend_style]" value="0" <?php checked( '0', $recommend_style ); ?> />
		<?php esc_html_e( 'Default style - Thumb', 'i-recommend-this' ); ?>
	</label><br />

	<label>
		<input type="radio" name="dot_irecommendthis_settings[recommend_style]" value="1" <?php checked( '1', $recommend_style ); ?> />
		<?php esc_html_e( 'Heart', 'i-recommend-this' ); ?>
	</label><br />
		<?php
	}


	/**
	 * Display the footer text.
	 */
	public function footer_text() {
		// Construct the review URL with proper escaping.
		$review_url = esc_url( 'https://wordpress.org/support/view/plugin-reviews/i-recommend-this?rate=5#postform' );

		// Prepare the modified footer text.
		$text = sprintf(
			'If you enjoy using <strong>I Recommend this</strong>, please <a href="%s" target="_blank">leave us a &#9733;&#9733;&#9733;&#9733;&#9733; rating</a>. A <strong style="text-decoration: underline;">huge</strong> thank you in advance!',
			$review_url
		);

		// Output the footer text.
		echo '<div class="irecommendthis-footer-text">' . wp_kses_post( $text ) . '</div>';
	}

	/**
	 * Column header text.
	 *
	 * @param array $defaults Default column headers.
	 * @return array Modified column headers.
	 */
	public function dot_columns_head( $defaults ) {
		$defaults['likes'] = __( 'Likes', 'i-recommend-this' );
		return $defaults;
	}

	/**
	 * Column content.
	 *
	 * @param string $column_name Name of the column.
	 * @param int    $post_ID     Post ID.
	 * @param bool   $is_return   Whether to return the content (default: false).
	 * @return string|null Content if $is_return is true, null if output directly.
	 */
	public function dot_column_content( $column_name, $post_ID, $is_return = false ) {
		if ( 'likes' === $column_name ) {
			$content = get_post_meta( $post_ID, '_recommended', true ) . ' ' . __( 'like', 'i-recommend-this' );

			if ( $is_return ) {
				return esc_html( $content ); // Escape the output.
			} else {
				echo esc_html( $content ); // Escape the output.
			}
		}
	}
	/**
	 * Register sortable column.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function dot_column_register_sortable( $columns ) {
		$columns['likes'] = 'likes';
		return $columns;
	}

	/**
	 * Handle column sorting.
	 *
	 * @param array $vars The current query variables.
	 * @return array Modified query variables.
	 */
	public function dot_column_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && 'likes' === $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_recommended',
					'orderby'  => 'meta_value_num', // Use meta_value_num for numeric sorting.
				)
			);
		}
		return $vars;
	}
}