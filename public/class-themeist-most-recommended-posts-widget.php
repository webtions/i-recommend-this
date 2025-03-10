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

		/**
		 * Action fired after widget is constructed.
		 *
		 * @since 4.0.0
		 * @param Themeist_Most_Recommended_Posts_Widget $this Widget instance.
		 */
		do_action( 'irecommendthis_widget_constructed', $this );
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

		/**
		 * Filter widget default values.
		 *
		 * @since 4.0.0
		 * @param array $defaults Default widget settings.
		 */
		$defaults = apply_filters( 'irecommendthis_widget_defaults', $defaults );

		// Parse current settings with defaults.
		$instance        = wp_parse_args( (array) $instance, $defaults );
		$title           = $instance['title'];
		$number_of_posts = $instance['number_of_posts'];
		$show_count      = $instance['show_count'];

		/**
		 * Action fired before widget form is rendered.
		 *
		 * @since 4.0.0
		 * @param array $instance Widget settings.
		 */
		do_action( 'irecommendthis_before_widget_form', $instance );
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
		/**
		 * Action to add additional widget form fields.
		 *
		 * @since 4.0.0
		 * @param array                                 $instance Widget settings.
		 * @param Themeist_Most_Recommended_Posts_Widget $this     Widget instance.
		 */
		do_action( 'irecommendthis_widget_form', $instance, $this );

		/**
		 * Action fired after widget form is rendered.
		 *
		 * @since 4.0.0
		 * @param array $instance Widget settings.
		 */
		do_action( 'irecommendthis_after_widget_form', $instance );
	}

	/**
	 * Update widget settings.
	 *
	 * @param array $new_instance New widget settings.
	 * @param array $old_instance Previous widget settings.
	 * @return array Updated settings.
	 */
	public function update( $new_instance, $old_instance ) {
		/**
		 * Action fired before widget settings are updated.
		 *
		 * @since 4.0.0
		 * @param array $new_instance New widget settings.
		 * @param array $old_instance Previous widget settings.
		 */
		do_action( 'irecommendthis_before_widget_update', $new_instance, $old_instance );

		$instance                    = $old_instance;
		$instance['title']           = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['number_of_posts'] = isset( $new_instance['number_of_posts'] ) ? absint( $new_instance['number_of_posts'] ) : 5;
		$instance['show_count']      = isset( $new_instance['show_count'] ) ? (bool) $new_instance['show_count'] : false;

		/**
		 * Filter widget settings before saving.
		 *
		 * @since 4.0.0
		 * @param array $instance     Updated widget settings.
		 * @param array $new_instance New widget settings.
		 * @param array $old_instance Previous widget settings.
		 */
		$instance = apply_filters( 'irecommendthis_widget_update', $instance, $new_instance, $old_instance );

		/**
		 * Action fired after widget settings are updated.
		 *
		 * @since 4.0.0
		 * @param array $instance Updated widget settings.
		 * @param array $old_instance Previous widget settings.
		 */
		do_action( 'irecommendthis_after_widget_update', $instance, $old_instance );

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

		/**
		 * Filter widget display arguments.
		 *
		 * @since 4.0.0
		 * @param array $args     Widget display arguments.
		 * @param array $instance Widget settings.
		 */
		$args = apply_filters( 'irecommendthis_widget_args', $args, $instance );

		/**
		 * Action fired before widget output.
		 *
		 * @since 4.0.0
		 * @param array $args     Widget display arguments.
		 * @param array $instance Widget settings.
		 */
		do_action( 'irecommendthis_before_widget', $args, $instance );

		// WordPress core before_widget hook (always include).
		echo wp_kses_post( $args['before_widget'] );

		// Display the widget.
		echo '<div class="widget-text wp_widget_plugin_box">';

		if ( $title ) {
			/**
			 * Filter the widget title.
			 *
			 * @since 4.0.0
			 * @param string $title    The widget title.
			 * @param array  $instance Widget settings.
			 * @param array  $args     Widget display arguments.
			 */
			$title = apply_filters( 'irecommendthis_widget_title', $title, $instance, $args );

			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}

		/**
		 * Action fired before widget list.
		 *
		 * @since 4.0.0
		 * @param array $instance Widget settings.
		 */
		do_action( 'irecommendthis_before_widget_list', $instance );

		/**
		 * Filter the widget list HTML tag.
		 *
		 * @since 4.0.0
		 * @param string $list_tag The HTML tag for the list.
		 */
		$list_tag = apply_filters( 'irecommendthis_widget_list_tag', 'ul' );

		/**
		 * Filter the widget list CSS class.
		 *
		 * @since 4.0.0
		 * @param string $list_class The CSS class for the list.
		 */
		$list_class = apply_filters( 'irecommendthis_widget_list_class', 'mostrecommendedposts' );

		echo '<' . esc_attr( $list_tag ) . ' class="' . esc_attr( $list_class ) . '">';

		global $wpdb;

		/**
		 * Filter the SQL query for the widget.
		 *
		 * @since 4.0.0
		 * @param string $sql           The SQL query.
		 * @param int    $number_of_posts Number of posts to show.
		 */
		$sql = apply_filters(
			'irecommendthis_widget_query',
			$wpdb->prepare(
				"SELECT p.ID, p.post_title, pm.meta_value AS meta_value
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_status = 'publish'
				AND p.post_type = 'post'
				AND pm.meta_key = '_recommended'
				ORDER BY CAST(pm.meta_value AS UNSIGNED) DESC
				LIMIT %d",
				$number_of_posts
			),
			$number_of_posts
		);

		// Execute query.
		$posts = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

		/**
		 * Filter the query results.
		 *
		 * @since 4.0.0
		 * @param array $posts    Query results.
		 * @param array $instance Widget settings.
		 */
		$posts = apply_filters( 'irecommendthis_widget_posts', $posts, $instance );

		foreach ( $posts as $item ) {
			$post_title = get_the_title( $item->ID );
			$permalink  = get_permalink( $item->ID );
			$post_count = absint( $item->meta_value );

			/**
			 * Filter the widget item HTML tag.
			 *
			 * @since 4.0.0
			 * @param string $item_tag The HTML tag for each item.
			 */
			$item_tag = apply_filters( 'irecommendthis_widget_item_tag', 'li' );

			/**
			 * Action fired before widget item.
			 *
			 * @since 4.0.0
			 * @param object $item The current post item.
			 */
			do_action( 'irecommendthis_before_widget_item', $item );

			echo '<' . esc_attr( $item_tag ) . '>';

			/**
			 * Filter the widget item link.
			 *
			 * @since 4.0.0
			 * @param string $link_html The link HTML.
			 * @param object $item      The current post item.
			 */
			$link_html = apply_filters(
				'irecommendthis_widget_item_link',
				'<a href="' . esc_url( $permalink ) . '" title="' . esc_attr( $post_title ) . '" rel="nofollow">' . esc_html( $post_title ) . '</a>',
				$item
			);

			echo wp_kses_post( $link_html );

			// Show number of recommends if $show_count is true.
			if ( $show_count ) {
				/**
				 * Filter the widget item count HTML.
				 *
				 * @since 4.0.0
				 * @param string $count_html The count HTML.
				 * @param int    $post_count The post count.
				 * @param object $item       The current post item.
				 */
				$count_html = apply_filters(
					'irecommendthis_widget_item_count',
					' (' . esc_html( $post_count ) . ')',
					$post_count,
					$item
				);

				echo wp_kses_post( $count_html );
			}

			echo '</' . esc_attr( $item_tag ) . '>';

			/**
			 * Action fired after widget item.
			 *
			 * @since 4.0.0
			 * @param object $item The current post item.
			 */
			do_action( 'irecommendthis_after_widget_item', $item );
		}

		echo '</' . esc_attr( $list_tag ) . '>';

		/**
		 * Action fired after widget list.
		 *
		 * @since 4.0.0
		 * @param array $instance Widget settings.
		 */
		do_action( 'irecommendthis_after_widget_list', $instance );

		echo '</div>';

		// WordPress core after_widget hook (always include).
		echo wp_kses_post( $args['after_widget'] );

		/**
		 * Action fired after widget output.
		 *
		 * @since 4.0.0
		 * @param array $args     Widget display arguments.
		 * @param array $instance Widget settings.
		 */
		do_action( 'irecommendthis_after_widget', $args, $instance );
	}

	/**
	 * Register the widget.
	 */
	public static function register_widget() {
		register_widget( 'Themeist_Most_Recommended_Posts_Widget' );

		/**
		 * Action fired after widget is registered.
		 *
		 * @since 4.0.0
		 */
		do_action( 'irecommendthis_widget_registered' );
	}
}

// Register the widget using the static method.
add_action( 'widgets_init', array( 'Themeist_Most_Recommended_Posts_Widget', 'register_widget' ) );
