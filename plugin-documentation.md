# I Recommend This - Plugin Documentation

## Table of Contents

1. [Overview](#overview)
2. [File Structure](#file-structure)
3. [Core Architecture](#core-architecture)
4. [Main Components](#main-components)
   - [Core Plugin Class](#core-plugin-class)
   - [Admin Components](#admin-components)
   - [Frontend Components](#frontend-components)
   - [Block Editor Support](#block-editor-support)
5. [Database Structure](#database-structure)
6. [Recommendation Process Flow](#recommendation-process-flow)
7. [Hooks and Customization](#hooks-and-customization)
8. [Theme Developer Guide](#theme-developer-guide)
9. [Advanced Recommendation Examples](#advanced-recommendation-examples)

## Overview

"I Recommend This" is a WordPress plugin that adds a recommendation/like system to posts. It allows visitors to like content without commenting, tracks recommendation counts, and provides tools to display popular posts.

Key features:
- Like/recommend buttons for posts
- Recommendation counter
- Most recommended posts widget
- Shortcodes for displaying recommendation buttons and top posts
- Gutenberg block integration
- GDPR compliance options

## File Structure

```
i-recommend-this/
├── admin/                                # Admin functionality
│   ├── class-themeist-irecommendthis-admin.php           # Admin class
│   ├── class-themeist-irecommendthis-admin-tools.php     # Admin tools
├── build/                                # Block editor compiled files
│   ├── block.json                        # Block registration
│   ├── index.asset.php                   # Block dependencies
│   ├── index.js                          # Compiled JS
├── css/                                  # Frontend styles
│   ├── irecommendthis.css                # Default style (thumb)
│   ├── irecommendthis-heart.css          # Heart style
├── includes/                             # Core functionality
│   ├── block-registration.php            # Block registration code
│   ├── class-themeist-irecommendthis.php # Main plugin class
│   ├── class-themeist-irecommendthis-ajax.php # AJAX processing
│   ├── class-themeist-irecommendthis-shortcodes.php # Shortcodes
│   ├── functions.php                     # Public functions (template tags)
├── js/                                   # Frontend scripts
│   ├── irecommendthis.js                 # Main JS functionality
├── languages/                            # Translation files
├── public/                               # Public-facing functionality
│   ├── class-themeist-irecommendthis-public.php # Public class
│   ├── class-themeist-irecommendthis-public-assets.php # Assets handling
│   ├── class-themeist-irecommendthis-public-display.php # Display handling
│   ├── class-themeist-irecommendthis-public-processor.php # Core processing
│   ├── class-themeist-most-recommended-posts-widget.php # Widget
├── src/                                  # Block editor source
│   ├── block.json                        # Block configuration
│   ├── index.js                          # Block editor JS source
├── i-recommend-this.php                  # Main plugin file
```

## Core Architecture

The plugin uses a component-based architecture that separates responsibilities into distinct classes. The main plugin file (`i-recommend-this.php`) acts as the entry point that initializes the core class and all required components.

Each component focuses on a specific functionality:
- The core plugin class (`Themeist_IRecommendThis`) handles activation, database setup, and updates
- Admin components handle settings, admin UI, and tools
- Public components handle frontend display, processing, and AJAX interactions
- Standalone components handle specific functionality like widgets and shortcodes

## Main Components

### Core Plugin Class

**Themeist_IRecommendThis** - `includes/class-themeist-irecommendthis.php`

The main plugin class that:
- Initializes the plugin
- Manages activation and database table creation
- Handles database updates
- Loads translations

Key methods:
- `__construct()`: Sets up plugin properties
- `add_hooks()`: Registers core hooks
- `activate()`: Activation handler
- `create_db_table()`: Creates database table
- `update()`: Updates database schema
- `load_localisation()`: Loads translations

### Admin Components

**Themeist_IRecommendThis_Admin** - `admin/class-themeist-irecommendthis-admin.php`

Handles admin functionality:
- Registers settings page
- Adds admin menu items
- Manages plugin settings
- Adds post list columns

Key methods:
- `add_admin_hooks()`: Registers admin hooks
- `add_settings_menu()`: Adds settings page
- `render_settings_page()`: Displays settings
- `register_settings()`: Registers plugin settings
- `validate_settings()`: Validates user input
- `setup_recommends()`: Initializes recommendation count for new posts

**Themeist_IRecommendThis_Admin_Tools** - `admin/class-themeist-irecommendthis-admin-tools.php`

Provides database maintenance tools:
- Database optimization
- Database info display
- Schema updates

Key methods:
- `add_tools_submenu()`: Adds tools submenu
- `render_tools_page()`: Displays tools interface
- `handle_database_update()`: Processes updates
- `display_database_info()`: Shows table details

### Frontend Components

**Themeist_IRecommendThis_Public** - `public/class-themeist-irecommendthis-public.php`

Manages public-facing functionality:
- Coordinates frontend components
- Provides access to processor

Key methods:
- `add_public_hooks()`: Registers public hooks
- `process_recommendation()`: Static method for processing recommendations

**Themeist_IRecommendThis_Public_Assets** - `public/class-themeist-irecommendthis-public-assets.php`

Handles frontend assets:
- Enqueues scripts and styles
- Localizes JavaScript

Key methods:
- `enqueue_scripts()`: Registers and enqueues assets

**Themeist_IRecommendThis_Public_Display** - `public/class-themeist-irecommendthis-public-display.php`

Handles frontend display:
- Modifies content to add recommendation buttons

Key methods:
- `modify_content()`: Filters content to add buttons

**Themeist_IRecommendThis_Public_Processor** - `public/class-themeist-irecommendthis-public-processor.php`

Core recommendation processing:
- Processes recommendation requests
- Updates counts
- Manages cookies and IP tracking

Key methods:
- `process_recommendation()`: Handles getting/updating recommendations
- `anonymize_ip()`: Anonymizes IP addresses for GDPR compliance

**Themeist_Most_Recommended_Posts_Widget** - `public/class-themeist-most-recommended-posts-widget.php`

Widget for displaying top posts:
- Displays most recommended posts
- Settings for count, display options

Key methods:
- `widget()`: Renders the widget
- `form()`: Displays widget settings form
- `update()`: Saves widget settings

**Themeist_IRecommendThis_Ajax** - `includes/class-themeist-irecommendthis-ajax.php`

Processes AJAX requests:
- Receives recommendation submissions
- Validates requests
- Returns updated counts

Key methods:
- `add_ajax_hooks()`: Registers AJAX endpoints
- `ajax_callback()`: Processes AJAX requests

**Themeist_IRecommendThis_Shortcodes** - `includes/class-themeist-irecommendthis-shortcodes.php`

Provides shortcodes:
- `[irecommendthis]`: Display recommendation button
- `[irecommendthis_top_posts]`: Display top posts

Key methods:
- `register_shortcodes()`: Registers all shortcodes
- `shortcode_recommends()`: Handles button shortcode
- `recommend()`: Generates button HTML
- `shortcode_recommended_top_posts()`: Handles top posts shortcode

### Block Editor Support

**Block Registration** - `includes/block-registration.php`

Registers Gutenberg block:
- Registers block type
- Provides render callback

Key functions:
- `register_irecommendthis_block()`: Registers block
- `irecommendthis_block_render_callback()`: Renders block

**Block Source** - `src/index.js`

Block editor implementation:
- Edit component
- Save component
- Block controls

## Database Structure

### Custom Table

**Table**: `{prefix}irecommendthis_votes`

| Column  | Type           | Description                              |
|---------|----------------|------------------------------------------|
| id      | MEDIUMINT(9)   | Auto-increment primary key               |
| time    | TIMESTAMP      | When the vote was cast                   |
| post_id | BIGINT(20)     | The ID of the recommended post           |
| ip      | VARCHAR(255)   | Anonymized IP address of the voter       |

**Indexes**:
- Primary key on `id`
- Index on `post_id`
- Index on `time`

### WordPress Data

**Post Meta**:
- `_recommended` - Recommendation count for each post

**Options**:
- `irecommendthis_settings` - Plugin settings
- `irecommendthis_db_version` - Database version for schema updates
- `irecommendthis-version` - Plugin version
- `irecommendthis_ip_migration_complete` - Flag for IP anonymization completion

## Recommendation Process Flow

The recommendation process follows this workflow:

1. **User Interaction**:
   - User clicks recommendation button
   - JavaScript intercepts click

2. **AJAX Request**:
   - JS sends AJAX request with post ID and nonce
   - Includes unrecommend flag if already recommended

3. **Server Processing**:
   - AJAX handler validates request
   - Checks for existing recommendation (cookie/IP)
   - Updates recommendation count
   - Stores IP (if enabled) and sets cookie
   - Generates updated HTML

4. **UI Update**:
   - JS updates button state and count
   - Changes active state and appearance
   - Updates aria-label and title attributes for accessibility

5. **Cache Clearing** (new in 4.0.0):
   - If integrated with caching plugins, the `irecommendthis_after_process_recommendation` hook fires
   - Cache clearing functions can be triggered for the specific post
   - Ensures visitors see updated recommendation counts even with caching

## Hooks and Customization

### Filters

- `irecommendthis_before_count` - Modify HTML output before count display
- `the_content` - Used to add recommendation button to content

### Actions

- `irecommendthis_after_process_recommendation` - Fires after a post's recommendation count is updated, with params:
  - `$post_id` (int) - The ID of the post that was recommended
  - `$recommended` (int) - The updated recommendation count
  - `$action` (string) - The action performed: 'get' or 'update'

### Template Tags

```php
// Display recommendation button
irecommendthis( $post_id = null, $should_echo = true );
```

### Shortcodes

```
// Display recommendation button
[irecommendthis]
[irecommendthis id="123"]

// Display top posts
[irecommendthis_top_posts number="5" post_type="post" container="li" show_count="1"]
```

### Gutenberg Block

The "I Recommend This" block provides:
- Post ID selection
- Text alignment options
- Option to use current post in query loops

## Theme Developer Guide

### Adding Recommendation Button to Theme

Basic usage:

```php
<?php if ( function_exists( 'irecommendthis' ) ) : ?>
    <?php irecommendthis(); ?>
<?php endif; ?>
```

With specific post ID:

```php
<?php if ( function_exists( 'irecommendthis' ) ) : ?>
    <?php irecommendthis( $post_id ); ?>
<?php endif; ?>
```

Getting the HTML instead of displaying:

```php
<?php if ( function_exists( 'irecommendthis' ) ) : ?>
    <?php $button = irecommendthis( $post_id, false ); ?>
    <div class="my-custom-wrapper">
        <?php echo $button; ?>
    </div>
<?php endif; ?>
```

### Styling the Archive/Index Button Wrapper

On archive and index pages, the recommendation button is now wrapped in a paragraph with a class:

```html
<p class="irecommendthis-wrapper">
    <!-- Recommendation button -->
</p>
```

You can target this wrapper with CSS for better layout control:

```css
.irecommendthis-wrapper {
    margin-top: 1em;
    text-align: right;
}

/* Different alignment on specific page types */
.home .irecommendthis-wrapper {
    text-align: center;
}

.archive .irecommendthis-wrapper {
    border-top: 1px solid #eee;
    padding-top: 0.5em;
}
```

### Displaying Most Recommended Posts

Using shortcode in template:

```php
<?php echo do_shortcode( '[irecommendthis_top_posts number="5"]' ); ?>
```

Custom query example:

```php
global $wpdb;
$top_posts = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT p.ID, p.post_title, pm.meta_value AS recommended_count
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_status = 'publish'
        AND p.post_type = %s
        AND pm.meta_key = '_recommended'
        ORDER BY CAST(pm.meta_value AS UNSIGNED) DESC
        LIMIT %d",
        'post',
        5
    )
);

foreach ( $top_posts as $post ) {
    echo '<a href="' . get_permalink( $post->ID ) . '">' .
         get_the_title( $post->ID ) . ' (' . $post->recommended_count . ')</a><br>';
}
```

### Custom Styling

Disable plugin CSS in settings and add custom styles:

```css
.irecommendthis {
    /* Button base styles */
    padding-left: 2em;
    position: relative;
    text-decoration: none;
}

.irecommendthis::before {
    /* Icon styles */
    content: '';
    display: inline-block;
    width: 1.5em;
    height: 1.5em;
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    background-image: url('your-icon.svg');
    background-size: contain;
    background-repeat: no-repeat;
}

.irecommendthis.active {
    /* Active state */
    color: #FF5757;
}

.irecommendthis-count {
    /* Count styling */
    font-weight: bold;
}
```

### Adding Custom Content Before Count

Use the filter to add custom content:

```php
add_filter( 'irecommendthis_before_count', function( $output ) {
    // Add an icon before the count
    return '<i class="fas fa-heart"></i> ' . $output;
});
```

### Checking If User Has Recommended

```php
function has_user_recommended( $post_id ) {
    return isset( $_COOKIE[ 'irecommendthis_' . $post_id ] );
}

if ( has_user_recommended( get_the_ID() ) ) {
    echo 'You already recommended this post!';
} else {
    echo 'Please recommend this post if you like it!';
}
```

## Advanced Recommendation Examples

### Checking User Recommendation Status

Sometimes you may want to check if a user has already recommended a post to customize their experience. Here's a helper function:

```php
/**
 * Check if the current user has already recommended a post
 *
 * @param int $post_id The post ID to check
 * @return bool True if user has recommended the post
 */
function has_user_recommended( $post_id ) {
    return isset( $_COOKIE[ 'irecommendthis_' . $post_id ] );
}
```

### Customizing Content Based on Recommendation Status

You can show different content to users who have recommended your post:

```php
if ( has_user_recommended( get_the_ID() ) ) {
    echo '<div class="thank-you-message">Thanks for recommending this post!</div>';
} else {
    echo '<div class="please-recommend">If you enjoyed this content, please recommend it!</div>';
}
```

### Integration with Caching Plugins

The plugin now provides a dedicated hook for caching plugin integration. Here's an example with popular caching plugins:

```php
/**
 * Clear cache for a post when its recommendation count changes
 */
function my_clear_post_cache_on_recommendation( $post_id, $count, $action ) {
    // Only run on actual updates
    if ( 'update' === $action ) {
        // WP Rocket
        if ( function_exists( 'rocket_clean_post' ) ) {
            rocket_clean_post( $post_id );
        }

        // W3 Total Cache
        if ( function_exists( 'w3tc_flush_post' ) ) {
            w3tc_flush_post( $post_id );
        }

        // WP Super Cache
        if ( function_exists( 'wp_cache_post_change' ) ) {
            wp_cache_post_change( $post_id );
        }

        // LiteSpeed Cache
        if ( class_exists( 'LiteSpeed\Core' ) ) {
            do_action( 'litespeed_purge_post', $post_id );
        }
    }
}
add_action( 'irecommendthis_after_process_recommendation', 'my_clear_post_cache_on_recommendation', 10, 3 );
```

### Enhanced HTML Attributes

Version 4.0.0 adds enhanced HTML attributes for better accessibility and JavaScript interaction:

```html
<a href="#"
   class="irecommendthis"
   data-post-id="123"
   data-like="Recommend this"
   data-unlike="Unrecommend this"
   aria-label="Recommend this"
   title="Recommend this">
   <span class="irecommendthis-count">5</span>
   <span class="irecommendthis-suffix">likes</span>
</a>
```

The new attributes make it easier to create custom behavior with JavaScript. For example:

```javascript
// Toggle button text based on state
jQuery('.irecommendthis').on('click', function() {
    var $this = jQuery(this);
    var newText = $this.hasClass('active') ? $this.data('unlike') : $this.data('like');
    $this.find('.like-text').text(newText);
});
```

### Creating a User's Recommendation List

Display a list of posts the user has recommended:

```php
function get_user_recommended_posts() {
    $recommended_posts = array();

    // Check cookies for recommendations
    foreach ($_COOKIE as $name => $value) {
        if (strpos($name, 'irecommendthis_') === 0) {
            $post_id = str_replace('irecommendthis_', '', $name);
            $recommended_posts[] = intval($post_id);
        }
    }

    return $recommended_posts;
}

// Usage example
$recommended_posts = get_user_recommended_posts();
if (!empty($recommended_posts)) {
    echo '<div class="your-recommendations">';
    echo '<h3>Posts You\'ve Recommended</h3>';
    echo '<ul>';
    foreach ($recommended_posts as $post_id) {
        echo '<li><a href="' . get_permalink($post_id) . '">' . get_the_title($post_id) . '</a></li>';
    }
    echo '</ul>';
    echo '</div>';
}
```

For more detailed examples and implementation ideas, see our blog post: [Advanced Customization with I Recommend This](https://themeist.com/docs/advanced-recommendation-examples/).
