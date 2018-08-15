<?php



/*--------------------------------------------*
 * Template Tag
 *--------------------------------------------*/

function dot_irecommendthis($id = null)
{
	global $dot_irecommendthis;
	echo $dot_irecommendthis->dot_recommend($id);

}

/*--------------------------------------------*
 * Settings Menu
 *--------------------------------------------*/

/*add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'dot_irecommendthis_plugin_links');

function dot_irecommendthis_plugin_links($links)
{
	return array_merge(
		array(
			'settings' => '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=dot-irecommendthis">' . __('Settings', 'dot-irecommendthis') . '</a>'
		),
		$links
	);
}*/

/*--------------------------------------------*
* Add Likes Column In Post Manage Page
*--------------------------------------------*/

function dot_columns_head($defaults)
{
	$defaults['likes'] = __('Likes', 'i-recommend-this');
	return $defaults;
}

function dot_column_content($column_name, $post_ID)
{
	if ($column_name == 'likes')
		echo get_post_meta($post_ID, '_recommended', true) . ' ' . __('like', 'i-recommend-this');
}

function dot_column_register_sortable($columns)
{
	$columns['likes'] = 'likes';
	return $columns;
}

function dot_column_orderby($vars)
{
	if (isset($vars['orderby']) && 'likes' == $vars['orderby']) {
		$vars = array_merge($vars, array(
			'meta_key' => '_recommended',
			'orderby' => 'meta_value'
		));
	}

	return $vars;
}

add_filter('request', 'dot_column_orderby');
add_filter('manage_edit-post_sortable_columns', 'dot_column_register_sortable');
add_filter('manage_posts_columns', 'dot_columns_head');
add_action('manage_posts_custom_column', 'dot_column_content', 10, 2);