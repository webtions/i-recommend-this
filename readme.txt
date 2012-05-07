=== Plugin Name ===
Contributors: Harish Chouhan, Benoit "LeBen" Burgener
Donate link: http://www.designerskiosk.com
Tags: recommend, like, love, post, rate, rating, heart
Requires at least: 3.0
Tested up to: 3.2.2
Stable tag: 1.3
Last Updated: 2012-May-7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows your visitors to simply like/recommend your posts instead of comment on it.


== Description ==

This plugin allows your visitors to simply like/recommend your posts instead of comment on it.


= This plugin includes =
* A counter/button to display the number of "like" and to vote.
* A widget and a function to display the X most liked posts.
* A preference pane with some options.


This plugin is based exactly on Benoit "LeBen" Burgener's "I Like This" Plugin and has been modified after getting requests for the changes I had made on my website.

Please report any bugs you find via http://www.harishchouhan.com/personal-projects/i-recommend-this/

= Examples of how the plugin has been used =

* [Harish's blog](http://www.harishchouhan.com/blog/)
* [Designers Kiosk](http://www.designerskiosk.com)


If you love the plugin, please consider rating it and clicking on "it works" button.


== Installation ==

1. Upload the directory `/i-recommend-this/` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

To display the recomment/like link other than at the bottom of individual post, you would have to add below code in your template
<?php if(function_exists(getIRecommendThis)) getIRecommendThis('get'); ?>

== Frequently Asked Questions ==

Take a look at the [official "I Recommend This" FAQ](http://www.harishchouhan.com/personal-projects/i-recommend-this/).

You can also visit the [support center](http://www.harishchouhan.com/personal-projects/i-recommend-this/) and start a discussion if needed.



== Changelog ==

= 1.3
* Removed 2 functions "register_widget_control()" & "register_sidebar_widget()" deprecated in version 2.8 with latest functions


= 1.2
* More bugs fixed.


= 1.1
* Fixed Bug that did not allow displaying text next to the counter.
* Updated code using branch of original plugin on GitHub


= 1.0
* Removed JQuery loading style when heart is clicked.
* Modified CSS & Images of LeBen's "I Like This" plugin based on what many users requested.
* This is the first version