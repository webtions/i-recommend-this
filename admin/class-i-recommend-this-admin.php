<?php

class Themeist_IRecommendThis_Admin {

	/**
	 * @param string $plugin_file
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	public function add_admin_hooks() {
		global $pagenow;

		add_filter( 'admin_footer_text', array( $this, 'footer_text' ) );

		// Hooks for Plugins overview page
		if( $pagenow === 'plugins.php' ) {
			add_filter( 'plugin_action_links', array( $this, 'add_plugin_settings_link' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links'), 10, 2 );
		}

		add_action('admin_menu', array($this, 'dot_irecommendthis_menu'));
		add_action('admin_init', array($this, 'dot_irecommendthis_settings'));
		add_action('publish_post', array($this, 'dot_setup_recommends'));

		/*
		Enable with better control in 3.8.2
		add_filter('request', 'dot_column_orderby');
		add_filter('manage_edit-post_sortable_columns', 'dot_column_register_sortable');
		add_filter('manage_posts_columns', 'dot_columns_head');
		add_action('manage_posts_custom_column', 'dot_column_content', 10, 2);
		*/
	}

	public function dot_irecommendthis_menu() {
		$page_title = __('I Recommend This', 'i-recommend-this');
		$menu_title = __('I Recommend This', 'i-recommend-this');
		$capability = 'manage_options';
		$menu_slug = 'dot-irecommendthis';
		$function = array(&$this, 'dot_settings_page');
		add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);

	}

	/**
	 * Ask for a plugin review in the WP Admin footer, if this is one of the plugin pages.
	 *
	 * @param $text
	 *
	 * @return string
	 */
	public function footer_text( $text ) {

		if(! empty( $_GET['page'] ) && strpos( $_GET['page'], 'dot-irecommendthis' ) === 0 ) {
			$text = sprintf( 'If you enjoy using <strong>I Recommend this</strong>, please <a href="%s" target="_blank">leave us a ★★★★★ rating</a>. A <strong style="text-decoration: underline;">huge</strong> thank you in advance!', 'https://wordpress.org/support/view/plugin-reviews/i-recommend-this?rate=5#postform' );
		}

		return $text;
	}

	/**
	 * Add the settings link to the Plugins overview
	 *
	 * @param array $links
	 * @param       $file
	 *
	 * @return array
	 */
	public function add_plugin_settings_link( $links, $file ) {
		if ( $file == plugin_basename($this->plugin_file) ) {

			$settings_link = '<a href="' . admin_url( 'options-general.php?page=dot-irecommendthis' ) . '">'. __( 'Settings', 'dot-irecommendthis' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}


	public function add_plugin_meta_links( $links, $file ) {
		if ( strpos( $file, 'i-recommend-this.php' ) !== false ) {
			$new_links = array(
					'donate' => '<a href="https://www.paypal.me/harishchouhan" target="_blank">Donate</a>',
					'Documentation' => '<a href="https://themeist.com/docs/#utm_source=wp-plugin&utm_medium=i-recommend-this&utm_campaign=plugins-page" target="_blank">Documentation</a>'
				);

			$links = array_merge( $links, $new_links );
		}
		return $links;
	}


	/**
	 * Adds meta links to the plugin in the WP Admin > Plugins screen
	 *
	 * @param array $links
	 * @param string $file
	 *
	 * @return array
	 */
	public function add_plugin_meta_links2( $links, $file ) {
		if( $file !== $this->plugin_file ) {
		//if( 'i-recommend-this.php' !== $this->plugin_file ) {
			return $links;

			//$links[] = '<a href="https://themeist.com/docs/#utm_source=wp-plugin&utm_medium=i-recommend-this&utm_campaign=plugins-page">'. __( 'Documentation', 'i-recommend-this' ) . '</a>';
		}

		$links[] = '<a href="https://themeist.com/docs/#utm_source=wp-plugin&utm_medium=i-recommend-this&utm_campaign=plugins-page">'. __( '2Documentation', 'i-recommend-this' ) . '</a>';

		/**
		 * Filters meta links shown on the Plugins overview page
		 *
		 * This takes an array of strings
		 *
		 * @since 3.8
		 * @param array $links
		 * @ignore
		 */
		//$links = (array) apply_filters( 'themeist_irt_admin_plugin_meta_links', $links );

		return $links;
	}

/*  public function dot_irecommendthis_plugin_links($links)
	{
		return array_merge(
			array(
				'settings' => '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=dot-irecommendthis">' . __('Settings', 'dot-irecommendthis') . '</a>'
			),
			$links
		);
	}*/

	function dot_setup_recommends($post_id)
	{
		if (!is_numeric($post_id)) return;

		add_post_meta($post_id, '_recommended', '0', true);

	}

	public function dot_irecommendthis_settings() {
		register_setting('dot-irecommendthis', 'dot_irecommendthis_settings', array(&$this, 'settings_validate'));

		add_settings_section('dot-irecommendthis', '', array(&$this, 'section_intro'), 'dot-irecommendthis');

		add_settings_field('show_on', __('Automatically display on', 'i-recommend-this'), array(&$this, 'setting_show_on'), 'dot-irecommendthis', 'dot-irecommendthis');

		add_settings_field('text_zero_suffix', __('Text after 0 Count', 'i-recommend-this'), array(&$this, 'setting_text_zero_suffix'), 'dot-irecommendthis', 'dot-irecommendthis');

		add_settings_field('text_one_suffix', __('Text after 1 Count', 'i-recommend-this'), array(&$this, 'setting_text_one_suffix'), 'dot-irecommendthis', 'dot-irecommendthis');

		add_settings_field('text_more_suffix', __('Text after more than 1 Count', 'i-recommend-this'), array(&$this, 'setting_text_more_suffix'), 'dot-irecommendthis', 'dot-irecommendthis');

		add_settings_field('link_title_new', __('Title for New posts', 'i-recommend-this'), array(&$this, 'setting_link_title_new'), 'dot-irecommendthis', 'dot-irecommendthis');

		add_settings_field('link_title_active', __('Title for already voted posts', 'i-recommend-this'), array(&$this, 'setting_link_title_active'), 'dot-irecommendthis', 'dot-irecommendthis');

		add_settings_field('disable_css', __('Disable CSS', 'i-recommend-this'), array(&$this, 'setting_disable_css'), 'dot-irecommendthis', 'dot-irecommendthis');

		add_settings_field('hide_zero', __('Hide Zero Count', 'i-recommend-this'), array(&$this, 'setting_hide_zero'), 'dot-irecommendthis', 'dot-irecommendthis');

		add_settings_field('disable_unique_ip', __('Enable IP saving', 'i-recommend-this'), array(&$this, 'setting_enable_unique_ip'), 'dot-irecommendthis', 'dot-irecommendthis');

		add_settings_field('recommend_style', __('Choose a style', 'i-recommend-this'), array(&$this, 'setting_recommend_style'), 'dot-irecommendthis', 'dot-irecommendthis');


	}

	public function dot_settings_page() {
		?>
		<div id="irecommendthis-settings" class="wrap irecommendthis-settings">
			<h1>I Recommend This: Settings</h1>
			<form action="options.php" method="post">
				<?php settings_fields('dot-irecommendthis'); ?>
				<?php do_settings_sections('dot-irecommendthis'); ?>
				<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes', 'i-recommend-this'); ?>"/></p>
			</form>
		</div>
		<?php
	}

	public function section_intro() {
		?>

		<p><?php _e('This plugin allows your visitors to simply recommend or like your posts instead of commment it.', 'i-recommend-this'); ?></p>
		<?php
	}

	public function setting_show_on()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['add_to_posts'])) $options['add_to_posts'] = '0';
		if (!isset($options['add_to_other'])) $options['add_to_other'] = '0';

		echo '<input type="hidden" name="dot_irecommendthis_settings[add_to_posts]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[add_to_posts]" value="1"' . (($options['add_to_posts']) ? ' checked="checked"' : '') . ' />
		' . __('Posts', 'i-recommend-this') . '</label><br />
		<input type="hidden" name="dot_irecommendthis_settings[add_to_other]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[add_to_other]" value="1"' . (($options['add_to_other']) ? ' checked="checked"' : '') . ' />
		' . __('All other pages like Index, Archive, etc.', 'i-recommend-this') . '</label><br />';
	}

	public function setting_hide_zero()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['hide_zero'])) $options['hide_zero'] = '0';

		echo '<input type="hidden" name="dot_irecommendthis_settings[hide_zero]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[hide_zero]" value="1"' . (($options['hide_zero']) ? ' checked="checked"' : '') . ' />' .
			__('Hide count if count is zero', 'i-recommend-this') . '</label>';
	}

	public function setting_enable_unique_ip()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['enable_unique_ip'])) $options['enable_unique_ip'] = '0';

		echo '<input type="hidden" name="dot_irecommendthis_settings[enable_unique_ip]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[enable_unique_ip]" value="1"' . (($options['enable_unique_ip']) ? ' checked="checked"' : '') . ' />' .
			__('Enable saving of IP Address (will affect GDPR). Cookies are saved by default but enabling this option will save IP & cookies to track user votes and a user be blocked from saving same post multiple times.', 'i-recommend-this') . '</label>';
	}

	public function setting_disable_css()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['disable_css'])) $options['disable_css'] = '0';

		echo '<input type="hidden" name="dot_irecommendthis_settings[disable_css]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[disable_css]" value="1"' . (($options['disable_css']) ? ' checked="checked"' : '') . ' />' .
			__('I want to use my own CSS styles', 'i-recommend-this') . '</label>';
	}

	public function setting_text_zero_suffix()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['text_zero_suffix'])) $options['text_zero_suffix'] = '';

		echo '<input type="text" name="dot_irecommendthis_settings[text_zero_suffix]" class="regular-text" value="' . $options['text_zero_suffix'] . '" /><br />
		<span class="description">' . __('Text to display after zero count. Leave blank for no text after the count.', 'i-recommend-this') . '</span>';
	}

	public function setting_text_one_suffix()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['text_one_suffix'])) $options['text_one_suffix'] = '';

		echo '<input type="text" name="dot_irecommendthis_settings[text_one_suffix]" class="regular-text" value="' . $options['text_one_suffix'] . '" /><br />
		<span class="description">' . __('Text to display after 1 person has recommended. Leave blank for no text after the count.', 'i-recommend-this') . '</span>';
	}

	public function setting_text_more_suffix()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['text_more_suffix'])) $options['text_more_suffix'] = '';

		echo '<input type="text" name="dot_irecommendthis_settings[text_more_suffix]" class="regular-text" value="' . $options['text_more_suffix'] . '" /><br />
		<span class="description">' . __('Text to display after more than 1 person have recommended. Leave blank for no text after the count.', 'i-recommend-this') . '</span>';
	}

	public function setting_link_title_new()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['link_title_new'])) $options['link_title_new'] = '';

		echo '<input type="text" name="dot_irecommendthis_settings[link_title_new]" class="regular-text" value="' . $options['link_title_new'] . '" /><br />
		<span class="description">' . __('Link Title element for posts not yet voted by a user.', 'i-recommend-this') . '</span>';
	}

	public function setting_link_title_active()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['link_title_active'])) $options['link_title_active'] = '';

		echo '<input type="text" name="dot_irecommendthis_settings[link_title_active]" class="regular-text" value="' . $options['link_title_active'] . '" /><br />
		<span class="description">' . __('Link Title element for posts already voted by a user.', 'i-recommend-this') . '</span>';
	}

	public function setting_recommend_style()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['recommend_style'])) $options['recommend_style'] = '0';

		echo '<label><input type="radio" name="dot_irecommendthis_settings[recommend_style]" value="0"' . (($options['recommend_style']) == "0" ? 'checked' : '') . ' />
		' . __('Default style - Thumb', 'i-recommend-this') . '</label><br />

		<label><input type="radio" name="dot_irecommendthis_settings[recommend_style]" value="1"' . (($options['recommend_style']) == "1" ? 'checked' : '') . ' />
		' . __('Heart', 'i-recommend-this') . '</label><br />';
	}

	public function settings_validate($input) {
		$new_input = array();

		if( isset( $input['add_to_posts'] ) )
			$new_input['add_to_posts'] = absint( $input['add_to_posts'] );

		if( isset( $input['add_to_other'] ) )
			$new_input['add_to_other'] = absint( $input['add_to_other'] );

		if( isset( $input['text_zero_suffix'] ) )
			$new_input['text_zero_suffix'] = sanitize_text_field( $input['text_zero_suffix'] );

		if( isset( $input['text_one_suffix'] ) )
			$new_input['text_one_suffix'] = sanitize_text_field( $input['text_one_suffix'] );

		if( isset( $input['text_more_suffix'] ) )
			$new_input['text_more_suffix'] = sanitize_text_field( $input['text_more_suffix'] );

		if( isset( $input['link_title_new'] ) )
			$new_input['link_title_new'] = sanitize_text_field( $input['link_title_new'] );

		if( isset( $input['link_title_active'] ) )
			$new_input['link_title_active'] = sanitize_text_field( $input['link_title_active'] );

		if( isset( $input['disable_css'] ) )
			$new_input['disable_css'] = absint( $input['disable_css'] );

		if( isset( $input['hide_zero'] ) )
			$new_input['hide_zero'] = absint( $input['hide_zero'] );

		if( isset( $input['enable_unique_ip'] ) )
			$new_input['enable_unique_ip'] = absint( $input['enable_unique_ip'] );

		if( isset( $input['recommend_style'] ) )
			$new_input['recommend_style'] = absint( $input['recommend_style'] );

		return $new_input;
	}

	/*--------------------------------------------*
	* Add Likes Column In Post Manage Page
	*--------------------------------------------*/

/*	function dot_columns_head($defaults)
	{
		$defaults['likes'] = __('Likes', 'i-recommend-this');
		return $defaults;
	}

	function dot_column_content($column_name, $post_ID)
	{
		if ($column_name == 'likes')
			echo get_post_meta($post_ID, '_recommended', true) . ' ' . __('like', 'i-recommend-this');
	}

	function dot_column_register_sortable($columns)
	{
		$columns['likes'] = 'likes';
		return $columns;
	}

	function dot_column_orderby($vars)
	{
		if (isset($vars['orderby']) && 'likes' == $vars['orderby']) {
			$vars = array_merge($vars, array(
				'meta_key' => '_recommended',
				'orderby' => 'meta_value'
			));
		}

		return $vars;
	}*/

}