=== I Recommend This ===
Contributors: themeist, hchouhan
Donate link: https://themeist.com
Tags: recommend, like, love, post, rate
Requires at least: 6.0
Tested up to: 6.3.2
Stable tag: 3.9.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enable your visitors to easily like or recommend your posts with a single click, enhancing engagement without the need for comments.

== Description ==

"I Recommend This" allows your visitors to effortlessly like or recommend your posts with a single click, making it easier for them to show their appreciation without leaving a comment. Enhance your site’s engagement by providing a simple, user-friendly way for readers to interact with your content.

## Features:

- Display a counter for likes/recommendations.
- Widget and shortcode to display the most liked posts.
- Choose between a "Thumbs Up" or "Heart" icon.
- Prevents multiple votes from the same user via cookies and IP address tracking.
- View and sort posts by likes in the post edit page.


### Advanced Options:

- Hide the counter if the count is zero.
- Customize messages for zero, one, or multiple likes.
- Disable plugin CSS for custom styling.
- Option to disable IP address saving to comply with GDPR.

### Shortcodes:

- `[dot_recommends]` - Add the voting link to any page.
- `[dot_recommended_posts post_type='post' container='div' number='10' year='2023' monthnum='7']` - Display most recommended posts.

### Example Sites Using the Plugin:

- [Flat UI Design Gallery](http://flattrendz.com)




== Other Notes ==

This plugin is based exactly on Benoit "LeBen" Burgener's "I Like This" Plugin and has been modified after getting requests for the changes I had made on my website.

**If you love the plugin, please consider rating it and clicking on "it works" button.**

For further assistance and detailed documentation, visit the [official plugin page](https://themeist.com/plugins/wordpress/i-recommend-this/).

### For Developers:

This plugin is being developed on GitHub.. If you want to collaborate, please look at [I Recommend This plugin on GitHub](https://github.com/hchouhan/I-Recommend-This).

#### Translations

You can [help translate this plugin into your language](https://translate.wordpress.org/projects/wp-plugins/i-recommend-this/stable/) using your WordPress.org account.


== Screenshots ==

1. The settings panel where you can configure the plugin.
2. Example of the like/recommend button on a post.
3. Displaying the most liked posts using the widget.


== Installation ==

1. **Upload the Plugin Files**
   - Download the `i-recommend-this` plugin zip file from the WordPress plugin repository.
   - Unzip the downloaded file.
   - Using an FTP client, upload the entire `i-recommend-this` folder to the `/wp-content/plugins/` directory on your WordPress server.

2. **Activate the Plugin**
   - Log in to your WordPress admin dashboard.
   - Navigate to `Plugins` > `Installed Plugins`.
   - Locate `I Recommend This` in the list and click the `Activate` link.

3. **Configure Plugin Settings**
   - After activation, click on `Settings` under the `I Recommend This` plugin name on the plugins page or navigate to `Settings` > `I Recommend This`.
   - Configure the settings as per your requirements, including display options, text suffixes, and styles.

4. **Display the Recommend/Like Button**
   - By default, the recommend/like button is added to the bottom of individual posts.
   - To display the recommend/like button in a custom location, add the following code to your theme template files (e.g., `single.php`):
     `<?php if ( function_exists( 'dot_irecommendthis' ) ) dot_irecommendthis(); ?>`

5. **Use Shortcodes**
   - To display the recommend/like button on any page or post, use the `[dot_recommends]` shortcode.
   - To display the most recommended posts, use the `[dot_recommended_posts]` shortcode with customizable attributes. Example:
     `[dot_recommended_posts post_type='post' container='div' number='10' year='2023' monthnum='7']`

6. **Display the Most Recommended Posts**
   - To display the most recommended posts in your theme templates, use the following code:
     `<?php if ( function_exists( 'dot_irecommendthis' ) ) echo do_shortcode( "[dot_recommended_posts container='div' post_type='post' number='10' year='2023' monthnum='7']" ); ?>`

7. **Add the Most Recommended Posts Widget**
   - The plugin includes a widget to display the most recommended posts.
   - To add the widget:
     1. Go to `Appearance` > `Widgets` in the WordPress admin dashboard.
     2. Locate the `Most Recommended Posts` widget in the list of available widgets.
     3. Drag the widget to the desired widget area (e.g., sidebar, footer).
     4. Configure the widget settings, including the title, number of posts to display, and other options.
     5. Click `Save` to add the widget to your site.

== Frequently Asked Questions ==

#### How do I customize the look of the recommend button?
You can customize the look of the recommend button via the plugin settings. Navigate to `Settings` > `I Recommend This` in your WordPress dashboard to choose from different heart or thumbs-up icon. You can also disable the plugin’s default CSS and use your own custom styles.

#### How can I prevent users from recommending the same post multiple times?
The plugin uses cookies and IP address tracking to prevent multiple recommendations from the same user. These settings can be configured in the plugin’s settings panel.

#### Can I disable the counter if no recommendations have been made?
Yes, you can choose to hide the counter if the count is zero. This option can be found in the plugin’s settings.

#### How do I display the most recommended posts?
You can display the most recommended posts using the `[dot_recommended_posts]` shortcode or the included widget. Customize the attributes of the shortcode to fit your needs.

#### Is the plugin GDPR compliant?
Yes, the plugin includes an option to disable IP address saving to comply with GDPR regulations. You can enable this option in the plugin settings.

#### Can I use the recommend button on custom post types?
Yes, the recommend button can be added to any post type. You can use the `[dot_recommends]` shortcode to place the button on custom post types.

#### Does the plugin work with caching plugins?
Yes, "I Recommend This" is compatible with most caching plugins. However, you may need to exclude the recommendation button from being cached to ensure it updates correctly in real-time.

#### How do I integrate the plugin with my theme?
Yes, you can integrate the recommend button directly into your theme by adding the following code to your theme template files:
`<?php if ( function_exists( 'dot_irecommendthis' ) ) dot_irecommendthis(); ?>`

#### Where can I learn more about this plugin?
Take a look at the [official "I Recommend This" FAQ](https://themeist.com/plugins/wordpress/i-recommend-this/).

#### How to get support?
You can also visit the [support center](https://wordpress.org/support/plugin/i-recommend-this/) and start a discussion if needed.

#### Reporting Security Bugs
Please report security bugs found in the source code through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/i-recommend-this). The Patchstack team will assist you with verification, CVE assignment and take care of notifying the developers of this plugin.

== Upgrade Notice ==

= 3.9.1 =
Fixes multiple security issues

== Changelog ==

= 3.9.1 =
* Security update

= 3.9.0 =
* Added support for un-recommending/un-liking a post
* Fixed data sanitization
* Fixed data escaping

= 3.8.1 =
* Added data sanitization
* Separate & recoded widget
* Support for multiple widget instances

= 3.8.0 =
* Added data sanitization
* Made IP saving options
* Restructured plugin code

= 3.7.8 =
* Vulnerabilities fixed by DannyvanKooten

= 3.7.7 =
* Dutch Translation added by Tim de Hoog
* Added support for IPv6

= 3.7.6 =
* Fixed version number in dot-irecommendthis.php

= 3.7.5 =
* Updated Readme

= 3.7.4 =
* The template tag now accepts a POST ID, thanks to [Oskar Adin](https://github.com/osadi).

= 3.7.3 =
* Fixed a possible SQL injection vulnerability reported by [Oskar Adin](https://github.com/osadi) and fixed by [Danny van Kooten](https://twitter.com/DannyvanKooten).

= 3.7.2 =
* Updated 'dot_irecommendthis.js' file to make the plugin work even when the like button is on a hidden element. Thanks to [forthewinn](http://wordpress.org/support/profile/forthewinn). [Support Ticket](http://wordpress.org/support/topic/recommendation-to-fix-usage-in-hiddenexpanding-elements)

= 3.7.1 =
* Spanish translation added. Thanks to Andrew Kurtis from [WebHostingHub](http://www.webhostinghub.com/)

= 3.7.0 =
* Removed wrong tags set for this plugin earlier. My sincere apologies for the extra update mess.

= 2.6.5 =
* Replaced deprecated jQuery function `live` with `on` in dot_irecommendthis.js

= 2.6.4 =
* Moved enqueued JS from wp_head() to wp_footer().

= 2.6.3 =
* Fixed undefined index errors for disable_unique_ip, link_title_new & link_title_active. Thanks to [sebabornia](http://wordpress.org/support/profile/sebabornia)

= 2.6.2 =
* French translation added. Thanks to Murat from [wptheme.fr](http://wptheme.fr/)

= 2.6.1 =
* Updates to Persian translation

= 2.6.0 =
* Added Persian translation - Thanks to [HSG](http://profiles.wordpress.org/HSG/)
* Added the number of likes on the Post Edit page along with a sorting option. Thanks to [HSG](http://profiles.wordpress.org/HSG/)

= 2.5.3 =
* Fixed PHP error in Widget.
* Converted text strings in the widget to be translatable.

= 2.5.2 =
* Replaced 'before' & 'after' attributes in shortcode 'dot_recommended_posts' with a single attribute 'container'

= 2.5.1 =
* Changed shortcode name from dot_recommends_posts to dot_recommended_posts

= 2.5.0 =
* Added new shortcode with multiple options to display the most recommended post/post_type of all time or from a specific date

= 2.4.2 =
* Bug fix. Thanks to @mmaxim

= 2.4.1 =
* Fixed undefined index error.

= 2.4.0 =
* Added filter dot_irt_before_count to allow custom content or icons before the count.

= 2.3.0 =
* Added option to hide count if count is zero
* Added option to disable saving of IP address in the database

= 2.2.0 =
* Added option to customize the link title. You can now remove the word recommend and add anything you like. Ideas suggested by Krystina Montemurro.

= 2.1.5 =
* Support URL update for new plugin details page.

= 2.1.4 =
* Removed 2 instances of double quotes. Thanks to [boyevul](http://profiles.wordpress.org/boyevul/)

= 2.1.3 =
* Fixed errors shown when Debug mode was on. Thanks to [Air](http://profiles.wordpress.org/air-1/)

= 2.1.2 =
* Fixed CSS Disable issue. Thanks to Nicolas Mollet.

= 2.1 =
* Fixed Naming Errors. Thanks to Marian Hillmar.
* Fixed Shortcode name & Added support to place like button anywhere pointing to any post. Thanks to Bryant Williams for the code.

= 2.0 =
* This is a major revamp. The entire plugin structure is now based on OOP
* Settings are now stored using Settings API and Settings page is created based on WordPress standards.
* This plugin contains code from "Zilla Like" plugin developed by Orman Clark of www.themezilla.com.
* Translation files are finally added.

= 1.4.3 =
* All deprecated functions removed. Plugin might not work on WordPress versions older than 3.

= 1.4.1 =
* Fixed bug that broke update.

= 1.4 =
* Added feature to display custom text when a post is liked.

= 1.3 =
* Removed 2 functions "register_widget_control()" & "register_sidebar_widget()" deprecated in version 2.8 with latest functions

= 1.2 =
* More bugs fixed.

= 1.1 =
* Fixed Bug that did not allow displaying text next to the counter.
* Updated code using branch of original plugin on GitHub

= 1.0 =
* Removed JQuery loading style when heart is clicked.
* Modified CSS & Images of LeBen's "I Like This" plugin based on what many users requested.
* This is the first version
