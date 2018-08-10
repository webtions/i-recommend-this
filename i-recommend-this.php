<?php
/*
 * Plugin Name:       I Recommend This
 * Plugin URI:        https://themeist.com/plugins/wordpress/i-recommend-this/#utm_source=wp-plugin&utm_medium=i-recommend-this&utm_campaign=plugins-page
 * Description:       This plugin allows your visitors to simply recommend or like your posts instead of commment it.
 * Version:           3.7.8
 * Author:            Themeist, Harish Chouhan
 * Author URI:        https://themeist.com/
 * Author Email:      support@themeist.com
 * Text Domain:       i-recommend-this
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

// do nothing if class is already defined
if( class_exists( 'DOT_IRecommendThis' ) ) {
    return;
}

// require includes
require_once dirname( __FILE__ ) . '/includes/class-i-recommend-this.php';
//require_once dirname( __FILE__ ) . '/includes/functions.php';

// create instance of plugin class
global $themeist_i_recommend_this;
$themeist_i_recommend_this = new DOT_IRecommendThis();
$themeist_i_recommend_this->add_hooks();