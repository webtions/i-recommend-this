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

