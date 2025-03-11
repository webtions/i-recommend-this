<?php
/**
 * Settings component for admin functionality.
 *
 * Handles plugin settings registration, validation, and display.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to handle plugin settings.
 */
class Themeist_IRecommendThis_Admin_Settings {

	/**
	 * Initialize the component.
	 */
	public function initialize() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		/**
		 * Action fired after settings component is initialized.
		 *
		 * @since 4.0.0
		 */
		do_action( 'irecommendthis_settings_initialized' );
	}

	/**
	 * Register settings and their sections/fields.
	 */
	public function register_settings() {
		register_setting( 'irecommendthis_settings', 'irecommendthis_settings', array( $this, 'validate_settings' ) );

		add_settings_section( 'irecommendthis', '', array( $this, 'render_settings_intro' ), 'irecommendthis_settings' );

		/**
		 * Action fired before registering settings fields.
		 *
		 * @since 4.0.0
		 */
		do_action( 'irecommendthis_before_register_fields' );

		// Display options.
		add_settings_field( 'show_on', __( 'Automatically display on', 'i-recommend-this' ), array( $this, 'render_show_on_field' ), 'irecommendthis_settings', 'irecommendthis' );
		add_settings_field( 'recommend_style', __( 'Choose a style', 'i-recommend-this' ), array( $this, 'render_recommend_style_field' ), 'irecommendthis_settings', 'irecommendthis' );
		add_settings_field( 'disable_css', __( 'Disable CSS', 'i-recommend-this' ), array( $this, 'render_disable_css_field' ), 'irecommendthis_settings', 'irecommendthis' );
		add_settings_field( 'hide_zero', __( 'Hide Zero Count', 'i-recommend-this' ), array( $this, 'render_hide_zero_field' ), 'irecommendthis_settings', 'irecommendthis' );

		// Text options.
		add_settings_field( 'text_zero_suffix', __( 'Text after 0 Count', 'i-recommend-this' ), array( $this, 'render_text_zero_suffix_field' ), 'irecommendthis_settings', 'irecommendthis' );
		add_settings_field( 'text_one_suffix', __( 'Text after 1 Count', 'i-recommend-this' ), array( $this, 'render_text_one_suffix_field' ), 'irecommendthis_settings', 'irecommendthis' );
		add_settings_field( 'text_more_suffix', __( 'Text after more than 1 Count', 'i-recommend-this' ), array( $this, 'render_text_more_suffix_field' ), 'irecommendthis_settings', 'irecommendthis' );
		add_settings_field( 'link_title_new', __( 'Text for new recommendations', 'i-recommend-this' ), array( $this, 'render_link_title_new_field' ), 'irecommendthis_settings', 'irecommendthis' );
		add_settings_field( 'link_title_active', __( 'Text for removing recommendations', 'i-recommend-this' ), array( $this, 'render_link_title_active_field' ), 'irecommendthis_settings', 'irecommendthis' );

		// Privacy options.
		add_settings_field( 'enable_unique_ip', __( 'Enable IP tracking', 'i-recommend-this' ), array( $this, 'render_enable_unique_ip_field' ), 'irecommendthis_settings', 'irecommendthis' );

		/**
		 * Action to register custom settings fields.
		 *
		 * @since 4.0.0
		 * @param string $page    The settings page ('irecommendthis_settings').
		 * @param string $section The settings section ('irecommendthis').
		 */
		do_action( 'irecommendthis_register_fields', 'irecommendthis_settings', 'irecommendthis' );
	}

	/**
	 * Validate settings before they're saved.
	 *
	 * @param array $input The input array to validate.
	 * @return array The validated input array.
	 */
	public function validate_settings( $input ) {
		/**
		 * Filter settings input before validation.
		 *
		 * @since 4.0.0
		 * @param array $input The input array before validation.
		 */
		$input = apply_filters( 'irecommendthis_pre_validate_settings', $input );

		$input['add_to_posts']      = ! empty( $input['add_to_posts'] ) ? '1' : '0';
		$input['add_to_other']      = ! empty( $input['add_to_other'] ) ? '1' : '0';
		$input['text_zero_suffix']  = sanitize_text_field( $input['text_zero_suffix'] ?? '' );
		$input['text_one_suffix']   = sanitize_text_field( $input['text_one_suffix'] ?? '' );
		$input['text_more_suffix']  = sanitize_text_field( $input['text_more_suffix'] ?? '' );
		$input['link_title_new']    = sanitize_text_field( $input['link_title_new'] ?? '' );
		$input['link_title_active'] = sanitize_text_field( $input['link_title_active'] ?? '' );
		$input['disable_css']       = ! empty( $input['disable_css'] ) ? '1' : '0';
		$input['hide_zero']         = ! empty( $input['hide_zero'] ) ? '1' : '0';
		$input['enable_unique_ip']  = ! empty( $input['enable_unique_ip'] ) ? '1' : '0';
		$input['recommend_style']   = ! empty( $input['recommend_style'] ) ? '1' : '0';

		/**
		 * Filter settings after validation.
		 *
		 * @since 4.0.0
		 * @param array $input The validated input array.
		 */
		return apply_filters( 'irecommendthis_validate_settings', $input );
	}

	/**
	 * Render the settings introduction text.
	 */
	public function render_settings_intro() {
		/**
		 * Action fired before settings intro is rendered.
		 *
		 * @since 4.0.0
		 */
		do_action( 'irecommendthis_before_settings_intro' );

		?>
		<p><?php esc_html_e( 'This plugin allows your visitors to simply recommend or like your posts instead of commenting.', 'i-recommend-this' ); ?></p>
		<?php

		/**
		 * Action fired after settings intro is rendered.
		 *
		 * @since 4.0.0
		 */
		do_action( 'irecommendthis_after_settings_intro' );
	}

	/**
	 * Render the 'Automatically display on' field.
	 */
	public function render_show_on_field() {
		$options = get_option( 'irecommendthis_settings', array() );

		if ( ! isset( $options['add_to_posts'] ) ) {
			$options['add_to_posts'] = '0';
		}
		if ( ! isset( $options['add_to_other'] ) ) {
			$options['add_to_other'] = '0';
		}

		/**
		 * Action fired before show_on field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_before_show_on_field', $options );

		echo '<input type="hidden" name="irecommendthis_settings[add_to_posts]" value="0" />
		<label><input type="checkbox" name="irecommendthis_settings[add_to_posts]" value="1"' . checked( $options['add_to_posts'], '1', false ) . ' />
		' . esc_html__( 'Posts', 'i-recommend-this' ) . '</label><br />
		<input type="hidden" name="irecommendthis_settings[add_to_other]" value="0" />
		<label><input type="checkbox" name="irecommendthis_settings[add_to_other]" value="1"' . checked( $options['add_to_other'], '1', false ) . ' />
		' . esc_html__( 'All other pages like Index, Archive, etc.', 'i-recommend-this' ) . '</label><br />';

		/**
		 * Action fired after show_on field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_after_show_on_field', $options );
	}

	/**
	 * Render the 'Hide Zero Count' field.
	 */
	public function render_hide_zero_field() {
		$options = get_option( 'irecommendthis_settings', array() );
		if ( ! isset( $options['hide_zero'] ) ) {
			$options['hide_zero'] = '0';
		}

		/**
		 * Action fired before hide_zero field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_before_hide_zero_field', $options );

		echo '<input type="hidden" name="irecommendthis_settings[hide_zero]" value="0" />
		<label><input type="checkbox" name="irecommendthis_settings[hide_zero]" value="1"' . checked( $options['hide_zero'], '1', false ) . ' />' .
			esc_html__( 'Hide count if count is zero', 'i-recommend-this' ) . '</label>';

		/**
		 * Action fired after hide_zero field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_after_hide_zero_field', $options );
	}

	/**
	 * Render the 'Enable Unique IP' field.
	 */
	public function render_enable_unique_ip_field() {
		$options = get_option( 'irecommendthis_settings', array() );
		if ( ! isset( $options['enable_unique_ip'] ) ) {
			$options['enable_unique_ip'] = '0';
		}

		/**
		 * Action fired before enable_unique_ip field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_before_enable_unique_ip_field', $options );

		echo '<input type="hidden" name="irecommendthis_settings[enable_unique_ip]" value="0" />
		<label><input type="checkbox" name="irecommendthis_settings[enable_unique_ip]" value="1"' . checked( $options['enable_unique_ip'], '1', false ) . ' />' .
			esc_html__( 'Enable tracking unique recommendations by IP. IPs are securely anonymized using WordPress cryptographic functions to enhance GDPR compliance.', 'i-recommend-this' ) . '</label>
			<p class="description">' . esc_html__( 'Cookies are always saved by default. Enabling this option provides stronger protection against duplicate votes using secure global IP hashing.', 'i-recommend-this' ) . '</p>';

		/**
		 * Action fired after enable_unique_ip field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_after_enable_unique_ip_field', $options );
	}

	/**
	 * Render the 'Disable CSS' field.
	 */
	public function render_disable_css_field() {
		$options = get_option( 'irecommendthis_settings', array() );
		if ( ! isset( $options['disable_css'] ) ) {
			$options['disable_css'] = '0';
		}

		/**
		 * Action fired before disable_css field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_before_disable_css_field', $options );

		echo '<input type="hidden" name="irecommendthis_settings[disable_css]" value="0" />
		<label><input type="checkbox" name="irecommendthis_settings[disable_css]" value="1"' . checked( $options['disable_css'], '1', false ) . ' />' .
			esc_html__( 'I want to use my own CSS styles', 'i-recommend-this' ) . '</label>';

		/**
		 * Action fired after disable_css field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_after_disable_css_field', $options );
	}

	/**
	 * Render the 'Text Zero Suffix' field.
	 */
	public function render_text_zero_suffix_field() {
		$options = get_option( 'irecommendthis_settings', array() );
		if ( ! isset( $options['text_zero_suffix'] ) ) {
			$options['text_zero_suffix'] = '';
		}

		/**
		 * Action fired before text_zero_suffix field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_before_text_zero_suffix_field', $options );

		echo '<input type="text" name="irecommendthis_settings[text_zero_suffix]" class="regular-text" value="' . esc_attr( $options['text_zero_suffix'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Text to display after zero count. Leave blank for no text after the count.', 'i-recommend-this' ) . '</span>';

		/**
		 * Action fired after text_zero_suffix field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_after_text_zero_suffix_field', $options );
	}

	/**
	 * Render the 'Text One Suffix' field.
	 */
	public function render_text_one_suffix_field() {
		$options = get_option( 'irecommendthis_settings', array() );
		if ( ! isset( $options['text_one_suffix'] ) ) {
			$options['text_one_suffix'] = '';
		}

		/**
		 * Action fired before text_one_suffix field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_before_text_one_suffix_field', $options );

		echo '<input type="text" name="irecommendthis_settings[text_one_suffix]" class="regular-text" value="' . esc_attr( $options['text_one_suffix'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Text to display after 1 person has recommended. Leave blank for no text after the count.', 'i-recommend-this' ) . '</span>';

		/**
		 * Action fired after text_one_suffix field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_after_text_one_suffix_field', $options );
	}

	/**
	 * Render the 'Text More Suffix' field.
	 */
	public function render_text_more_suffix_field() {
		$options = get_option( 'irecommendthis_settings', array() );
		if ( ! isset( $options['text_more_suffix'] ) ) {
			$options['text_more_suffix'] = '';
		}

		/**
		 * Action fired before text_more_suffix field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_before_text_more_suffix_field', $options );

		echo '<input type="text" name="irecommendthis_settings[text_more_suffix]" class="regular-text" value="' . esc_attr( $options['text_more_suffix'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Text to display after more than 1 person have recommended. Leave blank for no text after the count.', 'i-recommend-this' ) . '</span>';

		/**
		 * Action fired after text_more_suffix field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_after_text_more_suffix_field', $options );
	}

	/**
	 * Render the 'Link Title for New Posts' field.
	 */
	public function render_link_title_new_field() {
		$options = get_option( 'irecommendthis_settings', array() );
		if ( ! isset( $options['link_title_new'] ) ) {
			$options['link_title_new'] = '';
		}

		/**
		 * Action fired before link_title_new field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_before_link_title_new_field', $options );

		echo '<input type="text" name="irecommendthis_settings[link_title_new]" class="regular-text" value="' . esc_attr( $options['link_title_new'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Text shown for the recommendation button for posts not yet recommended. Default: "Recommend this"', 'i-recommend-this' ) . '</span>';

		/**
		 * Action fired after link_title_new field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_after_link_title_new_field', $options );
	}

	/**
	 * Render the 'Link Title for Already Voted Posts' field.
	 */
	public function render_link_title_active_field() {
		$options = get_option( 'irecommendthis_settings', array() );
		if ( ! isset( $options['link_title_active'] ) ) {
			$options['link_title_active'] = '';
		}

		/**
		 * Action fired before link_title_active field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_before_link_title_active_field', $options );

		echo '<input type="text" name="irecommendthis_settings[link_title_active]" class="regular-text" value="' . esc_attr( $options['link_title_active'] ) . '" /><br />
		<span class="description">' . esc_html__( 'Text shown for the recommendation button for posts already recommended. Default: "Unrecommend this"', 'i-recommend-this' ) . '</span>';

		/**
		 * Action fired after link_title_active field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_after_link_title_active_field', $options );
	}

	/**
	 * Render the 'Recommend Style' field.
	 */
	public function render_recommend_style_field() {
		$options = get_option( 'irecommendthis_settings', array() );
		if ( ! isset( $options['recommend_style'] ) ) {
			$options['recommend_style'] = '0';
		}

		/**
		 * Action fired before recommend_style field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_before_recommend_style_field', $options );

		echo '<label><input type="radio" name="irecommendthis_settings[recommend_style]" value="0"' . checked( $options['recommend_style'], '0', false ) . ' />
		' . esc_html__( 'Default style - Thumb', 'i-recommend-this' ) . '</label><br />

		<label><input type="radio" name="irecommendthis_settings[recommend_style]" value="1"' . checked( $options['recommend_style'], '1', false ) . ' />
		' . esc_html__( 'Heart', 'i-recommend-this' ) . '</label><br />';

		/**
		 * Action fired after recommend_style field is rendered.
		 *
		 * @since 4.0.0
		 * @param array $options Current settings.
		 */
		do_action( 'irecommendthis_after_recommend_style_field', $options );

		/**
		 * Action fired after all fields are rendered.
		 *
		 * @since 4.0.0
		 */
		do_action( 'irecommendthis_after_all_fields' );
	}
}
