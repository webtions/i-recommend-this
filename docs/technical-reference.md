# I Recommend This - Technical Reference

This document provides a technical reference for the functions, methods, and classes available for developers.

## Public Functions

### Template Tags

#### `irecommendthis()`

Displays or returns the recommendation button for a post.

```php
irecommendthis( $id = null, $should_echo = true, $wrapper = true )
```

**Parameters:**
- `$id` (int|null): The post ID. If null, the current post ID is used.
- `$should_echo` (bool): Whether to echo the output (true) or return it (false).
- `$wrapper` (bool): Whether to wrap the output in a container div.

**Returns:**
- (string|void): The recommendation button HTML if `$should_echo` is false.

**Example:**
```php
// Display recommendation button for current post
irecommendthis();

// Get HTML for a specific post without wrapper
$button = irecommendthis( 123, false, false );
```

#### `dot_irecommendthis()`

Backward compatibility function for the old function name. Deprecated as of version 4.0.0.

```php
dot_irecommendthis( $id = null, $should_echo = true, $wrapper = true )
```

Same parameters and return values as `irecommendthis()`.

## Shortcodes

### `[irecommendthis]`

Displays a recommendation button.

**Attributes:**
- `id` (int): Post ID. Default: current post ID
- `use_current_post` (bool): Whether to use the current post in loops. Default: false
- `wrapper` (bool): Whether to add a wrapper div. Default: true

**Example:**
```
[irecommendthis id="123" wrapper="true"]
```

### `[irecommendthis_top_posts]`

Displays a list of the most recommended posts.

**Attributes:**
- `container` (string): HTML element to use for each item. Default: "li"
- `number` (int): Number of posts to display. Default: 10
- `post_type` (string): Post type to display. Default: "post"
- `year` (int): Filter by year. Default: empty
- `monthnum` (int): Filter by month. Default: empty
- `show_count` (bool): Whether to show the count. Default: 1
- `wrapper` (string): Wrapper element around the list. Default: empty
- `wrapper_class` (string): Class for the wrapper. Default: "irecommendthis-top-posts"

**Example:**
```
[irecommendthis_top_posts number="5" container="div" show_count="1" year="2023"]
```

## Classes and Methods

### Core Classes

#### `Themeist_IRecommendThis`

The main plugin class responsible for initialization and core functionality.

**Key Methods:**
- `add_hooks()`: Registers core hooks
- `activate( $network_wide )`: Handles plugin activation
- `migrate_plugin_settings()`: Manages settings migration
- `load_localisation()`: Loads translations
- `check_db_table()`: Verifies database tables
- `get_version()`: Returns the plugin version
- `get_db_upgrader()`: Returns the database upgrader instance

#### `Themeist_IRecommendThis_Public_Processor`

Handles the processing of recommendations.

**Key Methods:**
- `process_recommendation( $post_id, $text_zero_suffix, $text_one_suffix, $text_more_suffix, $action, $unrecommend )`: Processes a recommendation action
- `anonymize_ip( $ip )`: Securely anonymizes an IP address

#### `Themeist_IRecommendThis_Shortcodes`

Manages the shortcodes provided by the plugin.

**Key Methods:**
- `register_shortcodes()`: Registers all shortcodes
- `shortcode_recommends( $atts )`: Handles the recommendation button shortcode
- `recommend( $id, $action, $wrapper )`: Generates the recommendation button HTML
- `shortcode_recommended_top_posts( $atts )`: Handles the top posts shortcode
- `recommended_top_posts_output( $atts )`: Generates the top posts HTML

#### `Themeist_IRecommendThis_Ajax`

Handles AJAX processing for the plugin.

**Key Methods:**
- `add_ajax_hooks()`: Registers AJAX hooks
- `ajax_callback()`: Processes AJAX recommendation requests

### Admin Classes

#### `Themeist_IRecommendThis_Admin`

Coordinates all admin functionality.

**Key Methods:**
- `add_admin_hooks()`: Registers admin hooks
- `add_settings_menu()`: Adds the settings menu
- `setup_recommends( $post_id )`: Sets up recommendations for a new post

#### `Themeist_IRecommendThis_Admin_Settings`

Manages plugin settings.

**Key Methods:**
- `initialize()`: Initializes the settings component
- `register_settings()`: Registers settings fields
- `validate_settings( $input )`: Validates settings before saving

#### `Themeist_IRecommendThis_Admin_DB_Tools`

Provides database tools for the admin area.

**Key Methods:**
- `initialize()`: Initializes the DB tools component
- `handle_database_update_request()`: Handles database update requests
- `display_database_info()`: Displays database table information

### Public Classes

#### `Themeist_IRecommendThis_Public`

Manages public-facing functionality.

**Key Methods:**
- `add_public_hooks()`: Registers public hooks
- `process_recommendation( $post_id, $text_zero_suffix, $text_one_suffix, $text_more_suffix, $action )`: Static method for recommendation processing

#### `Themeist_IRecommendThis_Public_Assets`

Handles assets for the public-facing side.

**Key Methods:**
- `initialize()`: Initializes the assets component
- `enqueue_scripts()`: Enqueues scripts and styles

#### `Themeist_IRecommendThis_Public_Display`

Manages display aspects of the plugin.

**Key Methods:**
- `initialize()`: Initializes the display component
- `modify_content( $content )`: Modifies post content to add recommendation buttons

### Widget Class

#### `Themeist_IRecommendThis_Widget_Most_Recommended`

Widget for displaying the most recommended posts.

**Key Methods:**
- `__construct()`: Constructor setting up the widget
- `form( $instance )`: Renders the widget admin form
- `update( $new_instance, $old_instance )`: Updates widget settings
- `widget( $args, $instance )`: Renders the widget on the front end
- `register_widget()`: Static method to register the widget

## Database Methods

### `Themeist_IRecommendThis_DB_Upgrader`

Handles database operations and upgrades.

**Key Methods:**
- `init()`: Initializes hooks
- `check_for_updates()`: Checks if database needs an update
- `create_table()`: Creates the database table
- `update()`: Updates the database schema
- `update_ip_column_size( $table_name )`: Updates IP column size
- `ensure_indexes( $table_name )`: Ensures all necessary indexes exist
- `maybe_anonymize_ips( $table_name )`: Anonymizes IP addresses
- `table_exists()`: Checks if the database table exists
- `get_table_info()`: Gets database table information
- `get_db_version()`: Gets the current database version

## Constants

- `THEMEIST_IRT_VERSION`: The current plugin version
- `THEMEIST_IRT_DB_VERSION`: The current database schema version

## Filter and Action Reference

See the [Developers Guide](developers.md) for a complete list of filters and actions available for customization.
