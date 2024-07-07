<?php
/**
 * Class Themeist_IRecommendThis_Admin
 *
 * Handles admin-specific functionality for the I Recommend This plugin.
 */
class Themeist_IRecommendThis_Admin {

	/**
	 * The path to the main plugin file.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_file Path to the main plugin file.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
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

		add_action( 'admin_menu', array( $this, 'dot_irecommendthis_menu' ) );
		add_action( 'admin_init', array( $this, 'dot_irecommendthis_settings' ) );
		add_action( 'publish_post', array( $this, 'dot_setup_recommends' ) );

		add_filter( 'request', array( $this, 'dot_column_orderby' ) );
		add_filter( 'manage_edit-post_sortable_columns', array( $this, 'dot_column_register_sortable' ) );
		add_filter( 'manage_posts_columns', array( $this, 'dot_columns_head' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'dot_column_content' ), 10, 2 );
	}

	/**
	 * Add the I Recommend This menu item to the WordPress admin menu.
	 */
	public function dot_irecommendthis_menu() {
		$page_title = __( 'I Recommend This', 'i-recommend-this' );
		$menu_title = __( 'I Recommend This', 'i-recommend-this' );
		$capability = 'manage_options';
		$menu_slug  = 'dot-irecommendthis';
		$function   = array( &$this, 'dot_settings_page' );

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
			$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=dot-irecommendthis' ) ) . '">' . __( 'Settings', 'dot-irecommendthis' ) . '</a>';
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
	public function dot_irecommendthis_settings() {
		register_setting( 'dot-irecommendthis', 'dot_irecommendthis_settings', array( &$this, 'settings_validate' ) );

		add_settings_section( 'dot-irecommendthis', '', array( &$this, 'section_intro' ), 'dot-irecommendthis' );

		add_settings_field( 'show_on', __( 'Automatically display on', 'i-recommend-this' ), array( &$this, 'setting_show_on' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'text_zero_suffix', __( 'Text after 0 Count', 'i-recommend-this' ), array( &$this, 'setting_text_zero_suffix' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'text_one_suffix', __( 'Text after 1 Count', 'i-recommend-this' ), array( &$this, 'setting_text_one_suffix' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'text_more_suffix', __( 'Text after more than 1 Count', 'i-recommend-this' ), array( &$this, 'setting_text_more_suffix' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'link_title_new', __( 'Title for New posts', 'i-recommend-this' ), array( &$this, 'setting_link_title_new' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'link_title_active', __( 'Title for already voted posts', 'i-recommend-this' ), array( &$this, 'setting_link_title_active' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'disable_css', __( 'Disable CSS', 'i-recommend-this' ), array( &$this, 'setting_disable_css' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'hide_zero', __( 'Hide Zero Count', 'i-recommend-this' ), array( &$this, 'setting_hide_zero' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'disable_unique_ip', __( 'Enable IP saving', 'i-recommend-this' ), array( &$this, 'setting_enable_unique_ip' ), 'dot-irecommendthis', 'dot-irecommendthis' );

		add_settings_field( 'recommend_style', __( 'Choose a style', 'i-recommend-this' ), array( &$this, 'setting_recommend_style' ), 'dot-irecommendthis', 'dot-irecommendthis' );
	}

	/**
	 * Display the settings page.
	 */
	public function dot_settings_page() {
		?>
		<div id="irecommendthis-settings" class="wrap irecommendthis-settings">
			<h1><?php esc_html_e( 'I Recommend This: Settings', 'i-recommend-this' ); ?></h1>
			<form action="options.php" method="post">
				<?php settings_fields( 'dot-irecommendthis' ); ?>
				<?php do_settings_sections( 'dot-irecommendthis' ); ?>
				<p class="submit"><input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'i-recommend-this' ); ?>"/></p>
			</form>
			<?php $this->plugin_review_notice(); ?>
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
		$options = get_option( 'dot_irecommendthis_settings' );
		if ( ! isset( $options['add_to_posts'] ) ) {
			$options['add_to_posts'] = '0';
		}
		if ( ! isset( $options['add_to_other'] ) ) {
			$options['add_to_other'] = '0';
		}

		echo '<input type="hidden" name="dot_irecommendthis_settings[add_to_posts]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[add_to_posts]" value="1"' . checked( $options['add_to_posts'], '1', false ) . ' />
		' . esc_html__( 'Posts', 'i-recommend-this' ) . '</label><br />
		<input type="hidden" name="dot_irecommendthis_settings[add_to_other]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[add_to_other]" value="1"' . checked( $options['add_to_other'], '1', false ) . ' />
		' . esc_html__( 'All other pages like Index, Archive, etc.', 'i-recommend-this' ) . '</label><br />';
	}

	/**
	 * Setting: Hide Zero Count.
	 */
	public function setting_hide_zero() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if ( ! isset( $options['hide_zero'] ) ) {
			$options['hide_zero'] = '0';
		}

		echo '<input type="hidden" name="dot_irecommendthis_settings[hide_zero]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[hide_zero]" value="1"' . checked( $options['hide_zero'], '1', false ) . ' />' .
			esc_html__( 'Hide count if count is zero', 'i-recommend-this' ) . '</label>';
	}

	/**
	 * Setting: Enable Unique IP.
	 */
	public function setting_enable_unique_ip() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if ( ! isset( $options['enable_unique_ip'] ) ) {
			$options['enable_unique_ip'] = '0';
		}

		echo '<input type="hidden" name="dot_irecommendthis_settings[enable_unique_ip]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[enable_unique_ip]" value="1"' . checked( $options['enable_unique_ip'], '1', false ) . ' />' .
			esc_html__( 'Enable saving of IP Address (will affect GDPR). Cookies are saved by default but enabling this option will save IP & cookies to track user votes and a user be blocked from saving same post multiple times.', 'i-recommend-this' ) . '</label>';
	}

	/**
	 * Setting: Disable CSS.
	 */
	public function setting_disable_css() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if ( ! isset( $options['disable_css'] ) ) {
			$options['disable_css'] = '0';
		}

		echo '<input type="hidden" name="dot_irecommendthis_settings[disable_css]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[disable_css]" value="1"' . checked( $options['disable_css'], '1', false ) . ' />' .
			esc_html__( 'I want to use my own CSS styles', 'i-recommend-this' ) . '</label>';
	}

	/**
	 * Setting: Text Zero Suffix.
	 */
	public function setting_text_zero_suffix() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if ( ! isset( $options['text_zero_suffix'] ) ) {
			$options['text_zero_suffix'] = '';
		}

		echo '<input type="text" name="dot_irecommendthis_settings[text_zero_suffix]" class="regular-text" value="' . esc_attr( $options['text_zero_suffix'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Text to display after zero count. Leave blank for no text after the count.', 'i-recommend-this' ) . '</span>';
	}

	/**
	 * Setting: Text One Suffix.
	 */
	public function setting_text_one_suffix() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if ( ! isset( $options['text_one_suffix'] ) ) {
			$options['text_one_suffix'] = '';
		}

		echo '<input type="text" name="dot_irecommendthis_settings[text_one_suffix]" class="regular-text" value="' . esc_attr( $options['text_one_suffix'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Text to display after 1 person has recommended. Leave blank for no text after the count.', 'i-recommend-this' ) . '</span>';
	}

	/**
	 * Setting: Text More Suffix.
	 */
	public function setting_text_more_suffix() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if ( ! isset( $options['text_more_suffix'] ) ) {
			$options['text_more_suffix'] = '';
		}

		echo '<input type="text" name="dot_irecommendthis_settings[text_more_suffix]" class="regular-text" value="' . esc_attr( $options['text_more_suffix'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Text to display after more than 1 person have recommended. Leave blank for no text after the count.', 'i-recommend-this' ) . '</span>';
	}

	/**
	 * Setting: Link Title for New Posts.
	 */
	public function setting_link_title_new() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if ( ! isset( $options['link_title_new'] ) ) {
			$options['link_title_new'] = '';
		}

		echo '<input type="text" name="dot_irecommendthis_settings[link_title_new]" class="regular-text" value="' . esc_attr( $options['link_title_new'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Link Title element for posts not yet voted by a user.', 'i-recommend-this' ) . '</span>';
	}

	/**
	 * Setting: Link Title for Already Voted Posts.
	 */
	public function setting_link_title_active() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if ( ! isset( $options['link_title_active'] ) ) {
			$options['link_title_active'] = '';
		}

		echo '<input type="text" name="dot_irecommendthis_settings[link_title_active]" class="regular-text" value="' . esc_attr( $options['link_title_active'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Link Title element for posts already voted by a user.', 'i-recommend-this' ) . '</span>';
	}

	/**
	 * Setting: Recommend Style.
	 */
	public function setting_recommend_style() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if ( ! isset( $options['recommend_style'] ) ) {
			$options['recommend_style'] = '0';
		}

		echo '<label><input type="radio" name="dot_irecommendthis_settings[recommend_style]" value="0"' . checked( $options['recommend_style'], '0', false ) . ' />
		' . esc_html__( 'Default style - Thumb', 'i-recommend-this' ) . '</label><br />

		<label><input type="radio" name="dot_irecommendthis_settings[recommend_style]" value="1"' . checked( $options['recommend_style'], '1', false ) . ' />
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
	 * Setup recommends for a post.
	 *
	 * @param int $post_id The post ID.
	 */
	public function dot_setup_recommends( $post_id ) {
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
	public function dot_columns_head( $defaults ) {
		$defaults['likes'] = __( 'Likes', 'i-recommend-this' );
		return $defaults;
	}

	/**
	 * Display content in the Likes column.
	 *
	 * @param string $column_name The name of the column.
	 * @param int    $post_ID     The post ID.
	 */
	public function dot_column_content( $column_name, $post_ID ) {
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
	public function dot_column_register_sortable( $columns ) {
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
	public function dot_column_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && 'likes' === $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_recommended', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'orderby'  => 'meta_value',
				)
			);
		}

		return $vars;
	}
}
