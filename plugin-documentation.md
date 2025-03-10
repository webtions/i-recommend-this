# I Recommend This - Plugin Documentation

## Table of Contents

1. [Overview](#overview)
2. [File Structure](#file-structure)
3. [Core Architecture](#core-architecture)
4. [Main Components](#main-components)
5. [Database Structure](#database-structure)
6. [Recommendation Process Flow](#recommendation-process-flow)
7. [Hooks and Filters Reference](#hooks-and-filters-reference)
8. [Theme Developer Guide](#theme-developer-guide)
9. [Advanced Recommendation Examples](#advanced-recommendation-examples)
10. [GDPR Compliance](#gdpr-compliance)
11. [Caching Integration](#caching-integration)

## Overview

"I Recommend This" is a WordPress plugin that adds a recommendation/like system to posts. It allows visitors to like content without commenting, tracks recommendation counts, and provides tools to display popular posts.

Key features:
- Like/recommend buttons for posts
- Recommendation counter
- Most recommended posts widget
- Shortcodes for displaying recommendation buttons and top posts
- Gutenberg block integration
- GDPR compliance options
- Extensive developer hooks for customization

## File Structure

```
i-recommend-this/
├── admin/                                # Admin functionality
│   ├── class-themeist-irecommendthis-admin.php           # Admin class
│   ├── class-themeist-irecommendthis-admin-settings.php  # Settings handler
│   ├── class-themeist-irecommendthis-admin-db-tools.php  # DB Tools
│   ├── class-themeist-irecommendthis-admin-ui.php        # Admin UI
├── assets/                               # Frontend assets
│   ├── css/                             # CSS stylesheets
│   │   ├── irecommendthis.css           # Default style (thumb)
│   │   ├── irecommendthis-heart.css     # Heart style
│   │   ├── admin-settings.css           # Admin styles
│   ├── js/                              # JavaScript files
│   │   ├── irecommendthis.js            # Main frontend JS
│   │   ├── admin-tabs.js                # Admin UI JS
├── blocks/                              # Block editor components
│   ├── blocks.php                       # Block registration manager
│   ├── recommend/                       # Recommendation block
│   │   ├── block.php                    # Block registration
│   │   ├── build/                       # Compiled JS
│   │   ├── src/                         # Source JS
├── core/                                # Core functionality
│   ├── class-themeist-irecommendthis.php              # Main plugin class
│   ├── class-themeist-irecommendthis-ajax.php         # AJAX processing
│   ├── class-themeist-irecommendthis-db-upgrader.php  # Database management
│   ├── class-themeist-irecommendthis-shortcodes.php   # Shortcodes
│   ├── functions.php                                 # Public functions
├── public/                               # Public-facing functionality
│   ├── class-themeist-irecommendthis-public.php           # Public class
│   ├── class-themeist-irecommendthis-public-assets.php    # Asset management
│   ├── class-themeist-irecommendthis-public-display.php   # Display handling
│   ├── class-themeist-irecommendthis-public-processor.php # Processor logic
│   ├── class-themeist-most-recommended-posts-widget.php   # Widget
├── i-recommend-this.php                  # Main plugin file
```

## Core Architecture

The plugin uses a modern component-based architecture that separates responsibilities into distinct classes. The main plugin file (`i-recommend-this.php`) acts as the entry point that initializes the core class and all required components.

Each component focuses on a specific functionality:
- The core plugin class (`Themeist_IRecommendThis`) handles activation, hooks, and database management
- The DB upgrader (`Themeist_IRecommendThis_DB_Upgrader`) handles database creation and updates
- Admin components handle settings, admin UI, and tools
- Public components handle frontend display, processing, and AJAX interactions
- Standalone components handle specific functionality like widgets and shortcodes

## Main Components

### Core Plugin Class

**Themeist_IRecommendThis** - `core/class-themeist-irecommendthis.php`

The main plugin class responsible for:
- Initializing the plugin
- Managing activation
- Loading translations
- Setting up hooks
- Migrating settings from older versions

Key methods:
- `add_hooks()`: Registers core hooks
- `activate()`: Handles plugin activation
- `migrate_plugin_settings()`: Manages settings migration
- `load_localisation()`: Loads translations
- `check_db_table()`: Verifies database tables

### Database Management

**Themeist_IRecommendThis_DB_Upgrader** - `core/class-themeist-irecommendthis-db-upgrader.php`

Responsible for:
- Creating and updating database tables
- Handling schema changes
- IP anonymization
- Database optimization

Key methods:
- `create_table()`: Creates the votes table
- `update()`: Updates database schema
- `anonymize_ip()`: Securely hashes IP addresses
- `maybe_anonymize_ips()`: Batch processes existing IPs

### AJAX Processing

**Themeist_IRecommendThis_Ajax** - `core/class-themeist-irecommendthis-ajax.php`

Handles AJAX requests:
- Processes recommendation requests
- Validates nonces
- Returns updated recommendation counts

Key methods:
- `ajax_callback()`: Processes AJAX requests
- `legacy_ajax_callback()`: Handles backward compatibility

### Recommendation Processing

**Themeist_IRecommendThis_Public_Processor** - `public/class-themeist-irecommendthis-public-processor.php`

Core business logic for processing recommendations:
- Handles recommendation counting
- Manages cookies and IP tracking
- Generates HTML output

Key methods:
- `process_recommendation()`: Main processing method
- `anonymize_ip()`: Securely anonymizes IPs

### Shortcodes

**Themeist_IRecommendThis_Shortcodes** - `core/class-themeist-irecommendthis-shortcodes.php`

Provides shortcodes:
- `[irecommendthis]`: Display recommendation button
- `[irecommendthis_top_posts]`: Display top posts

Key methods:
- `shortcode_recommends()`: Handles button shortcode
- `recommend()`: Generates button HTML
- `shortcode_recommended_top_posts()`: Handles top posts shortcode

### Widget

**Themeist_Most_Recommended_Posts_Widget** - `public/class-themeist-most-recommended-posts-widget.php`

Widget for displaying top posts:
- Displays most recommended posts
- Settings for count, display options

Key methods:
- `widget()`: Renders the widget
- `form()`: Displays widget settings form
- `update()`: Saves widget settings

## Database Structure

### Custom Table

**Table**: `{prefix}irecommendthis_votes`

| Column  | Type          | Description                              |
|---------|---------------|------------------------------------------|
| id      | MEDIUMINT(9)  | Auto-increment primary key               |
| time    | TIMESTAMP     | When the vote was cast                   |
| post_id | BIGINT(20)    | The ID of the recommended post           |
| ip      | VARCHAR(255)  | Anonymized IP address of the voter       |

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
   - JS sends AJAX request with post ID, nonce, and state information
   - Includes unrecommend flag if already recommended

3. **Server Processing**:
   - AJAX handler validates request
   - Checks for existing recommendation (cookie/IP)
   - Updates recommendation count
   - Stores anonymized IP (if enabled) and sets cookie
   - Generates updated HTML

4. **UI Update**:
   - JS updates button state and count
   - Changes active state and appearance
   - Updates aria-label and title attributes for accessibility

5. **Cache Clearing**:
   - `irecommendthis_after_process_recommendation` action fires
   - Cache clearing functions can be triggered for the specific post
   - Ensures visitors see updated recommendation counts even with caching

## Hooks and Filters Reference

### Core Hooks

**Initialization**

| Hook | Description | Parameters |
|------|-------------|------------|
| `irecommendthis_init` | Fired when the plugin has finished initializing | `$this` (Plugin instance) |
| `irecommendthis_before_activation` | Fired before plugin activation process begins | `$network_wide` (bool) |
| `irecommendthis_after_activation` | Fired after plugin activation is complete | `$network_wide` (bool) |

**Settings**

| Hook | Description | Parameters |
|------|-------------|------------|
| `irecommendthis_settings_migrated` | Fired after plugin settings have been migrated from old format | `$old_settings`, `$new_settings` |
| `irecommendthis_default_settings_created` | Fired after default plugin settings have been created | `$default_settings` |

**Database**

| Hook | Description | Parameters |
|------|-------------|------------|
| `irecommendthis_before_db_update` | Fired before database update | `$current_version`, `$new_version` |
| `irecommendthis_after_db_update` | Fired after database update | `$current_version`, `$new_version` |

### Recommendation Process Hooks

**Processing**

| Hook | Description | Parameters |
|------|-------------|------------|
| `irecommendthis_process_post_id` | Filter the post ID before processing recommendation | `$post_id` |
| `irecommendthis_pre_process_count` | Filter the current recommendation count before processing | `$recommended`, `$post_id`, `$action` |
| `irecommendthis_before_get_recommendation` | Fired before getting the recommendation count | `$post_id`, `$recommended` |
| `irecommendthis_before_update_recommendation` | Fired before updating the recommendation count | `$post_id`, `$recommended` |
| `irecommendthis_after_process_recommendation` | Fired after a post's recommendation count is updated | `$post_id`, `$recommended`, `$action` |

**Output**

| Hook | Description | Parameters |
|------|-------------|------------|
| `irecommendthis_count_output` | Filter the recommendation count HTML output | `$output`, `$recommended`, `$post_id`, `$suffix` |

**Events**

| Hook | Description | Parameters |
|------|-------------|------------|
| `irecommendthis_count_incremented` | Fired after incrementing recommendation count | `$post_id`, `$recommended`, `$cookie_exists` |
| `irecommendthis_count_decremented` | Fired after decrementing recommendation count | `$post_id`, `$recommended`, `$cookie_exists` |
| `irecommendthis_ip_record_added` | Fired after adding an IP record | `$post_id`, `$anonymized_ip` |
| `irecommendthis_ip_record_deleted` | Fired after deleting an IP record | `$post_id`, `$anonymized_ip` |

### AJAX Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `irecommendthis_ajax_hooks_registered` | Fired after AJAX hooks are registered | None |
| `irecommendthis_before_legacy_ajax` | Fired before legacy AJAX request is processed | `$_POST` |
| `irecommendthis_ajax_post_id` | Filter the post ID before processing AJAX | `$post_id` |
| `irecommendthis_ajax_nonce_failure` | Fired when nonce verification fails | `$post_id` |
| `irecommendthis_before_ajax_process` | Fired before processing AJAX recommendation request | `$post_id`, `$_POST` |
| `irecommendthis_after_ajax_process` | Fired after processing AJAX recommendation request | `$post_id`, `$result`, `$_POST` |
| `irecommendthis_ajax_response` | Filter the AJAX response HTML | `$result`, `$post_id` |

### Shortcode Hooks

**Button Shortcode**

| Hook | Description | Parameters |
|------|-------------|------------|
| `irecommendthis_shortcodes_registered` | Fired after shortcodes are registered | None |
| `irecommendthis_shortcode_atts` | Filter the shortcode attributes before processing | `$atts` |
| `irecommendthis_before_recommend` | Fired before recommendation link is generated | `$id`, `$action`, `$wrapper` |
| `irecommendthis_button_html` | Filter the recommendation HTML before wrapping | `$irt_html`, `$post_id`, `$wrapper` |
| `irecommendthis_wrapper_class` | Filter the wrapper class name | `$wrapper_class`, `$post_id` |
| `irecommendthis_after_recommend` | Fired after recommendation link is generated | `$irt_html`, `$post_id`, `$action` |

**Top Posts Shortcode**

| Hook | Description | Parameters |
|------|-------------|------------|
| `irecommendthis_top_posts_atts` | Filter the top posts shortcode attributes | `$atts` |
| `irecommendthis_before_top_posts_query` | Fired before querying for top posts | `$atts` |
| `irecommendthis_top_posts_sql` | Filter the SQL query for top posts | `$sql`, `$params`, `$atts` |
| `irecommendthis_before_top_posts_output` | Fired before rendering top posts list | `$posts`, `$atts` |
| `irecommendthis_top_post_open_tag` | Filter the opening HTML tag for each top post item | `$open_tag`, `$container`, `$item` |
| `irecommendthis_top_post_link` | Filter the post link HTML | `$link_html`, `$item`, `$permalink`, `$post_title` |
| `irecommendthis_top_post_count` | Filter the count display HTML | `$count_html`, `$post_count`, `$item` |
| `irecommendthis_top_post_close_tag` | Filter the closing HTML tag for each top post item | `$close_tag`, `$container`, `$item` |
| `irecommendthis_top_posts_html` | Filter the final top posts HTML | `$return`, `$posts`, `$atts` |
| `irecommendthis_after_top_posts_output` | Fired after rendering top posts list | `$return`, `$posts`, `$atts` |

### Widget Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `irecommendthis_widget_constructed` | Fired after widget is constructed | `$this` |
| `irecommendthis_widget_registered` | Fired after widget is registered | None |
| `irecommendthis_widget_defaults` | Filter widget default values | `$defaults` |
| `irecommendthis_before_widget_form` | Fired before widget form is rendered | `$instance` |
| `irecommendthis_widget_form` | Action to add additional widget form fields | `$instance`, `$this` |
| `irecommendthis_after_widget_form` | Fired after widget form is rendered | `$instance` |
| `irecommendthis_before_widget_update` | Fired before widget settings are updated | `$new_instance`, `$old_instance` |
| `irecommendthis_widget_update` | Filter widget settings before saving | `$instance`, `$new_instance`, `$old_instance` |
| `irecommendthis_after_widget_update` | Fired after widget settings are updated | `$instance`, `$old_instance` |
| `irecommendthis_before_widget` | Fired before widget output | `$args`, `$instance` |
| `irecommendthis_before_widget_list` | Fired before widget list | `$instance` |
| `irecommendthis_widget_list_tag` | Filter the widget list HTML tag | `$list_tag` |
| `irecommendthis_widget_list_class` | Filter the widget list CSS class | `$list_class` |
| `irecommendthis_widget_query` | Filter the SQL query for the widget | `$sql`, `$number_of_posts` |
| `irecommendthis_widget_posts` | Filter the query results | `$posts`, `$instance` |
| `irecommendthis_widget_item_tag` | Filter the widget item HTML tag | `$item_tag` |
| `irecommendthis_before_widget_item` | Fired before widget item | `$item` |
| `irecommendthis_widget_item_link` | Filter the widget item link | `$link_html`, `$item` |
| `irecommendthis_widget_item_count` | Filter the widget item count HTML | `$count_html`, `$post_count`, `$item` |
| `irecommendthis_after_widget_item` | Fired after widget item | `$item` |
| `irecommendthis_after_widget_list` | Fired after widget list | `$instance` |
| `irecommendthis_after_widget` | Fired after widget output | `$args`, `$instance` |

### Admin Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `irecommendthis_settings_initialized` | Fired after settings component is initialized | None |
| `irecommendthis_before_register_fields` | Fired before registering settings fields | None |
| `irecommendthis_register_fields` | Action to register custom settings fields | `$page`, `$section` |
| `irecommendthis_pre_validate_settings` | Filter settings input before validation | `$input` |
| `irecommendthis_validate_settings` | Filter settings after validation | `$input` |

## Theme Developer Guide

### Adding the Recommendation Button to a Theme

Basic template tag usage:

```php
<?php
if ( function_exists( 'irecommendthis' ) ) {
    irecommendthis();
}
?>
```

Advanced usage with options:

```php
<?php
if ( function_exists( 'irecommendthis' ) ) {
    // Parameters:
    // 1. Post ID (null = current post)
    // 2. Echo output (true) or return it (false)
    // 3. Add wrapper div (true) or not (false)
    irecommendthis( get_the_ID(), true, true );
}
?>
```

Returning the HTML for custom placement:

```php
<?php
if ( function_exists( 'irecommendthis' ) ) {
    $button = irecommendthis( get_the_ID(), false );
    echo '<div class="my-custom-wrapper">' . $button . '</div>';
}
?>
```

### Customizing the Button Appearance

Using the wrapper class filter:

```php
add_filter( 'irecommendthis_wrapper_class', function( $class, $post_id ) {
    // Add additional classes based on post ID or type
    if ( has_category( 'featured', $post_id ) ) {
        $class .= ' featured-recommendation';
    }
    return $class;
}, 10, 2 );
```

Customizing the HTML before the count:

```php
add_filter( 'irecommendthis_count_output', function( $output, $count, $post_id, $suffix ) {
    // Add an icon before the count
    $icon_html = '<i class="fas fa-heart"></i> ';
    return $icon_html . $output;
}, 10, 4 );
```

### Displaying Most Recommended Posts

Using the shortcode in a template:

```php
<?php
echo do_shortcode( '[irecommendthis_top_posts number="5" container="div" show_count="1"]' );
?>
```

Customizing the output with filters:

```php
// Change the title formatting for top posts
add_filter( 'irecommendthis_top_post_link', function( $link_html, $item, $permalink, $post_title ) {
    return '<a href="' . esc_url( $permalink ) . '" class="top-recommendation">' .
           '<span class="post-title">' . esc_html( $post_title ) . '</span></a>';
}, 10, 4 );

// Change the count display
add_filter( 'irecommendthis_top_post_count', function( $count_html, $post_count, $item ) {
    return '<span class="recommendation-count">' . $post_count . ' people like this</span>';
}, 10, 3 );
```

### Working with Post Thumbnails

Adding thumbnails to the top posts shortcode:

```php
add_filter( 'irecommendthis_top_post_link', function( $link_html, $item, $permalink, $post_title ) {
    $thumbnail = '';
    if ( has_post_thumbnail( $item->ID ) ) {
        $thumbnail = get_the_post_thumbnail( $item->ID, 'thumbnail' );
    }

    return '<a href="' . esc_url( $permalink ) . '" title="' . esc_attr( $post_title ) . '">' .
           $thumbnail . '<span class="title">' . esc_html( $post_title ) . '</span></a>';
}, 10, 4 );
```

## Advanced Recommendation Examples

### Changing Button Text Based on State

You can dynamically change the text based on the recommendation state:

```php
add_filter( 'irecommendthis_button_html', function( $html, $post_id, $wrapper ) {
    // Replace the standard text with custom text
    $html = str_replace( 'Recommend this', 'I like this article', $html );
    $html = str_replace( 'Unrecommend this', 'Unlike this article', $html );

    return $html;
}, 10, 3 );
```

### Limiting Recommendations by User Role

You can limit who can make recommendations based on user roles:

```php
add_filter( 'irecommendthis_before_ajax_process', function( $post_id, $post_data ) {
    // Only allow logged-in users to recommend
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Please log in to recommend posts' ) );
        exit;
    }

    // Or limit to specific roles
    $user = wp_get_current_user();
    if ( ! in_array( 'subscriber', $user->roles ) ) {
        wp_send_json_error( array( 'message' => 'Only subscribers can recommend posts' ) );
        exit;
    }
}, 10, 2 );
```

### Adding Custom Data to Recommendations

You can store additional data with each recommendation:

```php
add_action( 'irecommendthis_ip_record_added', function( $post_id, $anonymized_ip ) {
    // Store the current user ID with the recommendation
    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id();
        add_post_meta( $post_id, '_recommendation_by_user_' . $user_id, time() );
    }
}, 10, 2 );
```

### Creating a User's Recommendation List

Display a list of posts the user has recommended:

```php
function get_user_recommended_posts() {
    $recommended_posts = array();

    // Check cookies for recommendations
    foreach ($_COOKIE as $name => $value) {
        if ( strpos( $name, 'irecommendthis_' ) === 0 ) {
            $post_id = str_replace( 'irecommendthis_', '', $name );
            $recommended_posts[] = intval( $post_id );
        }
    }

    return $recommended_posts;
}

// Usage example
function display_user_recommendations() {
    $recommended_posts = get_user_recommended_posts();

    if ( ! empty( $recommended_posts ) ) {
        echo '<div class="your-recommendations">';
        echo '<h3>Posts You\'ve Recommended</h3>';
        echo '<ul>';

        foreach ( $recommended_posts as $post_id ) {
            echo '<li><a href="' . get_permalink( $post_id ) . '">' .
                 get_the_title( $post_id ) . '</a></li>';
        }

        echo '</ul>';
        echo '</div>';
    }
}
```

## GDPR Compliance

The plugin offers two approaches to GDPR compliance:

### Option 1: Disable IP Tracking

In the plugin settings, you can disable IP tracking completely:

```php
// Programmatically disable IP tracking
$options = get_option( 'irecommendthis_settings' );
$options['enable_unique_ip'] = '0';
update_option( 'irecommendthis_settings', $options );
```

### Option 2: Use Anonymized IPs

The plugin uses secure one-way hashing to anonymize IP addresses:

```php
// The anonymization function
public static function anonymize_ip( $ip ) {
    // Empty IPs should return a consistent hash
    if ( empty( $ip ) ) {
        $ip = 'unknown';
    }

    // Use WordPress salt for authentication
    $auth_salt = wp_salt( 'auth' );

    // Use site-specific hash for additional entropy
    $site_hash = defined( 'COOKIEHASH' ) ? COOKIEHASH : md5( site_url() );

    // Create the hash using WordPress hash function with site context
    $hashed_ip = wp_hash( $ip . $site_hash, 'auth' );

    return $hashed_ip;
}
```

This approach:
- Creates an irreversible hash of the IP address
- Uses WordPress's cryptographic functions for security
- Makes it impossible to recover the original IP address
- Still allows tracking unique votes

### Custom Cookie Settings

You can customize cookie settings for better privacy:

```php
add_filter( 'irecommendthis_cookie_parameters', function( $params, $post_id ) {
    // Customize cookie parameters
    $params['expires'] = time() + MONTH_IN_SECONDS; // 30 days instead of 1 year
    $params['samesite'] = 'Lax'; // Less restrictive than 'Strict'

    return $params;
}, 10, 2 );
```

## Caching Integration

The plugin provides a dedicated hook for integration with caching plugins:

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

You can also selectively clear only fragments:

```php
function clear_recommendation_fragment_cache( $post_id, $count, $action ) {
    if ( 'update' === $action ) {
        // For object caching systems
        $cache_key = 'irecommendthis_count_' . $post_id;
        wp_cache_delete( $cache_key, 'irecommendthis' );

        // For fragment caching plugins
        if ( function_exists( 'clearcache_recommend_fragment' ) ) {
            clearcache_recommend_fragment( $post_id );
        }
    }
}
add_action( 'irecommendthis_after_process_recommendation', 'clear_recommendation_fragment_cache', 10, 3 );
```
