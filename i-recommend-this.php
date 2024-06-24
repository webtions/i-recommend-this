<?php
/**
 * Plugin Name: I Recommend This
 * Plugin URI: https://themeist.com/plugins/wordpress/i-recommend-this/#utm_source=wp-plugin&utm_medium=i-recommend-this&utm_campaign=plugins-page
 * Description: This plugin allows your visitors to recommend or like your posts.
 * Version: 3.9.0
 * Author: Harish Chouhan, Themeist
 * Author URI: https://themeist.com/
 * Author Email: support@themeist.com
 * Text Domain: i-recommend-this
 * License: GPL-3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Do nothing if class is already defined.
if ( class_exists( 'Themeist_IRecommendThis' ) ) {
	return;
}

define( 'THEMEIST_IRT_VERSION', '3.9.0' );
define( 'THEMEIST_IRT_DB_VERSION', '2.6.2' );

// Require includes.
require_once __DIR__ . '/includes/class-themeist-irecommendthis.php';
require_once __DIR__ . '/admin/class-themeist-irecommendthis-admin.php';
require_once __DIR__ . '/public/class-i-recommend-this-public.php';
require_once __DIR__ . '/public/class-widget-most-recommended-posts.php';
require_once __DIR__ . '/includes/functions.php';

// Create instance of plugin class.
global $themeist_i_recommend_this;
$themeist_i_recommend_this = new Themeist_IRecommendThis( __FILE__ );
$themeist_i_recommend_this->add_hooks();

// Create instance of admin class.
global $themeist_i_recommend_this_admin;
$themeist_i_recommend_this_admin = new Themeist_IRecommendThis_Admin( __FILE__ );
$themeist_i_recommend_this_admin->add_admin_hooks();

// Create instance of public class.
global $themeist_i_recommend_this_public;
$themeist_i_recommend_this_public = new Themeist_IRecommendThis_Public( __FILE__ );
$themeist_i_recommend_this_public->add_public_hooks();
