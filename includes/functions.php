<?php



/*--------------------------------------------*
 * Template Tag
 *--------------------------------------------*/

function dot_irecommendthis($id = null)
{
	global $dot_irecommendthis;
	echo $dot_irecommendthis->dot_recommend($id);

}