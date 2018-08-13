<?php

class Themeist_IRecommendThis_Admin {

	/*--------------------------------------------*
	 * Admin Menu
	 *--------------------------------------------*/

	function dot_irecommendthis_menu()
	{
		$page_title = __('I Recommend This', 'i-recommend-this');
		$menu_title = __('I Recommend This', 'i-recommend-this');
		$capability = 'manage_options';
		$menu_slug = 'dot-irecommendthis';
		$function = array(&$this, 'dot_settings_page');
		add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);

	}    //dot_irecommendthis_menu


	/*--------------------------------------------*
	 * Settings & Settings Page
	 *--------------------------------------------*/

	public function dot_irecommendthis_settings() // whitelist options
	{
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

		add_settings_field('disable_unique_ip', __('Disable IP saving', 'i-recommend-this'), array(&$this, 'setting_disable_unique_ip'), 'dot-irecommendthis', 'dot-irecommendthis');

		add_settings_field('recommend_style', __('Choose a style', 'i-recommend-this'), array(&$this, 'setting_recommend_style'), 'dot-irecommendthis', 'dot-irecommendthis');

		add_settings_field('instructions', __('Shortcode and Template Tag', 'i-recommend-this'), array(&$this, 'setting_instructions'), 'dot-irecommendthis', 'dot-irecommendthis');

	}    //dot_irecommendthis_settings



	public function dot_settings_page()
	{
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>"I Recommend This" Options</h2>
			<div class="metabox-holder has-right-sidebar">
				<!-- SIDEBAR -->
				<div class="inner-sidebar">
					<!--<div class="postbox">
						<h3><span>Metabox 1</span></h3>
						<div class="inside">
							<p>Hi, I'm metabox 1!</p>
						</div>
					</div>-->
				</div> <!-- //inner-sidebar -->

				<!-- MAIN CONTENT -->
				<div id="post-body">
					<div id="post-body-content">
						<form action="options.php" method="post">
							<?php settings_fields('dot-irecommendthis'); ?>
							<?php do_settings_sections('dot-irecommendthis'); ?>
							<p class="submit"><input type="submit" class="button-primary"
													 value="<?php _e('Save Changes', 'i-recommend-this'); ?>"/></p>
						</form>
					</div>
				</div> <!-- //main content -->
			</div> <!-- //metabox-holder -->
		</div>
		<?php

	} //dot_settings_page



	function section_intro()
	{
		?>

		<p><?php _e('<a href="https://twitter.com/harishchouhan" class="twitter-follow-button" data-show-count="false">Follow @harishchouhan</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>', 'i-recommend-this'); ?>
			<br/>
			<?php _e('or Check out our other themes & plugins at <a href="https://themeist.com">Themeist</a>.', 'i-recommend-this'); ?>
		</p>
		<p><?php _e('This plugin allows your visitors to simply recommend or like your posts instead of commment it.', 'i-recommend-this'); ?></p>
		<?php
	}

	function setting_show_on()
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

	function setting_hide_zero()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['hide_zero'])) $options['hide_zero'] = '0';

		echo '<input type="hidden" name="dot_irecommendthis_settings[hide_zero]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[hide_zero]" value="1"' . (($options['hide_zero']) ? ' checked="checked"' : '') . ' />' .
			__('Hide count if count is zero', 'i-recommend-this') . '</label>';
	}

	function setting_disable_unique_ip()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['disable_unique_ip'])) $options['disable_unique_ip'] = '0';

		echo '<input type="hidden" name="dot_irecommendthis_settings[disable_unique_ip]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[disable_unique_ip]" value="1"' . (($options['disable_unique_ip']) ? ' checked="checked"' : '') . ' />' .
			__('Disable saving of IP Address. Will only save cookies to track user votes.', 'i-recommend-this') . '</label>';
	}

	function setting_disable_css()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['disable_css'])) $options['disable_css'] = '0';

		echo '<input type="hidden" name="dot_irecommendthis_settings[disable_css]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[disable_css]" value="1"' . (($options['disable_css']) ? ' checked="checked"' : '') . ' />' .
			__('I want to use my own CSS styles', 'i-recommend-this') . '</label>';
	}

	function setting_text_zero_suffix()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['text_zero_suffix'])) $options['text_zero_suffix'] = '';

		echo '<input type="text" name="dot_irecommendthis_settings[text_zero_suffix]" class="regular-text" value="' . $options['text_zero_suffix'] . '" /><br />
		<span class="description">' . __('Text to display after zero count. Leave blank for no text after the count.', 'i-recommend-this') . '</span>';
	}

	function setting_text_one_suffix()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['text_one_suffix'])) $options['text_one_suffix'] = '';

		echo '<input type="text" name="dot_irecommendthis_settings[text_one_suffix]" class="regular-text" value="' . $options['text_one_suffix'] . '" /><br />
		<span class="description">' . __('Text to display after 1 person has recommended. Leave blank for no text after the count.', 'i-recommend-this') . '</span>';
	}

	function setting_text_more_suffix()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['text_more_suffix'])) $options['text_more_suffix'] = '';

		echo '<input type="text" name="dot_irecommendthis_settings[text_more_suffix]" class="regular-text" value="' . $options['text_more_suffix'] . '" /><br />
		<span class="description">' . __('Text to display after more than 1 person have recommended. Leave blank for no text after the count.', 'i-recommend-this') . '</span>';
	}

	function setting_link_title_new()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['link_title_new'])) $options['link_title_new'] = '';

		echo '<input type="text" name="dot_irecommendthis_settings[link_title_new]" class="regular-text" value="' . $options['link_title_new'] . '" /><br />
		<span class="description">' . __('Link Title element for posts not yet voted by a user.', 'i-recommend-this') . '</span>';
	}

	function setting_link_title_active()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['link_title_active'])) $options['link_title_active'] = '';

		echo '<input type="text" name="dot_irecommendthis_settings[link_title_active]" class="regular-text" value="' . $options['link_title_active'] . '" /><br />
		<span class="description">' . __('Link Title element for posts already voted by a user.', 'i-recommend-this') . '</span>';
	}

	function setting_recommend_style()
	{
		$options = get_option('dot_irecommendthis_settings');
		if (!isset($options['recommend_style'])) $options['recommend_style'] = '0';

		echo '<label><input type="radio" name="dot_irecommendthis_settings[recommend_style]" value="0"' . (($options['recommend_style']) == "0" ? 'checked' : '') . ' />
		' . __('Default style - Thumb', 'i-recommend-this') . '</label><br />

		<label><input type="radio" name="dot_irecommendthis_settings[recommend_style]" value="1"' . (($options['recommend_style']) == "1" ? 'checked' : '') . ' />
		' . __('Heart', 'i-recommend-this') . '</label><br />';
	}

	function setting_instructions()
	{
		echo '<p>' . __('To use I Recomment This in your posts and pages you can use the shortcode:', 'i-recommend-this') . '</p>
		<p><code>[dot_recommends]</code></p>
		<p>' . __('To use I Recomment This manually in your theme template use the following PHP code:', 'i-recommend-this') . '</p>
		<p><code>&lt;?php if( function_exists(\'dot_irecommendthis\') ) dot_irecommendthis(); ?&gt;</code></p>
		<p>' . __('To show top recommended post from a particular date use below shortcode', 'i-recommend-this') . '</p>
		<p><code>[dot_recommended_posts container=\'div\' post_type=\'showcase\' number=\'10\' year=\'2013\' monthnum=\'7\']</code></p>';
	}

	function settings_validate($input)
	{
		return $input;
	}

}