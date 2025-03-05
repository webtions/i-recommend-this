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

## Hooks and Customization

### Filters

- `irecommendthis_before_count` - Modify HTML output before count display
- `the_content` - Used to add recommendation button to content

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
