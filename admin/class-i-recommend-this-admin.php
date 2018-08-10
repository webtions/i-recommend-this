<?php

class Themeist_IRecommendThis_Admin {

	/*--------------------------------------------*
	 * Admin Menu
	 *--------------------------------------------*/

	function dot_irecommendthis_menu()
	{
		$page_title = __('I Recommend This', 'i-recommend-this');
		$menu_title = __('I Recommend This', 'i-recommend-this');
		$capability = 'manage_options';
		$menu_slug = 'dot-irecommendthis';
		$function = array(&$this, 'dot_settings_page');
		add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);

	}    //dot_irecommendthis_menu

}