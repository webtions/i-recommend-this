<?php
/**
 * Register the widget.
 *
 * @package IRecommendThis
 */

/**
 * Register the Most Recommended Posts widget.
 */
function widget_most_recommended_posts() {
	register_widget( 'Themeist_Most_Recommended_Posts_Widget' );
}
add_action( 'widgets_init', 'widget_most_recommended_posts' );
