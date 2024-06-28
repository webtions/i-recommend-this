<?php

/*--------------------------------------------*
 * Template Tag
 *--------------------------------------------*/

/**
 * Outputs the recommendation button for a post.
 *
 * @param int|null $id The post ID. If null, the current post ID is used.
 */
function dot_irecommendthis($id = null) {
    return Themeist_IRecommendThis_Shortcodes::dot_recommend($id);
}
