<?php
/**
 * Most Recommended Posts Widget.
 *
 * @package IRecommendThis
 */

/**
 * Widget to display the most recommended posts.
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
	 * @param array $instance Current widget settings.
	 */
	public function form( $instance ) {
		// Set widget defaults.
		$defaults = array(
			'title'           => '',
			'number_of_posts' => '5',
			'show_count'      => '',
		);

		// Parse current settings with defaults.
		$instance        = wp_parse_args( (array) $instance, $defaults );
		$title           = $instance['title'];
		$number_of_posts = $instance['number_of_posts'];
		$show_count      = $instance['show_count'];
		?>

		<!-- Widget Title -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Widget Title', 'i-recommend-this' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<!-- Number of Posts to show -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number_of_posts' ) ); ?>"><?php esc_html_e( 'Number of posts to show:', 'i-recommend-this' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number_of_posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number_of_posts' ) ); ?>" type="text" value="<?php echo esc_attr( $number_of_posts ); ?>" />
		</p>

		<!-- Show number of recommends -->
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_count ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>"><?php esc_html_e( 'Show number of recommends?', 'i-recommend-this' ); ?></label>
		</p>
		<?php
	}

	/**
	 * Update widget settings.
	 *
	 * @param array $new_instance New widget settings.
	 * @param array $old_instance Previous widget settings.
	 * @return array Updated settings.
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
	 * @param array $instance Widget settings.
	 */
	public function widget( $args, $instance ) {
		$title           = ! empty( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$number_of_posts = ! empty( $instance['number_of_posts'] ) ? absint( $instance['number_of_posts'] ) : 5;
		$show_count      = ! empty( $instance['show_count'] ) ? true : false;

		// WordPress core before_widget hook (always include).
		echo wp_kses_post( $args['before_widget'] );

		// Display the widget.
		echo '<div class="widget-text wp_widget_plugin_box">';
		if ( $title ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}
		echo '<ul class="mostrecommendedposts">';

		global $wpdb;

		// Build query string.
		$sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->posts}, {$wpdb->postmeta} WHERE {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
			AND post_status='publish' AND post_type='post' AND meta_key='_recommended'
			ORDER BY {$wpdb->postmeta}.meta_value+0 DESC LIMIT %d",
			$number_of_posts
		);

		// Execute query.
		$posts = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

		$return = '';
		foreach ( $posts as $item ) {
			$post_title = esc_attr( $item->post_title );
			$permalink  = esc_url( get_permalink( $item->ID ) );
			$post_count = absint( $item->meta_value );
			echo '<li><a href="' . esc_url( $permalink ) . '" title="' . esc_attr( $post_title ) . '" rel="nofollow">' . esc_html( $post_title ) . '</a>';

			// Show number of recommends if $show_count is true.
			if ( $show_count ) {
				echo ' (' . esc_html( $post_count ) . ')';
			}
			echo '</li>';
		}
		echo esc_html( $return );
		echo '</ul>';

		echo '</div>';
		// WordPress core after_widget hook (always include).
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Register the widget.
	 */
	public static function register_widget() {
		register_widget( 'Themeist_Most_Recommended_Posts_Widget' );
	}
}

// Register the widget using the static method.
add_action( 'widgets_init', array( 'Themeist_Most_Recommended_Posts_Widget', 'register_widget' ) );
