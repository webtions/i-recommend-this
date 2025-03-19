# I Recommend This - Advanced Usage

This document covers advanced usage scenarios, including customization, GDPR compliance, and caching integration.

## Advanced Customization Examples

### User Role Restrictions

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

### Storing Additional Data with Recommendations

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

In the plugin settings, you can disable IP tracking completely. This option can be found in the "I Recommend This" settings page in your WordPress admin area.

You can also disable IP tracking programmatically:

```php
// Programmatically disable IP tracking
$options = get_option( 'irecommendthis_settings' );
$options['enable_unique_ip'] = '0';
update_option( 'irecommendthis_settings', $options );
```

### Option 2: Use Anonymized IPs

The plugin uses secure one-way hashing to anonymize IP addresses. When IP tracking is enabled, IP addresses are never stored in their original form. Instead, they are converted into irreversible hashes using WordPress's cryptographic functions.

The anonymization process:
1. Creates an irreversible hash of the IP address
2. Uses WordPress's cryptographic functions for security
3. Makes it impossible to recover the original IP address
4. Still allows tracking unique votes

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

For sites using caching plugins, recommendation counts may not update immediately for all users. The plugin provides hooks to integrate with popular caching solutions.

### Clearing Cache on Recommendation Updates

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

### Selective Fragment Caching

For more targeted cache clearing:

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

## Troubleshooting

### Common Issues

1. **Recommendation button not showing**
   - Check if the template tag is properly added to your theme
   - Verify that the plugin is activated
   - Ensure there are no JavaScript errors on your page

2. **Counts not updating**
   - Check if a caching plugin is active and configure it to exclude AJAX requests
   - Verify that the database table is properly created
   - Check browser console for JavaScript errors

3. **Multiple recommendation clicks from same user**
   - Ensure cookies are working properly on your site
   - Check if IP tracking is enabled in the settings
   - Verify that your caching plugin is respecting cookies

### Debugging

You can enable WordPress debug mode to get more information:

```php
// Add to wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
```

For AJAX requests debugging, check the browser's developer tools Network tab to monitor the AJAX responses.