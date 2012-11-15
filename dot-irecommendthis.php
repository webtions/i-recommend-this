<?php
/**
 * Plugin Name: I Recommend This
 * Plugin URI: http://www.harishchouhan.com/personal-projects/i-recommend-this/
 * Description: This plugin allows your visitors to simply recommend or like your posts instead of commment it.
 * Version: 2.0
 * Author: Harish Chouhan
 * Author URI: http://www.harishchouhan.com
 * Author Email: me@harishchouhan.com
 *
 * @package WordPress
 * @subpackage DOT_IRecommendThis
 * @author Harish
 * @since 2.0
 *
 * License:

  Copyright 2012 "I Recommend This WordPress Plugin" (me@harishchouhan.coms)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
 */
 
//if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 

if ( ! class_exists( 'DOT_IRecommendThis' ) ) 
{
	
	
	class DOT_IRecommendThis {
		
		public $version = '2.0';
		
		/*--------------------------------------------*
		 * Constructor
		 *--------------------------------------------*/
		
		function __construct( $file ) 
		{		
			$this->file = $file;
			
			// Load text domain
			add_action( 'init', array( &$this, 'load_localisation' ), 0 );
			
			// Run this on activation / deactivation
			register_activation_hook(  __FILE__, array( &$this, 'activate' ) );
			
			add_action( 'admin_menu', array( &$this, 'dot_irecommendthis_menu' ) );
			add_action( 'admin_init', array( &$this, 'dot_irecommendthis_settings' ) );
			add_action( 'init', array( &$this, 'add_widget_most_recommended_posts' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'dot_enqueue_scripts' ) );
			add_filter( 'the_content', array( &$this, 'dot_content' ) );
			add_action( 'publish_post', array( &$this, 'dot_setup_recommends' ) );
			add_action( 'wp_ajax_dot-irecommendthis', array( &$this, 'ajax_callback' ) );
			add_action( 'wp_ajax_nopriv_dot-irecommendthis', array( &$this, 'ajax_callback' ) );
			add_shortcode( 'dot_recommends', array( &$this, 'shortcode' ) );
			
		} // end constructor    
	
	
		/*--------------------------------------------*
		 * Localisation | Public | 1.4.6 | Return : void
		 *--------------------------------------------*/
	
		public function load_localisation () 
		{
			load_plugin_textdomain( 'dot', false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
			
		} // End load_localisation()
		
		
		/*--------------------------------------------*
		 * Activate
		 *--------------------------------------------*/
		 
		public function activate( $network_wide ) {
			
			global $wpdb;
			
			$table_name = $wpdb->prefix . "irecommendthis_votes";
			if($wpdb->get_var("show tables recommend '$table_name'") != $table_name) {
				$sql = "CREATE TABLE " . $table_name . " (
					id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					time TIMESTAMP NOT NULL,
					post_id BIGINT(20) NOT NULL,
					ip VARCHAR(15) NOT NULL,
					UNIQUE KEY id (id)
				);";
		
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
				
				$this->register_plugin_version();
		
				//add_option("dot_irecommendthis_db_version", $dot_irecommendthis_db_version);
			}
	
		} // end activate
		
		private function register_plugin_version () {
			if ( $this->version != '' ) {
				update_option( 'dot-irecommendthis' . '-version', $this->version );
			}
		} // End register_plugin_version()
				
	
		/*--------------------------------------------*
		 * Admin Menu
		 *--------------------------------------------*/
	
		function dot_irecommendthis_menu() 
		{
			$page_title = __('I Recommend This', 'dot');
			$menu_title = __('I Recommend This', 'dot');
			$capability = 'manage_options';
			$menu_slug = 'dot-irecommendthis';
			$function =  array( &$this, 'dot_settings_page');
			add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
			
		}	//dot_irecommendthis_menu
		
		
		/*--------------------------------------------*
		 * Settings & Settings Page
		 *--------------------------------------------*/
	
		public function dot_irecommendthis_settings() // whitelist options
		{ 
			register_setting( 'dot-irecommendthis', 'dot_irecommendthis_settings', array(&$this, 'settings_validate') );
			add_settings_section( 'dot-irecommendthis', '', array(&$this, 'section_intro'), 'dot-irecommendthis' );		
			add_settings_field( 'show_on', __( 'Automatically display on', 'dot' ), array(&$this, 'setting_show_on'), 'dot-irecommendthis', 'dot-irecommendthis' );
			add_settings_field( 'text_zero_suffix', __( 'Text after 0 Count', 'dot' ), array(&$this, 'setting_text_zero_suffix'), 'dot-irecommendthis', 'dot-irecommendthis' );
			add_settings_field( 'text_one_suffix', __( 'Text after 1 Count', 'dot' ), array(&$this, 'setting_text_one_suffix'), 'dot-irecommendthis', 'dot-irecommendthis' );
			add_settings_field( 'text_more_suffix', __( 'Text after more than 1 Count', 'dot' ), array(&$this, 'setting_text_more_suffix'), 'dot-irecommendthis', 'dot-irecommendthis' );
			add_settings_field( 'disable_css', __( 'Disable CSS', 'dot' ), array(&$this, 'setting_disable_css'), 'dot-irecommendthis', 'dot-irecommendthis' );
			add_settings_field( 'recommend_style', __( 'Choose a style', 'dot' ), array(&$this, 'setting_recommend_style'), 'dot-irecommendthis', 'dot-irecommendthis' );
			add_settings_field( 'instructions', __( 'Shortcode and Template Tag', 'dot' ), array(&$this, 'setting_instructions'), 'dot-irecommendthis', 'dot-irecommendthis' );
			
		}	//dot_irecommendthis_settings
		
		
		public function dot_settings_page() 
		{
			?>
			<div class="wrap">
				<?php screen_icon(); ?>
				<h2>"I Recommend This" Options</h2>
					<div class="metabox-holder has-right-sidebar">
						<!-- SIDEBAR -->
						<div class="inner-sidebar">
							<!--<div class="postbox">
								<h3><span>Metabox 1</span></h3>
								<div class="inside">
									<p>Hi, I'm metabox 1!</p>
								</div>
							</div>-->
						</div> <!-- //inner-sidebar -->
							
						<!-- MAIN CONTENT -->
						<div id="post-body">
							<div id="post-body-content">
								<form action="options.php" method="post">
									<?php settings_fields( 'dot-irecommendthis' ); ?>
									<?php do_settings_sections( 'dot-irecommendthis' ); ?>
									<p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'dot' ); ?>" /></p>
								</form>
							</div>
						</div> <!-- //main content -->
					</div> <!-- //metabox-holder -->
			</div>
			<?php
	
		} //dot_settings_page
			
		function section_intro() 
		{
			?>

			<p><?php _e('<a href="https://twitter.com/harishchouhan" class="twitter-follow-button" data-show-count="false">Follow @harishchouhan</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script> or Check out our other themes & plugins at <a href="http://www.dreamsonline.net">Dreams Online Themes</a>.', 'dot'); ?></p>
			<p><?php _e('This plugin allows your visitors to simply recommend or like your posts instead of commment it.', 'dot'); ?></p>
			<?php		
		}	
	
		function setting_show_on() 
		{
			$options = get_option( 'dot_irecommendthis_settings' );
			if( !isset($options['add_to_posts']) ) $options['add_to_posts'] = '0';
			if( !isset($options['add_to_other']) ) $options['add_to_other'] = '0';
			
			echo '<input type="hidden" name="dot_irecommendthis_settings[add_to_posts]" value="0" />
			<label><input type="checkbox" name="dot_irecommendthis_settings[add_to_posts]" value="1"'. (($options['add_to_posts']) ? ' checked="checked"' : '') .' />
			'. __('Posts', 'dot') .'</label><br />
			<input type="hidden" name="dot_irecommendthis_settings[add_to_other]" value="0" />
			<label><input type="checkbox" name="dot_irecommendthis_settings[add_to_other]" value="1"'. (($options['add_to_other']) ? ' checked="checked"' : '') .' />
			'. __('All other pages like Index, Archive, etc.', 'dot') .'</label><br />';
		}
	
		function setting_disable_css() 
		{
			$options = get_option( 'dot_irecommendthis_settings' );
			if( !isset($options['disable_css']) ) $options['disable_css'] = '0';
			
			echo '<input type="hidden" name="dot_irecommendthis_settings[disable_css]" value="0" />
			<label><input type="checkbox" name="dot_irecommendthis_settings[disable_css]" value="1"'. (($options['disable_css']) ? ' checked="checked"' : '') .' />
			I want to use my own CSS styles</label>';
		}
		
		function setting_text_zero_suffix() 
		{
			$options = get_option( 'dot_irecommendthis_settings' );
			if( !isset($options['text_zero_suffix']) ) $options['text_zero_suffix'] = '';
			
			echo '<input type="text" name="dot_irecommendthis_settings[text_zero_suffix]" class="regular-text" value="'. $options['text_zero_suffix'] .'" /><br />
			<span class="description">'. __('Text to display after zero count. Leave blank for no text after the count.', 'dot') .'</span>';
		}
		
		function setting_text_one_suffix() {
			$options = get_option( 'dot_irecommendthis_settings' );
			if( !isset($options['text_one_suffix']) ) $options['text_one_suffix'] = '';
			
			echo '<input type="text" name="dot_irecommendthis_settings[text_one_suffix]" class="regular-text" value="'. $options['text_one_suffix'] .'" /><br />
			<span class="description">'. __('Text to display after 1 person has recommended. Leave blank for no text after the count.', 'dot') .'</span>';
		}
		
		function setting_text_more_suffix() 
		{
			$options = get_option( 'dot_irecommendthis_settings' );
			if( !isset($options['text_more_suffix']) ) $options['text_more_suffix'] = '';
			
			echo '<input type="text" name="dot_irecommendthis_settings[text_more_suffix]" class="regular-text" value="'. $options['text_more_suffix'] .'" /><br />
			<span class="description">'. __('Text to display after more than 1 person have recommended. Leave blank for no text after the count.', 'dot') .'</span>';
		}
	
		function setting_recommend_style() 
		{
			$options = get_option( 'dot_irecommendthis_settings' );
			if( !isset($options['recommend_style']) ) $options['recommend_style'] = '0';
			
			echo '<label><input type="radio" name="dot_irecommendthis_settings[recommend_style]" value="0"'. (($options['recommend_style']) == "0" ? checked : '') .' />
			'. __('Default style - Thumb', 'dot') .'</label><br />
			
			<label><input type="radio" name="dot_irecommendthis_settings[recommend_style]" value="1"'. (($options['recommend_style']) == "1" ? checked : '') .' />
			'. __('Heart', 'dot') .'</label><br />';
		}
		
		function setting_instructions() 
		{
			echo '<p>'. __('To use I Recomment This in your posts and pages you can use the shortcode:', 'dot') .'</p>
			<p><code>[dot_irecommendthis]</code></p>
			<p>'. __('To use I Recomment This manually in your theme template use the following PHP code:', 'dot') .'</p>
			<p><code>&lt;?php if( function_exists(\'dot_irecommendthis\') ) dot_irecommendthis(); ?&gt;</code></p>';
		}	
		
		function settings_validate($input) 
		{
			return $input;
		}
	
	
		/*--------------------------------------------*
		 * Enqueue Scripts
		 *--------------------------------------------*/
		 
		function dot_enqueue_scripts() 
		{
			$options = get_option( 'dot_irecommendthis_settings' );
			if( !isset($options['disable_css']) ) $options['disable_css'] = '0';
			if( !isset($options['recommend_style']) ) $options['recommend_style'] = '0';
			
			
				if ($options['recommend_style'] == '0') {
					wp_enqueue_style( 'dot-irecommendthis', plugins_url( '/css/dot-irecommendthis.css', __FILE__ ) );
				}
				else {
					wp_enqueue_style( 'dot-irecommendthis', plugins_url( '/css/dot-irecommendthis-heart.css', __FILE__ ) );
				}
			
			
			wp_enqueue_script( 'dot-irecommendthis', plugins_url( '/js/dot_irecommendthis.js', __FILE__ ), array('jquery') );
			wp_enqueue_script( 'jquery' );
			
			wp_localize_script('dot-irecommendthis', 'dot', array(
				'ajaxurl' => admin_url('admin-ajax.php'),
			));
	
			wp_localize_script( 'dot-irecommendthis', 'dot_irecommendthis', array('ajaxurl' => admin_url('admin-ajax.php')) );
	
		}	//dot_enqueue_scripts
	
	
		/*--------------------------------------------*
		 * Content / Front-end view
		 *--------------------------------------------*/
		 
		function dot_content( $content ) 
		{
			// Don't show on custom page templates or pages
			if(is_page_template() || is_page() || is_front_page()) return $content;
			
			// Don't show after excerpts				
			global $wp_current_filter;
			if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
				return $content;
			}
	
			$options = get_option( 'dot_irecommendthis_settings' );
			if( !isset($options['add_to_posts']) ) $options['add_to_posts'] = '0';
			if( !isset($options['add_to_other']) ) $options['add_to_other'] = '0';
	
			if(is_singular('post') && $options['add_to_posts']) $content .= $this->dot_recommend();
			if((is_home() || is_category() || is_tag() || is_author() || is_date() || is_search()) && $options['add_to_other'] ) $content .= $this->dot_recommend();
			
			return $content;
			
		}	//dot_content
		
	
		/*--------------------------------------------*
		 * Setup recommends
		 *--------------------------------------------*/
	
		function setup_recommends( $post_id ) 
		{
			if(!is_numeric($post_id)) return;
		
			add_post_meta($post_id, '_recommended', '0', true);
			
		}	//setup_recommends
		
	
		/*--------------------------------------------*
		 * AJAX Callback
		 *--------------------------------------------*/	
		
		function ajax_callback($post_id) 
		{
			$options = get_option( 'dot_irecommendthis_settings' );
			if( !isset($options['add_to_posts']) ) $options['add_to_posts'] = '1';
			if( !isset($options['add_to_other']) ) $options['add_to_other'] = '1';
			if( !isset($options['text_zero_suffix']) ) $options['text_zero_suffix'] = '';
			if( !isset($options['text_one_suffix']) ) $options['text_one_suffix'] = '';
			if( !isset($options['text_more_suffix']) ) $options['text_more_suffix'] = '';
	
			if( isset($_POST['recommend_id']) ) {
				// Click event. Get and Update Count
				$post_id = str_replace('dot-irecommendthis-', '', $_POST['recommend_id']);
				echo $this->dot_recommend_this($post_id, $options['text_zero_suffix'], $options['text_one_suffix'], $options['text_more_suffix'], 'update');
			} else {
				// AJAXing data in. Get Count
				$post_id = str_replace('dot-irecommendthis-', '', $_POST['post_id']);
				echo $this->dot_recommend_this($post_id, $options['text_zero_suffix'], $options['text_one_suffix'], $options['text_more_suffix'], 'get');
			}
			
			exit;
			
		}	//ajax_callback
		
	
		/*--------------------------------------------*
		 * Main Process
		 *--------------------------------------------*/	
		 
			
		function dot_recommend_this($post_id, $text_zero_suffix = false, $text_one_suffix = false, $text_more_suffix = false, $action = 'get') 
		{
			if(!is_numeric($post_id)) return;
			$text_zero_suffix = strip_tags($text_zero_suffix);
			$text_one_suffix = strip_tags($text_one_suffix);
			$text_more_suffix = strip_tags($text_more_suffix);		
			
			switch($action) {
			
				case 'get':
					$recommended = get_post_meta($post_id, '_recommended', true);
					if( !$recommended ){
						$recommended = 0;
						add_post_meta($post_id, '_recommended', $recommended, true);
					}
					
					if( $recommended == 0 ) { $suffix = $text_zero_suffix; }
					elseif( $recommended == 1 ) { $suffix = $text_one_suffix; }
					else { $suffix = $text_more_suffix; }
					
					return '<span class="dot-irecommendthis-count">'. $recommended .'</span> <span class=""dot-irecommendthis-suffix">'. $suffix .'</span>';
					break;
					
				case 'update':			
			
					$recommended = get_post_meta($post_id, '_recommended', true);
					
					global $wpdb;
					$ip = $_SERVER['REMOTE_ADDR'];
					$voteStatusByIp = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."irecommendthis_votes WHERE post_id = '$post_id' AND ip = '$ip'");
					
					if ( isset($_COOKIE['dot_irecommendthis_'. $post_id]) && $voteStatusByIp != 0 ) {
						return $recommended;
					}
					
					$recommended++;
					update_post_meta($post_id, '_recommended', $recommended);
					setcookie('dot_irecommendthis_'. $post_id, time(), time()+3600*24*365, '/');
					$wpdb->query("INSERT INTO ".$wpdb->prefix."irecommendthis_votes VALUES ('', NOW(), '$post_id', '$ip')");
					
					if( $recommended == 0 ) { $suffix = $text_zero_suffix; }
					elseif( $recommended == 1 ) { $suffix = $text_one_suffix; }
					else { $suffix = $text_more_suffix; }
					
					return '<span class="dot-irecommendthis-count">'. $recommended .'</span> <span class=""dot-irecommendthis-suffix">'. $suffix .'</span>';
					break;
			
			}
		}	//dot_recommend_this
		
	
		/*--------------------------------------------*
		 * Shortcode
		 *--------------------------------------------*/	
				
		function shortcode( $atts )
		{
			extract( shortcode_atts( array(), $atts ) );
			return $this->dot_recommend();
			
		}	//shortcode
	
	
		function dot_recommend()
		{
			global $wpdb;
			$ip = $_SERVER['REMOTE_ADDR'];
			$post_ID = get_the_ID();
			$voteStatusByIp = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."irecommendthis_votes WHERE post_id = '$post->ID' AND ip = '$ip'");
			global $post;
		

	
			$options = get_option( 'dot_irecommendthis_settings' );
			if( !isset($options['text_zero_suffix']) ) $options['text_zero_suffix'] = '';
			if( !isset($options['text_one_suffix']) ) $options['text_one_suffix'] = '';
			if( !isset($options['text_more_suffix']) ) $options['text_more_suffix'] = '';
			
			$output = $this->dot_recommend_this($post->ID, $options['text_zero_suffix'], $options['text_one_suffix'], $options['text_more_suffix']);
	  

			
			//if ( isset($_COOKIE['dot_irecommendthis_'. $post_id]) && $voteStatusByIp != 0 ) {
						
			if (!isset($_COOKIE['dot_irecommendthis_'.$post_ID]) && $voteStatusByIp == 0) {
				$class = 'dot-irecommendthis';
				$title = __('Recommend this', 'dot');
			} 
			else {				

				$class = 'dot-irecommendthis active';
				$title = __('You already recommended this', 'dot');
			}
			
			return '<a href="#" class="'. $class .'" id="dot-irecommendthis-'. $post->ID .'" title="'. $title .'">'. $output .'</a>';
		}
		

		/*--------------------------------------------*
		 * Widget
		 *--------------------------------------------*/

		function add_widget_most_recommended_posts() 
		{
			
			function most_recommended_posts($numberOf, $before, $after, $show_count, $post_type="post", $raw=false) {
				global $wpdb;
			
				$request = "SELECT * FROM $wpdb->posts, $wpdb->postmeta";
				$request .= " WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id";
				$request .= " AND post_status='publish' AND post_type='$post_type' AND meta_key='_recommended'";
				$request .= " ORDER BY $wpdb->postmeta.meta_value+0 DESC LIMIT $numberOf";
				$posts = $wpdb->get_results($request);
			
				if ($raw):
					return $posts;
				else:
					foreach ($posts as $item) {
						$post_title = stripslashes($item->post_title);
						$permalink = get_permalink($item->ID);
						$post_count = $item->meta_value;
						echo $before.'<a href="' . $permalink . '" title="' . $post_title.'" rel="nofollow">' . $post_title . '</a>';
						echo $show_count == '1' ? ' ('.$post_count.')' : '';
						echo $after;
					}
				endif;
			}
		
			function widget_most_recommended_posts($args) 
			{
				extract($args);
				$options = get_option("most_recommended_posts");
				if (!is_array( $options )) 
				{
					$options = array(
					'title' => 'Most recommended posts',
					'number' => '5',
					'show_count' => '0'
					);
				}
				$title = $options['title'];
				$numberOf = $options['number'];
				$show_count = $options['show_count'];
				
				echo $before_widget;
				echo $before_title . $title . $after_title;
				echo '<ul class="mostrecommendedposts">';
		
				most_recommended_posts($numberOf, '<li>', '</li>', $show_count);
				
				echo '</ul>';
				echo $after_widget;
			}	
			wp_register_sidebar_widget('most_recommended_posts', 'Most recommended posts', 'widget_most_recommended_posts');
			
			function options_widget_most_recommended_posts() {
				$options = get_option("most_recommended_posts");
				
				if (!is_array( $options )) {
					$options = array(
					'title' => 'Most recommended posts',
					'number' => '5',
					'show_count' => '0'
					);
				}
				
				if ($_POST['mrp-submit']) {
					$options['title'] = htmlspecialchars($_POST['mrp-title']);
					$options['number'] = htmlspecialchars($_POST['mrp-number']);
					$options['show_count'] = $_POST['mrp-show-count'];
					if ( $options['number'] > 15) { $options['number'] = 15; }
					
					update_option("most_recommended_posts", $options);
				}
				?>
				<p><label for="mrp-title"><?php _e('Title:', 'i-recommend-this'); ?><br />
				<input class="widefat" type="text" id="mrp-title" name="mrp-title" value="<?php echo $options['title'];?>" /></label></p>
				
				<p><label for="mrp-number"><?php _e('Number of posts to show:', 'i-recommend-this'); ?><br />
				<input type="text" id="mrp-number" name="mrp-number" style="width: 25px;" value="<?php echo $options['number'];?>" /> <small>(max. 15)</small></label></p>
				
				<p><label for="mrp-show-count"><input type="checkbox" id="mrp-show-count" name="mrp-show-count" value="1"<?php if($options['show_count'] == '1') echo 'checked="checked"'; ?> /> <?php _e('Show post count', 'i-recommend-this'); ?></label></p>
				
				<input type="hidden" id="mrp-submit" name="mrp-submit" value="1" />
				<?php
			}
			wp_register_widget_control('most_recommended_posts', 'Most recommended posts', 'options_widget_most_recommended_posts');
		} 

	
	} // End Class
	
	global $dot_irecommendthis;
	
	// Initiation call of plugin
	$dot_irecommendthis = new DOT_IRecommendThis( $file );

}

	/*--------------------------------------------*
	 * Template Tag
	 *--------------------------------------------*/
	 
	function dot_irecommendthis()
	{
		global $dot_irecommendthis;
		echo $dot_irecommendthis->dot_recommend(); 
		
	}

	/*--------------------------------------------*
	 * Settings Menu
	 *--------------------------------------------*/
	
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'dot_irecommendthis_plugin_links' );

	function dot_irecommendthis_plugin_links($links) {	
		return array_merge(
			array(
				'settings' => '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=dot-irecommendthis">' . __('Settings', 'dot-irecommendthis') . '</a>'
			),
			$links
		);
	}

?>
