<?php
/**
 * Public-facing functionality for the I Recommend This plugin.
 *
 * @package IRecommendThis
 */
class Themeist_IRecommendThis_Public {

	/**
	 * Path to the main plugin file.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Constructor to initialize the class.
	 *
	 * @param string $plugin_file The main plugin file path.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Add public-facing hooks for the plugin.
	 */
	public function add_public_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'the_content', array( $this, 'modify_content' ) );
	}

	/**
	 * Enqueue scripts and styles for the plugin.
	 */
	public function enqueue_scripts() {
		// Get plugin settings with fallback for backward compatibility
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );

		// Validate and set default values for CSS options
		$disable_css     = isset( $options['disable_css'] ) ? intval( $options['disable_css'] ) : 0;
		$recommend_style = isset( $options['recommend_style'] ) ? intval( $options['recommend_style'] ) : 0;

		// Enqueue styles if CSS is not disabled
		if ( 0 === $disable_css ) {
			$css_file = ( 0 === $recommend_style ) ? 'irecommendthis.css' : 'irecommendthis-heart.css';
			$css_path = plugin_dir_path( $this->plugin_file ) . 'css/' . $css_file;
			$css_url  = plugins_url( 'css/' . $css_file, $this->plugin_file );

			if ( file_exists( $css_path ) ) {
				wp_enqueue_style( 'irecommendthis', $css_url, array(), filemtime( $css_path ) );
			} else {
				wp_enqueue_style( 'irecommendthis', $css_url, array(), THEMEIST_IRT_VERSION );
			}
		}

		// Register and enqueue the main JavaScript file
		wp_register_script( 'irecommendthis', plugins_url( 'js/irecommendthis.js', $this->plugin_file ), array( 'jquery' ), THEMEIST_IRT_VERSION, true );
		wp_enqueue_script( 'irecommendthis' );

		// Create a nonce for secure AJAX requests and localize it
		$nonce = wp_create_nonce( 'irecommendthis-nonce' );
		wp_localize_script(
			'irecommendthis',
			'irecommendthis',
			array(
				'nonce'   => $nonce,
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'options' => wp_json_encode( $options ),
			)
		);

		// Maintain backward compatibility with old variable name
		wp_localize_script(
			'irecommendthis',
			'dot_irecommendthis',
			array(
				'nonce'   => $nonce,
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'options' => wp_json_encode( $options ),
			)
		);
	}

	/**
	 * Modify content to append recommendation button.
	 *
	 * @param string $content The original post content.
	 * @return string Modified post content with recommendation button.
	 */
	public function modify_content( $content ) {
		// Skip adding button on specific page types
		if ( is_page_template() || is_page() || is_front_page() ) {
			return $content;
		}

		global $wp_current_filter;
		if ( in_array( 'get_the_excerpt', (array) $wp_current_filter, true ) ) {
			return $content;
		}

		// Get plugin settings with fallback for backward compatibility
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );

		// Support both new and old setting keys
		$add_to_posts = isset( $options['add_to_posts'] ) ? $options['add_to_posts'] : '0';
		$add_to_other = isset( $options['add_to_other'] ) ? $options['add_to_other'] : '0';

		// Append recommendation button to singular posts
		if ( is_singular( 'post' ) && $add_to_posts ) {
			$content .= Themeist_IRecommendThis_Shortcodes::recommend();
		}

		// Append recommendation button to other post archive pages
		if ( ( is_home() || is_category() || is_tag() || is_author() || is_date() || is_search() ) && $add_to_other ) {
			$content .= Themeist_IRecommendThis_Shortcodes::recommend();
		}

		return $content;
	}

	// Remaining methods from previous implementation stay the same
}
