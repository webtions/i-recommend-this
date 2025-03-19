<?php
/**
 * Most Recommended Posts Widget.
 *
 * @package IRecommendThis
 */

/**
 * Widget to display the most recommended posts.
 */
class Themeist_IRecommendThis_Widget_Most_Recommended extends WP_Widget {

	/**
	 * Main constructor.
	 */
	public function __construct() {
		parent::__construct(
			'irecommendthis_widget_most_recommended',
			__( 'Most Recommended Posts', 'i-recommend-this' ),
			array(
				'customize_selective_refresh' => true,
				'classname'                   => 'widget_irecommendthis_most_recommended',
				'description'                 => __( 'Display the most recommended posts.', 'i-recommend-this' ),
			)
		);

		/**
		 * Action fired after widget is constructed.
		 *
		 * @since 4.0.0
		 * @param Themeist_IRecommendThis_Widget_Most_Recommended $this Widget instance.
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
			'title'           => __( 'Most Recommended Posts', 'i-recommend-this' ),
			'number_of_posts' => '5',
			'show_count'      => true,
			'show_empty'      => false,
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
		$title           = sanitize_text_field( $instance['title'] );
		$number_of_posts = absint( $instance['number_of_posts'] );
		$show_count      = (bool) $instance['show_count'];
		$show_empty      = (bool) $instance['show_empty'];

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
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Widget Title:', 'i-recommend-this' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<!-- Number of Posts to show -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number_of_posts' ) ); ?>"><?php esc_html_e( 'Number of posts to show:', 'i-recommend-this' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number_of_posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number_of_posts' ) ); ?>" type="number" min="1" max="50" value="<?php echo esc_attr( $number_of_posts ); ?>" />
		</p>

		<!-- Show number of recommends -->
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>" type="checkbox" value="1" <?php checked( true, $show_count ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>"><?php esc_html_e( 'Show number of recommends?', 'i-recommend-this' ); ?></label>
		</p>

		<!-- Show message when no recommended posts found -->
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'show_empty' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_empty' ) ); ?>" type="checkbox" value="1" <?php checked( true, $show_empty ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_empty' ) ); ?>"><?php esc_html_e( 'Show message when no posts found?', 'i-recommend-this' ); ?></label>
		</p>

		<?php
		/**
		 * Action to add additional widget form fields.
		 *
		 * @since 4.0.0
		 * @param array                                          $instance Widget settings.
		 * @param Themeist_IRecommendThis_Widget_Most_Recommended $this     Widget instance.
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
		$instance['show_empty']      = isset( $new_instance['show_empty'] ) ? (bool) $new_instance['show_empty'] : false;

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
		 * @param array $instance     Updated widget settings.
		 * @param array $old_instance Previous widget settings.
		 */
		do_action( 'irecommendthis_after_widget_update', $instance, $old_instance );

		return $instance;
	}

	/**
	 * Display the widget.
	 *
	 * @param array $args     Display arguments.
	 * @param array $instance Widget settings.
	 */
	public function widget( $args, $instance ) {
		$title           = ! empty( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$number_of_posts = ! empty( $instance['number_of_posts'] ) ? absint( $instance['number_of_posts'] ) : 5;
		$show_count      = isset( $instance['show_count'] ) ? (bool) $instance['show_count'] : true;
		$show_empty      = isset( $instance['show_empty'] ) ? (bool) $instance['show_empty'] : false;

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
		echo '<div class="widget-text irecommendthis-widget">';

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

			echo wp_kses_post( $args['before_title'] ) .
				'<span class="irecommendthis-widget-title">' . esc_html( $title ) . '</span>' .
				wp_kses_post( $args['after_title'] );
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
		 *
* @param string $list_tag The HTML tag for the list.
		 */
		$list_tag = apply_filters( 'irecommendthis_widget_list_tag', 'ul' );

		/**
		 * Filter the widget list CSS class.
		 *
		 * @since 4.0.0
		 * @param string $list_class The CSS class for the list.
		 */
		$list_class = apply_filters( 'irecommendthis_widget_list_class', 'irecommendthis-most-recommended-posts' );

		global $wpdb;

		/**
		 * Filter the SQL query for the widget.
		 *
		 * @since 4.0.0
		 * @param string $sql              The SQL query.
		 * @param int    $number_of_posts  Number of posts to show.
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

		// Check if there are any posts.
		if ( empty( $posts ) ) {
			if ( $show_empty ) {
				echo '<p class="irecommendthis-no-posts">' .
					esc_html__( 'No recommended posts found.', 'i-recommend-this' ) .
					'</p>';
			}
		} else {
			// Output the list with proper ARIA attributes for accessibility.
			printf(
				'<%1$s class="%2$s" aria-label="%3$s">',
				esc_attr( $list_tag ),
				esc_attr( $list_class ),
				esc_attr__( 'Most recommended posts', 'i-recommend-this' )
			);

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

				printf( '<%s>', esc_attr( $item_tag ) );

				/**
				 * Filter the widget item link.
				 *
				 * @since 4.0.0
				 * @param string $link_html The link HTML.
				 * @param object $item      The current post item.
				 */
				$link_html = apply_filters(
					'irecommendthis_widget_item_link',
					sprintf(
						'<a href="%1$s" title="%2$s" class="irecommendthis-post-link">%3$s</a>',
						esc_url( $permalink ),
						esc_attr( $post_title ),
						esc_html( $post_title )
					),
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
						sprintf(
							' <span class="irecommendthis-count" aria-label="%1$s">(%2$s)</span>',
							/* translators: %d: Number of recommendations */
							esc_attr( sprintf( _n( '%d recommendation', '%d recommendations', $post_count, 'i-recommend-this' ), $post_count ) ),
							esc_html( $post_count )
						),
						$post_count,
						$item
					);

					echo wp_kses_post( $count_html );
				}//end if

				printf( '</%s>', esc_attr( $item_tag ) );

				/**
				 * Action fired after widget item.
				 *
				 * @since 4.0.0
				 * @param object $item The current post item.
				 */
				do_action( 'irecommendthis_after_widget_item', $item );
			}//end foreach

			printf( '</%s>', esc_attr( $list_tag ) );
		}//end if

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
		register_widget( 'Themeist_IRecommendThis_Widget_Most_Recommended' );

		/**
		 * Action fired after widget is registered.
		 *
		 * @since 4.0.0
		 */
		do_action( 'irecommendthis_widget_registered' );
	}
}

// Backward compatibility.
class_alias( 'Themeist_IRecommendThis_Widget_Most_Recommended', 'Themeist_Most_Recommended_Posts_Widget' );

// Register the widget using the static method.
add_action( 'widgets_init', array( 'Themeist_IRecommendThis_Widget_Most_Recommended', 'register_widget' ) );
