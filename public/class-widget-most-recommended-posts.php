<?php
class Themeist_Most_Recommended_Posts_Widget extends WP_Widget {

	// Main constructor
	public function __construct() {
		parent::__construct(
			'most_recommended_posts',
			__( 'Most Recommended Posts', 'i-recommend-this' ),
			array(
				'customize_selective_refresh' => true,
			)
		);
	}
	// The widget form (for the backend )
	public function form( $instance ) {
		// Set widget defaults
		$defaults = array(
			'title'    => '',
			'number_of_posts'     => '5',
			'show_count' => '',
		);

		// Parse current settings with defaults
		extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>

		<?php // Widget Title ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Widget Title', 'i-recommend-this' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<?php // Number of Posts to show ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number_of_posts' ) ); ?>"><?php _e( 'Number of posts to show:', 'i-recommend-this' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number_of_posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number_of_posts' ) ); ?>" type="text" value="<?php echo esc_attr( $number_of_posts ); ?>" />
			<!--<small>(max. 15)</small>-->
		</p>

		<?php // Show number of recommends ?>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_count ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>"><?php _e( 'Show number of recommends?', 'i-recommend-this' ); ?></label>
		</p>

	<?php }
	// Update widget settings
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']    = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['number_of_posts']     = isset( $new_instance['number_of_posts'] ) ? wp_strip_all_tags( $new_instance['number_of_posts'] ) : '';
		$instance['show_count'] = isset( $new_instance['show_count'] ) ? 1 : false;
		return $instance;
	}

	// Display the widget
	public function widget( $args, $instance ) {
		extract( $args );
		// Check the widget options
		$title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		//$number_of_posts = isset( $instance['number_of_posts'] ) && $instance['number_of_posts'] !== '' ? $instance['number_of_posts'] : 5;
		$number_of_posts = isset( $instance['number_of_posts'] ) && ! empty( $instance['number_of_posts'] ) ? $instance['number_of_posts'] : 5;
		//$number_of_posts = 5;
		$show_count = ! empty( $instance['show_count'] ) ? $instance['show_count'] : false;

		// WordPress core before_widget hook (always include )
		echo $before_widget;

		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';
			// Display widget title if defined
			if ( $title ) {
				echo $before_title . $title . $after_title;
			}
			echo '<ul class="mostrecommendedposts">';

				global $wpdb;

				// empty params array to hold params for prepared statement
				$params = array();

				// build query string
				$sql = "SELECT * FROM $wpdb->posts, $wpdb->postmeta WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id";
				$sql .= " AND post_status='publish' AND post_type='post' AND meta_key='_recommended'";

				// add order by and limit
				$sql .= " ORDER BY {$wpdb->postmeta}.meta_value+0 DESC LIMIT %d";
				$params[] = $number_of_posts;

				// prepare sql statement
				$query = $wpdb->prepare($sql, $params);

				// execute query
				$posts = $wpdb->get_results($query);

				$return = '';
				foreach ($posts as $item) {
					$post_title = stripslashes($item->post_title);
					$permalink = get_permalink($item->ID);
					$post_count = $item->meta_value;
					echo '<li><a href="' . $permalink . '" title="' . $post_title . '" rel="nofollow">' . $post_title . '</a>';

					// Show number of recommends if $show_count is true
					if ( $show_count ) {
						echo ' (' . $post_count . ')';
					}
					echo '</li>';
				}
				echo $return;
			echo '</ul>';

			//display_recommended_posts($number_of_posts, '<li>', '</li>', $show_count);

		echo '</div>';
		// WordPress core after_widget hook (always include )
		echo $after_widget;
	}
}

// Register the widget
function widget_most_recommended_posts() {
	register_widget( 'Themeist_Most_Recommended_Posts_Widget' );
}
add_action( 'widgets_init', 'widget_most_recommended_posts' );