# I Recommend This - Plugin Documentation

## Table of Contents

1. [Plugin Overview](#plugin-overview)
2. [File Structure](#file-structure)
3. [Architecture](#architecture)
   - [Component-Based Design](#component-based-design)
   - [Design Patterns](#design-patterns)
4. [Core Components](#core-components)
   - [Main Plugin Class](#main-plugin-class)
   - [Admin Components](#admin-components)
   - [Public Components](#public-components)
   - [Shared Components](#shared-components)
5. [Data Flow](#data-flow)
   - [Recommendation Process](#recommendation-process)
   - [AJAX Interactions](#ajax-interactions)
6. [Database Schema](#database-schema)
7. [Extension Points](#extension-points)
8. [Security Considerations](#security-considerations)
9. [Developer Usage Guide](#developer-usage-guide)

## Plugin Overview

"I Recommend This" is a WordPress plugin that allows users to like or recommend posts with a simple click. It's designed to enhance user engagement by providing an alternative to commenting.

Key features:
- Recommendation/like buttons for posts
- Counter for likes
- Shortcodes to display most recommended posts
- Widget for most recommended posts
- Gutenberg block support
- GDPR compliance options
- Database optimization tools

The plugin follows modern WordPress development practices with an emphasis on:
- Object-oriented architecture
- Component-based design
- WordPress coding standards
- Security best practices
- Performant database interactions

## File Structure

The plugin is organized into a logical directory structure:

```
i-recommend-this/
├── admin/                  # Admin-facing functionality
│   ├── class-themeist-irecommendthis-admin.php              # Main admin class
│   ├── class-themeist-irecommendthis-admin-tools.php        # Admin tools
│   ├── css/                # Admin-specific styles
│   └── js/                 # Admin-specific scripts
├── build/                  # Compiled block editor assets
│   ├── index.asset.php     # Block dependencies
│   ├── index.js            # Compiled block JS
│   └── block.json          # Block registration
├── css/                    # Public-facing styles
│   ├── irecommendthis.css              # Default thumb style
│   └── irecommendthis-heart.css        # Heart style option
├── includes/               # Core plugin functionality
│   ├── block-registration.php                      # Block registration
│   ├── class-themeist-irecommendthis.php           # Main plugin class
│   ├── class-themeist-irecommendthis-ajax.php      # AJAX handler
│   ├── class-themeist-irecommendthis-shortcodes.php # Shortcodes
│   └── functions.php                               # Public functions
├── js/                     # Public-facing scripts
│   └── irecommendthis.js                           # Main frontend script
├── languages/              # Translation files
├── public/                 # Public-facing functionality
│   ├── class-themeist-irecommendthis-public.php             # Main public class
│   └── class-themeist-most-recommended-posts-widget.php     # Widget class
├── src/                    # Block editor source files
│   ├── index.js            # Block editor source
│   └── block.json          # Block configuration
├── i-recommend-this.php    # Main plugin file
└── Various configuration files (.editorconfig, .gitignore, etc.)
```

## Architecture

### Component-Based Design

The plugin employs a component-based architecture that separates responsibilities into distinct classes:

1. **Main Plugin Container**: The `Themeist_IRecommendThis` class serves as the main container and bootstraps other components.

2. **Public Components**: Handle the frontend functionality displayed to visitors.

3. **Admin Components**: Handle the admin functionality for WordPress administrators.

4. **Shared Components**: Handle functionality used by both public and admin areas.

Each component follows single responsibility principles, focusing on specific aspects of the plugin's functionality.

### Design Patterns

The plugin utilizes several design patterns:

1. **Factory Pattern**: The main plugin class creates instances of other component classes.

2. **Strategy Pattern**: Different implementations for recommendation styles and output formats.

3. **Dependency Injection**: Components receive dependencies via constructor parameters.

4. **Singleton Pattern**: Global instances are managed through WordPress globals.

5. **Observer Pattern**: WordPress hooks (actions/filters) are used to respond to events.

## Core Components

### Main Plugin Class

`class-themeist-irecommendthis.php` is the core plugin class that:

- Initializes the plugin
- Sets up plugin hooks
- Handles activation and database table creation
- Manages version and database updates
- Loads translations

Key methods:
- `__construct()`: Initializes the plugin with its core properties.
- `add_hooks()`: Adds WordPress action and filter hooks.
- `activate()`: Runs on plugin activation to set up database tables.
- `create_db_table()`: Creates the database table for storing votes.
- `update()`: Updates the database schema when needed.
- `update_check()`: Checks if database updates are needed.

### Admin Components

#### Admin Main Class (`class-themeist-irecommendthis-admin.php`)

Coordinates all admin functionality:

- Sets up admin hooks
- Manages settings menu registration
- Adds columns to post list table
- Manages settings fields

Methods:
- `add_admin_hooks()`: Registers all admin hooks
- `dot_irecommendthis_menu()`: Adds the settings page
- `dot_settings_page()`: Renders the settings page
- `dot_irecommendthis_settings()`: Registers settings fields
- `settings_validate()`: Validates form submissions
- `setup_recommends()`: Sets up recommendation data for new posts
- `dot_columns_head()`: Adds custom column to posts table
- `dot_column_content()`: Displays recommendation count in posts table
- `dot_column_register_sortable()`: Makes column sortable
- `dot_column_orderby()`: Handles column sorting

#### Admin Tools (`class-themeist-irecommendthis-admin-tools.php`)

Provides database tools for administrators:

- Database optimization
- Table information display
- Direct update options

Methods:
- `add_hooks()`: Registers hooks for the tools
- `add_tools_submenu()`: Adds submenu to tools menu
- `render_tools_page()`: Displays database tools interface
- `display_database_info()`: Shows database structure information
- `handle_database_update()`: Processes database update requests
- `process_direct_update()`: Handles URL-based update requests

### Public Components

#### Public Main Class (`class-themeist-irecommendthis-public.php`)

Manages public-facing functionality:

- Enqueues scripts and styles
- Adds the recommendation button to content
- Processes recommendation requests

Key methods:
- `add_public_hooks()`: Registers public hooks
- `enqueue_scripts()`: Adds necessary scripts and styles
- `dot_content()`: Filters content to add recommendation button
- `dot_recommend_this()`: Processes recommendation updates

#### Widget Class (`class-themeist-most-recommended-posts-widget.php`)

Implements a widget to display most recommended posts:

- Renders the widget
- Processes widget settings
- Queries for top posts
- Displays results

Methods:
- `__construct()`: Sets up widget properties
- `form()`: Displays widget settings form
- `update()`: Processes widget settings updates
- `widget()`: Renders the widget on the frontend
- `register_widget()`: Static method to register the widget

### Shared Components

#### AJAX Handler (`class-themeist-irecommendthis-ajax.php`)

Processes AJAX requests:

- Handles recommendation submissions
- Validates requests
- Processes recommendation updates
- Returns updated counts

Methods:
- `add_ajax_hooks()`: Registers AJAX hooks
- `ajax_callback()`: Processes AJAX recommendation requests

#### Shortcodes (`class-themeist-irecommendthis-shortcodes.php`)

Implements shortcodes for the plugin:

- `[irecommendthis]` - Display recommendation button
- `[irecommendthis_top_posts]` - Display top recommended posts

Methods:
- `register_shortcodes()`: Registers all shortcodes
- `shortcode_recommends()`: Handles recommendation button shortcode
- `recommend()`: Generates recommendation button HTML
- `shortcode_recommended_top_posts()`: Handles top posts shortcode
- `recommended_top_posts_output()`: Generates top posts HTML

#### Block Registration (`block-registration.php`)

Registers the Gutenberg block:

- Sets up block registration
- Handles block rendering
- Manages block attributes
- Processes shortcode output

Functions:
- `register_irecommendthis_block()`: Registers the block
- `irecommendthis_block_render_callback()`: Renders the block content

#### Public Functions (`functions.php`)

Provides theme API functions:

- `irecommendthis()`: Template tag to display recommendation button

## Data Flow

### Recommendation Process

The process for recommending a post follows this flow:

1. **User Interaction**: User clicks the recommend button.

2. **JavaScript Handler**: The `irecommendthis.js` script:
   - Prevents default click action
   - Checks if the user has already recommended (active class)
   - Sends AJAX request with post ID and nonce

3. **AJAX Processing**:
   - `class-themeist-irecommendthis-ajax.php` receives the request
   - Validates the nonce
   - Calls the processor to update recommendation

4. **Recommendation Processing**:
   - The public class handles the recommendation logic
   - Checks for duplicate recommendations via cookies/IP
   - Updates post meta with new count
   - If IP tracking is enabled, saves IP to database
   - Generates updated HTML for the button

5. **UI Update**:
   - JavaScript updates the button state
   - Updates the count display
   - Toggles active class

### AJAX Interactions

The plugin uses WordPress AJAX to handle recommendations without page reloads:

1. **Initialization**:
   - Scripts are localized with AJAX URL and nonce
   - Settings are passed to JavaScript

2. **Request**:
   - JavaScript constructs AJAX request
   - Sends post ID, nonce, and unrecommend flag

3. **Processing**:
   - `ajax.php` receives request via wp_ajax hook
   - Validates inputs and permissions
   - Processes the recommendation action

4. **Response**:
   - Returns HTML for updated button
   - JavaScript updates the DOM

## Database Schema

The plugin uses a custom table for tracking votes:

**Table**: `{prefix}irecommendthis_votes`

| Column  | Type           | Description                              |
|---------|----------------|------------------------------------------|
| id      | MEDIUMINT(9)   | Auto-increment primary key               |
| time    | TIMESTAMP      | When the vote was cast                   |
| post_id | BIGINT(20)     | The ID of the recommended post           |
| ip      | VARCHAR(45)    | IP address of the voter (if enabled)     |

**Indexes**:
- Primary key on `id`
- Index on `post_id` (idx_post_id)
- Index on `time` (idx_time)

Additionally, the plugin stores data in WordPress standard tables:

1. **Post Meta**:
   - `_recommended` - Stores the count of recommendations for each post

2. **Options**:
   - Plugin settings
   - Database version for schema updates
   - Plugin version

## Extension Points

The plugin provides several ways for developers to extend functionality:

### Filters

- `irecommendthis_before_count` - Modify the HTML output before count
- `plugin_action_links` - Modify plugin action links
- `plugin_row_meta` - Modify plugin row meta
- `the_content` - Content filter used to add recommendation button
- `widget_title` - Filter widget title

### Functions

Developers can use these functions in themes:

```php
// Display recommendation button
if ( function_exists( 'irecommendthis' ) ) {
    irecommendthis();
}

// Get recommendation button HTML
$button_html = irecommendthis( $post_id, false );

// Display top posts
echo do_shortcode( '[irecommendthis_top_posts number="5"]' );
```

### Shortcodes

```
[irecommendthis]
[irecommendthis id="123"]
[irecommendthis_top_posts number="5" post_type="post" container="li" show_count="1"]
```

### Gutenberg Block

The plugin includes a Gutenberg block that can be added to posts and pages through the block editor. The block includes settings for:

- Post ID selection
- Text alignment
- Using current post in query loops

## Security Considerations

The plugin implements several security measures:

1. **Data Sanitization**:
   - All user inputs are sanitized before use
   - Database queries use prepared statements
   - Output is escaped properly

2. **Nonce Verification**:
   - AJAX requests verified with nonces
   - Form submissions protected with nonces

3. **IP Handling**:
   - Option to disable IP address saving for GDPR compliance
   - IP address storage can be configured in settings

4. **Capability Checks**:
   - Admin functions restricted to appropriate capabilities
   - Settings only accessible to administrators

5. **XSS Protection**:
   - Output escaping for all dynamic content
   - Properly sanitized attributes

## Developer Usage Guide

### Basic Usage in Themes

Display recommendation button in a theme template:

```php
<?php if ( function_exists( 'irecommendthis' ) ) : ?>
    <?php irecommendthis(); ?>
<?php endif; ?>
```

With custom post ID:

```php
<?php if ( function_exists( 'irecommendthis' ) ) : ?>
    <?php irecommendthis( $post_id ); ?>
<?php endif; ?>
```

Return button HTML instead of displaying it:

```php
$button = irecommendthis( $post_id, false );
echo $button;
```

### Displaying Top Posts

Use shortcode in a template:

```php
<?php echo do_shortcode( '[irecommendthis_top_posts number="5" post_type="post"]' ); ?>
```

Full shortcode options:

```
[irecommendthis_top_posts 
    number="10" 
    post_type="post" 
    container="li" 
    year="2023" 
    monthnum="7" 
    show_count="1"
]
```

### Styling

Disable plugin CSS and add custom styles:

```css
.irecommendthis {
    padding-left: 2em;
    position: relative;
    text-decoration: none;
}

.irecommendthis::before {
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
    color: #FF5757;
}
```

### Adding Custom Output Before Count

Use the filter to add custom content:

```php
add_filter( 'irecommendthis_before_count', function( $output ) {
    // Add an icon before the count
    return '<i class="fas fa-heart"></i> ' . $output;
});
```

### Querying Top Posts Programmatically

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
