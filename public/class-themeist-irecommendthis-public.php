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
	 * Register public-facing hooks for the plugin.
	 *
	 * Adds actions and filters for enqueuing scripts and modifying content.
	 */
	public function register_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'the_content', array( $this, 'modify_content' ) );
	}

	/**
	 * Enqueue scripts and styles for the plugin.
	 *
	 * Loads plugin-specific CSS and JavaScript, and localizes script with necessary data.
	 */
	public function enqueue_scripts() {
		// Get plugin settings.
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );

		// Validate and set default values for CSS options.
		$disable_css     = isset( $options['disable_css'] ) ? intval( $options['disable_css'] ) : 0;
		$recommend_style = isset( $options['recommend_style'] ) ? intval( $options['recommend_style'] ) : 0;

		// Enqueue styles if CSS is not disabled.
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

		// Register and enqueue the main JavaScript file.
		wp_register_script( 'irecommendthis', plugins_url( 'js/irecommendthis.js', $this->plugin_file ), array( 'jquery' ), THEMEIST_IRT_VERSION, true );
		wp_enqueue_script( 'irecommendthis' );

		// Create a nonce for secure AJAX requests and localize it.
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

		// For backward compatibility, also localize with the old variable name
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
	 * Adds the recommendation button to post content based on plugin settings.
	 *
	 * @param string $content The original post content.
	 * @return string Modified post content with recommendation button.
	 */
	public function modify_content( $content ) {
		// Skip adding button on specific page types.
		if ( is_page_template() || is_page() || is_front_page() ) {
			return $content;
		}

		global $wp_current_filter;
		if ( in_array( 'get_the_excerpt', (array) $wp_current_filter, true ) ) {
			return $content;
		}

		// Get plugin settings for displaying recommendation button.
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );

		// Support both new setting keys and old dot_ prefixed ones for backward compatibility
		$add_to_posts = isset( $options['add_to_posts'] ) ? $options['add_to_posts'] : '0';
		$add_to_other = isset( $options['add_to_other'] ) ? $options['add_to_other'] : '0';

		// Append recommendation button to singular posts.
		if ( is_singular( 'post' ) && $add_to_posts ) {
			$content .= Themeist_IRecommendThis_Shortcodes::recommend();
		}

		// Append recommendation button to other post archive pages.
		if ( ( is_home() || is_category() || is_tag() || is_author() || is_date() || is_search() ) && $add_to_other ) {
			$content .= Themeist_IRecommendThis_Shortcodes::recommend();
		}

		return $content;
	}

	/**
	 * Handle recommendation updates and tracking.
	 *
	 * Manages the recommendation count for a specific post, including
	 * incrementing, decrementing, and tracking via cookies and IP.
	 *
	 * @param int    $post_id           The post ID being recommended.
	 * @param string $text_zero_suffix  Text to display when count is zero.
	 * @param string $text_one_suffix   Text to display when count is one.
	 * @param string $text_more_suffix  Text to display when count is more than one.
	 * @param string $action            Action to perform: 'get' or 'update'.
	 * @return string HTML representation of the recommendation count.
	 */
	public static function process_recommendation( $post_id, $text_zero_suffix = false, $text_one_suffix = false, $text_more_suffix = false, $action = 'get' ) {
		// Validate post ID.
		if ( ! is_numeric( $post_id ) ) {
			return;
		}

		// Sanitize suffix inputs.
		$text_zero_suffix = sanitize_text_field( $text_zero_suffix );
		$text_one_suffix  = sanitize_text_field( $text_one_suffix );
		$text_more_suffix = sanitize_text_field( $text_more_suffix );

		// Fetch options and recommendation count.
		$options = get_option( 'irecommendthis_settings', get_option( 'dot_irecommendthis_settings', array() ) );
		$recommended = (int) get_post_meta( $post_id, '_recommended', true );

		// Check both new and old option keys for backward compatibility
		$hide_zero = isset( $options['hide_zero'] ) ? (int) $options['hide_zero'] : 0;
		$enable_unique_ip = isset( $options['enable_unique_ip'] ) ? (int) $options['enable_unique_ip'] : 0;

		// Function for getting the suffix based on the recommendation count.
		$get_suffix = function ( $count ) use ( $text_zero_suffix, $text_one_suffix, $text_more_suffix ) {
			if ( 0 === $count ) {
				return $text_zero_suffix;
			} elseif ( 1 === $count ) {
				return $text_one_suffix;
			} else {
				return $text_more_suffix;
			}
		};

		// Handling the 'get' action.
		if ( 'get' === $action ) {
			// Initialize recommendation count if not set.
			if ( ! $recommended ) {
				$recommended = 0;
				add_post_meta( $post_id, '_recommended', $recommended, true );
			}

			// Output HTML for the recommendation button.
			$suffix = $get_suffix( $recommended );
			$output = ( 0 === $recommended && 1 === $hide_zero )
				? '<span class="irecommendthis-count" style="display: none;">0</span> <span class="irecommendthis-suffix">' . esc_html( $suffix ) . '</span>'
				: '<span class="irecommendthis-count">' . esc_html( $recommended ) . '</span> <span class="irecommendthis-suffix">' . esc_html( $suffix ) . '</span>';

			return apply_filters( 'irecommendthis_before_count', apply_filters( 'dot_irt_before_count', $output ) );
		}

		// Handling the 'update' action.
		if ( 'update' === $action ) {
			global $wpdb;

			// Process unique IP address checking if enabled.
			if ( 0 !== $enable_unique_ip ) {
				$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

				$nonce_verified = false;
				if ( isset( $_POST['security'] ) ) {
					// Check both old and new nonce names for backward compatibility
					$nonce_verified = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'irecommendthis-nonce' ) ||
									  wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'dot-irecommendthis-nonce' );
				}

				if ( isset( $_POST['unrecommend'] ) && $nonce_verified && 'true' === sanitize_text_field( wp_unslash( $_POST['unrecommend'] ) ) ) {
					$sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $ip );
					$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
				} else {
					$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $ip );
					$vote_status_by_ip = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
					$sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}irecommendthis_votes VALUES ('', NOW(), %d, %s )", $post_id, $ip );
					$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
				}
			}

			// Use the new cookie name only - as discussed, users can simply re-like posts
			$cookie_name = 'irecommendthis_' . $post_id;
			$has_cookie = isset( $_COOKIE[ $cookie_name ] ) || isset( $_COOKIE[ 'dot_irecommendthis_' . $post_id ] );

			$nonce_verified = false;
			if ( isset( $_POST['security'] ) ) {
				// Check both old and new nonce names for backward compatibility
				$nonce_verified = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'irecommendthis-nonce' ) ||
								  wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'dot-irecommendthis-nonce' );
			}

			// Handle the case where the user is un-recommending.
			if ( $has_cookie && $nonce_verified && isset( $_POST['unrecommend'] ) && 'false' === sanitize_text_field( wp_unslash( $_POST['unrecommend'] ) ) ) {
				return $recommended;
			}

			// Handle recommendation update.
			if ( $nonce_verified && isset( $_POST['unrecommend'] ) ) {
				if ( 'true' === sanitize_text_field( wp_unslash( $_POST['unrecommend'] ) ) ) {
					setcookie( $cookie_name, '', time() - 3600, '/' );
					$recommended = max( 0, $recommended - 1 );
				} else {
					setcookie( $cookie_name, time(), time() + 3600 * 24 * 365, '/' );
					++$recommended;
				}
			}

			// Update the recommendation count.
			update_post_meta( $post_id, '_recommended', $recommended );

			// Output HTML for the recommendation button.
			$suffix = $get_suffix( $recommended );
			$output = ( 0 === $recommended && 1 === $hide_zero )
				? '<span class="irecommendthis-count" style="display: none;">0</span> <span class="irecommendthis-suffix">' . esc_html( $suffix ) . '</span>'
				: '<span class="irecommendthis-count">' . esc_html( $recommended ) . '</span> <span class="irecommendthis-suffix">' . esc_html( $suffix ) . '</span>';

			return apply_filters( 'irecommendthis_before_count', apply_filters( 'dot_irt_before_count', $output ) );
		}
	}

	/**
	 * Backward compatibility methods.
	 */

	/**
	 * Backward compatibility method for the old name dot_recommend_this().
	 *
	 * @deprecated Use process_recommendation() instead.
	 */
	public static function dot_recommend_this( $post_id, $text_zero_suffix = false, $text_one_suffix = false, $text_more_suffix = false, $action = 'get' ) {
		return self::process_recommendation( $post_id, $text_zero_suffix, $text_one_suffix, $text_more_suffix, $action );
	}

	/**
	 * Backward compatibility method for the old name recommend_this().
	 *
	 * @deprecated Use process_recommendation() instead.
	 */
	public static function recommend_this( $post_id, $text_zero_suffix = false, $text_one_suffix = false, $text_more_suffix = false, $action = 'get' ) {
		return self::process_recommendation( $post_id, $text_zero_suffix, $text_one_suffix, $text_more_suffix, $action );
	}

	/**
	 * Backward compatibility method for dot_content().
	 *
	 * @deprecated Use modify_content() instead.
	 * @param string $content The post content.
	 * @return string Modified post content.
	 */
	public function dot_content( $content ) {
		return $this->modify_content( $content );
	}

	/**
	 * Backward compatibility method for enqueue_scripts().
	 *
	 * @deprecated Use enqueue_scripts() instead.
	 */
	public function dot_enqueue_scripts() {
		return $this->enqueue_scripts();
	}

	/**
	 * Backward compatibility method for add_public_hooks().
	 *
	 * @deprecated Use register_hooks() instead.
	 */
	public function add_public_hooks() {
		return $this->register_hooks();
	}

	/**
	 * Backward compatibility method for the old method names.
	 *
	 * @deprecated Use register_hooks() instead.
	 */
	public function irecommendthis_register_hooks() {
		return $this->register_hooks();
	}

	/**
	 * Backward compatibility method for the old method names.
	 *
	 * @deprecated Use enqueue_scripts() instead.
	 */
	public function irecommendthis_enqueue_scripts() {
		return $this->enqueue_scripts();
	}

	/**
	 * Backward compatibility method for the old method names.
	 *
	 * @deprecated Use modify_content() instead.
	 */
	public function irecommendthis_content( $content ) {
		return $this->modify_content( $content );
	}

	/**
	 * Backward compatibility method for the old method names.
	 *
	 * @deprecated Use process_recommendation() instead.
	 */
	public static function irecommendthis_update_count( $post_id, $text_zero_suffix = false, $text_one_suffix = false, $text_more_suffix = false, $action = 'get' ) {
		return self::process_recommendation( $post_id, $text_zero_suffix, $text_one_suffix, $text_more_suffix, $action );
	}
}
