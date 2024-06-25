<?php
/**
 * Themeist_Most_Recommended_Posts_Widget Class
 *
 * @package IRecommendThis
 */

/**
 * Main widget class for displaying most recommended posts.
 */
class Themeist_Most_Recommended_Posts_Widget extends WP_Widget {

	/**
	 * Main constructor.
	 */
	public function __construct() {
		parent::__construct(
			'most_recommended_posts',
			__( 'Most Recommended Posts', 'i-recommend-this' ),
			array(
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * The widget form (for the backend).
	 *
	 * @param array $instance The widget options.
	 */
	public function form( $instance ) {
		// Set widget defaults.
		$defaults = array(
			'title'           => '',
			'number_of_posts' => '5',
			'show_count'      => '',
		);

		// Parse current settings with defaults.
		$title           = isset( $instance['title'] ) ? $instance['title'] : $defaults['title'];
		$number_of_posts = isset( $instance['number_of_posts'] ) ? $instance['number_of_posts'] : $defaults['number_of_posts'];
		$show_count      = isset( $instance['show_count'] ) ? $instance['show_count'] : $defaults['show_count'];
		?>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Widget Title', 'i-recommend-this' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number_of_posts' ) ); ?>"><?php esc_html_e( 'Number of posts to show:', 'i-recommend-this' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number_of_posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number_of_posts' ) ); ?>" type="number" value="<?php echo esc_attr( $number_of_posts ); ?>" />
		</p>

		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_count ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>"><?php esc_html_e( 'Show number of recommends?', 'i-recommend-this' ); ?></label>
		</p>
		<?php
	}

	/**
	 * Update widget settings.
	 *
	 * @param array $new_instance New settings for this instance.
	 * @param array $old_instance Old settings for this instance.
	 * @return array $instance Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                    = $old_instance;
		$instance['title']           = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['number_of_posts'] = isset( $new_instance['number_of_posts'] ) ? absint( $new_instance['number_of_posts'] ) : 5;
		$instance['show_count']      = isset( $new_instance['show_count'] ) ? (bool) $new_instance['show_count'] : false;
		return $instance;
	}

	/**
	 * Display the widget.
	 *
	 * @param array $args Display arguments.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		$title           = ! empty( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$number_of_posts = ! empty( $instance['number_of_posts'] ) ? absint( $instance['number_of_posts'] ) : 5;
		$show_count      = ! empty( $instance['show_count'] ) ? true : false;

		// WordPress core before_widget hook.
		echo wp_kses_post( $args['before_widget'] );

		echo '<div class="widget-text wp_widget_plugin_box">';
		// Display widget title if defined.
		if ( $title ) {
			echo wp_kses_post( $args['before_title'] . esc_html( $title ) . $args['after_title'] );
		}
		echo '<ul class="mostrecommendedposts">';

		$posts = $this->get_most_recommended_posts( $number_of_posts );

		foreach ( $posts as $item ) {
			$post_title = esc_html( $item->post_title );
			$permalink  = esc_url( get_permalink( $item->ID ) );
			$post_count = absint( $item->meta_value );
			echo '<li><a href="' . esc_url( $permalink ) . '" title="' . esc_attr( $post_title ) . '" rel="nofollow">' . esc_html( $post_title ) . '</a>';

			// Show number of recommends if $show_count is true.
			if ( $show_count ) {
				echo ' (' . esc_html( $post_count ) . ')';
			}
			echo '</li>';
		}
		echo '</ul>';

		echo '</div>';
		// WordPress core after_widget hook.
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Get most recommended posts.
	 *
	 * @param int $number_of_posts Number of posts to retrieve.
	 * @return array List of most recommended posts.
	 */
	private function get_most_recommended_posts( $number_of_posts ) {
		global $wpdb;

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery, WordPress.WP.Caching.NonPersistentCacheSetting
		$query = "
			SELECT p.ID, p.post_title, pm.meta_value 
			FROM {$wpdb->posts} p
			JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
			WHERE p.post_status = %s 
			AND p.post_type = %s 
			AND pm.meta_key = %s 
			ORDER BY pm.meta_value+0 DESC 
			LIMIT %d
		";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$sql = $wpdb->prepare( $query, 'publish', 'post', '_recommended', $number_of_posts );

		$cache_key = 'most_recommended_posts_' . $number_of_posts;
		// phpcs:ignore WordPress.WP.Caching.NonPersistentCacheSetting
		$posts = get_transient( $cache_key );
		if ( false === $posts ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$posts = $wpdb->get_results( $sql );
			// phpcs:ignore WordPress.WP.Caching.NonPersistentCacheSetting
			set_transient( $cache_key, $posts, HOUR_IN_SECONDS );
		}

		return $posts;
	}
	// phpcs:enable
}
