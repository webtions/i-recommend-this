# I Recommend This - Developer's Guide

This guide covers implementation methods, hooks, filters, and theme integration for developers working with the "I Recommend This" plugin.

## Implementation Methods

There are several ways to integrate the recommendation functionality into your themes and plugins:

### Template Tags

The template tag approach is the most direct way to add recommendation functionality to your theme templates:

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

### Shortcodes

To add recommendation functionality within post content or using page builders:

```
[irecommendthis]
```

Shortcode with parameters:

```
[irecommendthis id="123" wrapper="true"]
```

For displaying the most recommended posts:

```
[irecommendthis_top_posts number="5" container="div" show_count="1"]
```

### Block Editor

The plugin includes a Gutenberg block that can be added through the block editor. Look for the "I Recommend This" block in the widget category of the block inserter.

The block allows you to:
- Choose alignment
- Use the current post or specify a post ID
- Automatically detects and works in query loops

## Hook and Filter Reference

The plugin provides extensive hooks and filters for customization.

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
| `irecommendthis_ajax_post_id` | Filter the post ID before processing AJAX | `$post_id` |
| `irecommendthis_before_ajax_process` | Fired before processing AJAX recommendation request | `$post_id`, `$_POST` |
| `irecommendthis_after_ajax_process` | Fired after processing AJAX recommendation request | `$post_id`, `$result`, `$_POST` |
| `irecommendthis_ajax_response` | Filter the AJAX response HTML | `$result`, `$post_id` |

### Shortcode Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `irecommendthis_shortcodes_registered` | Fired after shortcodes are registered | None |
| `irecommendthis_shortcode_atts` | Filter the shortcode attributes before processing | `$atts` |
| `irecommendthis_button_html` | Filter the recommendation HTML before wrapping | `$irt_html`, `$post_id`, `$wrapper` |
| `irecommendthis_wrapper_class` | Filter the wrapper class name | `$wrapper_class`, `$post_id` |
| `irecommendthis_after_recommend` | Fired after recommendation link is generated | `$irt_html`, `$post_id`, `$action` |

### Top Posts Shortcode/Widget Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `irecommendthis_top_posts_atts` | Filter the top posts shortcode attributes | `$atts` |
| `irecommendthis_top_posts_sql` | Filter the SQL query for top posts | `$sql`, `$params`, `$atts` |
| `irecommendthis_top_post_link` | Filter the post link HTML | `$link_html`, `$item`, `$permalink`, `$post_title` |
| `irecommendthis_top_post_count` | Filter the count display HTML | `$count_html`, `$post_count`, `$item` |
| `irecommendthis_top_posts_html` | Filter the final top posts HTML | `$return`, `$posts`, `$atts` |

## Theme Integration Examples

### Adding Custom CSS Classes

You can add custom CSS classes to the recommendation wrapper:

```php
add_filter( 'irecommendthis_wrapper_class', function( $class, $post_id ) {
    // Add additional classes based on post ID or type
    if ( has_category( 'featured', $post_id ) ) {
        $class .= ' featured-recommendation';
    }
    return $class;
}, 10, 2 );
```

### Customizing the Count Display

```php
add_filter( 'irecommendthis_count_output', function( $output, $count, $post_id, $suffix ) {
    // Add an icon before the count
    $icon_html = '<i class="fas fa-heart"></i> ';
    return $icon_html . $output;
}, 10, 4 );
```

### Adding Thumbnails to Top Posts List

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

### Modifying Button Text Based on State

```php
add_filter( 'irecommendthis_button_html', function( $html, $post_id, $wrapper ) {
    // Replace the standard text with custom text
    $html = str_replace( 'Recommend this', 'I like this article', $html );
    $html = str_replace( 'Unrecommend this', 'Unlike this article', $html );

    return $html;
}, 10, 3 );
```

## Widget Implementation

The plugin includes a widget for displaying top recommended posts. To add it programmatically:

```php
// Register the widget
if ( ! function_exists( 'register_irecommendthis_widget' ) ) {
    function register_irecommendthis_widget() {
        register_widget( 'Themeist_IRecommendThis_Widget_Most_Recommended' );
    }
    add_action( 'widgets_init', 'register_irecommendthis_widget' );
}
```

Or simply use the WordPress admin widgets interface to add it to any widget area.