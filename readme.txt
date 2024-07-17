=== I Recommend This ===
Contributors: themeist, hchouhan
Donate link: https://themeist.com
Tags: recommend, like, love, post, rate
Requires at least: 6.0
Tested up to: 6.6
Stable tag: 3.10.2
Requires PHP: 7.4
License: GPL-3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

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

- `[irecommendthis]` - Add the voting link to any page.
- `[irecommendthis_top_posts post_type='post' container='div' number='10' year='2023' monthnum='7']` - Display most recommended posts.


This plugin is based exactly on Benoit "LeBen" Burgener's "I Like This" Plugin and has been modified after getting requests for the changes I had made on my website.

**If you love the plugin, please consider rating it and clicking on "it works" button.**


### Example Sites Using the Plugin:

- [Flat UI Design Gallery](http://flattrendz.com)

### For Developers:

This plugin is being developed on GitHub.. If you want to collaborate, please look at [I Recommend This plugin on GitHub](https://github.com/hchouhan/I-Recommend-This).

### Translations

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
     `<?php if ( function_exists( 'irecommendthis' ) ) irecommendthis(); ?>`

5. **Use Shortcodes**
   - To display the recommend/like button on any page or post, use the `[irecommendthis]` shortcode.
   - To display the most recommended posts, use the `[irecommendthis_top_posts]` shortcode with customizable attributes. Example:
     `[irecommendthis_top_posts post_type='post' container='div' number='10' year='2023' monthnum='7']`

6. **Display the Most Recommended Posts**
   - To display the most recommended posts in your theme templates, use the following code:
     ```<?php if ( function_exists( 'irecommendthis' ) ) echo do_shortcode( "[irecommendthis_top_posts container='div' post_type='post' number='10' year='2023' monthnum='7']" ); ?>`

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
You can display the most recommended posts using the `[irecommendthis_top_posts]` shortcode or the included widget. Customize the attributes of the shortcode to fit your needs.

#### Is the plugin GDPR compliant?
Yes, the plugin includes an option to disable IP address saving to comply with GDPR regulations. You can enable this option in the plugin settings.

#### Can I use the recommend button on custom post types?
Yes, the recommend button can be added to any post type. You can use the `[irecommendthis]` shortcode to place the button on custom post types.

#### Does the plugin work with caching plugins?
Yes, "I Recommend This" is compatible with most caching plugins. However, you may need to exclude the recommendation button from being cached to ensure it updates correctly in real-time.

#### How do I integrate the plugin with my theme?
Yes, you can integrate the recommend button directly into your theme by adding the following code to your theme template files:
```<?php if ( function_exists( 'irecommendthis' ) ) irecommendthis(); ?>`

#### Where can I learn more about this plugin?
Take a look at the [official "I Recommend This" FAQ](https://themeist.com/plugins/wordpress/i-recommend-this/).

#### How to get support?
You can also visit the [support center](https://wordpress.org/support/plugin/i-recommend-this/) and start a discussion if needed.

#### Reporting Security Bugs
Please report security bugs found in the source code through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/i-recommend-this). The Patchstack team will assist you with verification, CVE assignment and take care of notifying the developers of this plugin.

== Upgrade Notice ==


== Changelog ==

= 3.10.2 =
* Fix: Template tag not outputting the recommend link

= 3.10.1 =
* Fix: Correct post ID parsing in AJAX request to ensure proper recommendation handling

= 3.10.0 =
* Security update
* Code Refactor
* Added Block

= 3.9.1 =
Fixes multiple security issues

= 3.9.1 =
* Security update

