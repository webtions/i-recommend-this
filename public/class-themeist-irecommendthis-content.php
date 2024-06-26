<?php
/**
 * Class to handle content-related functionality for the I Recommend This plugin.
 *
 * @package IRecommendThis
 */

class Themeist_IRecommendThis_Content {

	/**
	 * Constructor to initialize the class.
	 *
	 * @param string $plugin_file The main plugin file path.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Add hooks for the content-related functionality.
	 */
	public function add_content_hooks() {
		add_filter( 'the_content', array( $this, 'dot_content' ) );
		add_shortcode( 'dot_recommends', array( $this, 'shortcode' ) );
	}

	/**
	 * Filter the content to include the recommendation button.
	 *
	 * @param string $content The original content.
	 * @return string Modified content with the recommendation button.
	 */
	public function dot_content( $content ) {
		if ( is_page_template() || is_page() || is_front_page() ) {
			return $content;
		}

		global $wp_current_filter;
		if ( in_array( 'get_the_excerpt', (array) $wp_current_filter, true ) ) {
			return $content;
		}

		$options = get_option( 'dot_irecommendthis_settings' );
		$add_to_posts = isset( $options['add_to_posts'] ) ? $options['add_to_posts'] : '0';
		$add_to_other = isset( $options['add_to_other'] ) ? $options['add_to_other'] : '0';

		if ( is_singular( 'post' ) && $add_to_posts ) {
			$content .= $this->dot_recommend();
		}
		if ( ( is_home() || is_category() || is_tag() || is_author() || is_date() || is_search() ) && $add_to_other ) {
			$content .= $this->dot_recommend();
		}

		return $content;
	}

	/**
	 * Shortcode handler for displaying the recommendation button.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the recommendation button.
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts( array( 'id' => null ), $atts );
		return $this->dot_recommend( intval( $atts['id'] ) );
	}

	/**
	 * Display the recommendation button.
	 *
	 * @param int $id Post ID.
	 * @return string HTML output for the recommendation button.
	 */
	private function dot_recommend( $id = null ) {
		global $post;

		$post_id = $id ? $id : get_the_ID();
		$options = get_option( 'dot_irecommendthis_settings' );

		$ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
		$default_options = array(
			'text_zero_suffix'  => '',
			'text_one_suffix'   => '',
			'text_more_suffix'  => '',
			'link_title_new'    => '',
			'link_title_active' => '',
			'enable_unique_ip'  => '0',
		);
		$options = wp_parse_args( $options, $default_options );

		$output = $this->dot_recommend_this( $post_id, $options['text_zero_suffix'], $options['text_one_suffix'], $options['text_more_suffix'], 'get' );
		$voteStatusByIp = 0;
		if ( $options['enable_unique_ip'] != '0' ) {
			global $wpdb;
			$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $ip );
			$voteStatusByIp = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
		}

		if ( ! isset( $_COOKIE[ 'dot_irecommendthis_' . $post_id ] ) && $voteStatusByIp == 0 ) {
			$class = 'dot-irecommendthis';
			$title = empty( $options['link_title_new'] ) ? __( 'Recommend this', 'i-recommend-this' ) : $options['link_title_new'];
		} else {
			$class = 'dot-irecommendthis active';
			$title = empty( $options['link_title_active'] ) ? __( 'You already recommended this', 'i-recommend-this' ) : $options['link_title_active'];
		}

		$dot_irt_html  = '<a href="#" class="' . esc_attr( $class ) . '" id="dot-irecommendthis-' . $post_id . '" title="' . esc_attr( $title ) . '">';
		$dot_irt_html .= apply_filters( 'dot_irt_before_count', $output );
		$dot_irt_html .= '</a>';

		return $dot_irt_html;
	}

	/**
	 * Main Process to get or update the recommendation count.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $text_zero_suffix Text to display after zero count.
	 * @param string $text_one_suffix Text to display after one count.
	 * @param string $text_more_suffix Text to display after more than one count.
	 * @param string $action Action to perform: get or update.
	 * @return string HTML output with the recommendation count.
	 */
	public static function dot_recommend_this( $post_id, $text_zero_suffix = false, $text_one_suffix = false, $text_more_suffix = false, $action = 'get' ) {
		global $wpdb;

		if ( ! is_numeric( $post_id ) ) {
			return;
		}

		$text_zero_suffix = sanitize_text_field( $text_zero_suffix );
		$text_one_suffix  = sanitize_text_field( $text_one_suffix );
		$text_more_suffix = sanitize_text_field( $text_more_suffix );

		switch ( $action ) {
			case 'get':
				$recommended = get_post_meta( $post_id, '_recommended', true );

				if ( ! $recommended ) {
					$recommended = 0;
					add_post_meta( $post_id, '_recommended', $recommended, true );
				}

				if ( $recommended == 0 ) {
					$suffix = $text_zero_suffix;
				} elseif ( $recommended == 1 ) {
					$suffix = $text_one_suffix;
				} else {
					$suffix = $text_more_suffix;
				}

				// Check if zero should be hidden
				$options   = get_option( 'dot_irecommendthis_settings' );
				$hide_zero = isset( $options['hide_zero'] ) ? $options['hide_zero'] : '0';

				if ( $recommended == 0 && $hide_zero == 1 ) {
					$output = '<span class="dot-irecommendthis-count">&nbsp;</span> <span class="dot-irecommendthis-suffix">' . esc_html( $suffix ) . '</span>';
				} else {
					$output = '<span class="dot-irecommendthis-count">' . esc_html( $recommended ) . '</span> <span class="dot-irecommendthis-suffix">' . esc_html( $suffix ) . '</span>';
				}

				return apply_filters( 'dot_irt_before_count', $output );

			case 'update':
				$recommended = get_post_meta( $post_id, '_recommended', true );

				$options          = get_option( 'dot_irecommendthis_settings' );
				$enable_unique_ip = isset( $options['enable_unique_ip'] ) ? $options['enable_unique_ip'] : '0';

				if ( $enable_unique_ip != 0 ) {
					$ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );

					if ( $_POST['unrecommend'] == 'true' ) {
						$sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $ip );
						$wpdb->query( $sql );
					} else {
						$sql            = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}irecommendthis_votes WHERE post_id = %d AND ip = %s", $post_id, $ip );
						$voteStatusByIp = $wpdb->get_var( $sql );
						$sql            = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}irecommendthis_votes VALUES ('', NOW(), %d, %s )", $post_id, $ip );
						$wpdb->query( $sql );
					}
				}

				if ( isset( $_COOKIE[ 'dot_irecommendthis_' . $post_id ] ) && $_POST['unrecommend'] == 'false' ) {
					return $recommended;
				}

				if ( $_POST['unrecommend'] == 'true' ) {
					if ( isset( $_COOKIE[ 'dot_irecommendthis_' . $post_id ] ) ) {
						// Set the cookie to expire in the past to delete it.
						setcookie( 'dot_irecommendthis_' . $post_id, '', time() - 3600, '/' );
					}
					--$recommended;
				} else {
					setcookie( 'dot_irecommendthis_' . $post_id, time(), time() + 3600 * 24 * 365, '/' );
					++$recommended;
				}
				update_post_meta( $post_id, '_recommended', $recommended );

				if ( $recommended == 0 ) {
					$suffix = $text_zero_suffix;
				} elseif ( $recommended == 1 ) {
					$suffix = $text_one_suffix;
				} else {
					$suffix = $text_more_suffix;
				}

				$output = '<span class="dot-irecommendthis-count">' . esc_html( $recommended ) . '</span> <span class="dot-irecommendthis-suffix">' . esc_html( $suffix ) . '</span>';
				return apply_filters( 'dot_irt_before_count', $output );
		}
	}
}
