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
    global $themeist_i_recommend_this_public;

    // Ensure the global object is available before using it
    if ( isset( $themeist_i_recommend_this_public ) ) {
        echo $themeist_i_recommend_this_public->dot_recommend( $id );
    }
}