<?php



/*--------------------------------------------*
 * Template Tag
 *--------------------------------------------*/

function dot_irecommendthis($id = null)
{
	global $themeist_i_recommend_this_public;
	echo $themeist_i_recommend_this_public->dot_recommend($id);

}
