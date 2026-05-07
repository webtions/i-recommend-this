# I Recommend This - Overview

## Purpose and Key Features

"I Recommend This" is a WordPress plugin that adds a recommendation/like system to posts. It allows visitors to like content without commenting, tracks recommendation counts, and provides tools to display popular posts.

### Key Features

- Like/recommend buttons for posts with a simple click interaction
- Recommendation counter with customizable display options
- Most recommended posts widget for displaying popular content
- Shortcodes for displaying recommendation buttons and top posts
- Gutenberg block integration for modern WordPress sites
- GDPR compliance options including IP anonymization
- Extensive developer hooks for customization
- REST API: `irt_likes` on core post REST responses and an authenticated `POST .../irecommendthis/v1/posts/{id}/like` endpoint (for headless/mobile clients; see developer docs)

## File Structure

```
i-recommend-this/
├── admin/                                # Admin functionality
│   ├── class-themeist-irecommendthis-admin.php           # Admin class
│   ├── class-themeist-irecommendthis-admin-settings.php  # Settings handler
│   ├── class-themeist-irecommendthis-admin-db-tools.php  # DB Tools
│   ├── class-themeist-irecommendthis-admin-ui.php        # Admin UI
│   ├── class-themeist-irecommendthis-admin-post-columns.php  # Admin columns
│   ├── class-themeist-irecommendthis-admin-plugin-links.php  # Plugin links
├── assets/                               # Frontend assets
│   ├── css/                              # CSS stylesheets
│   │   ├── irecommendthis.css            # Default style (thumb)
│   │   ├── irecommendthis-heart.css      # Heart style
│   │   ├── admin-settings.css            # Admin styles
│   ├── js/                               # JavaScript files
│   │   ├── irecommendthis.js             # Main frontend JS
│   │   ├── admin-tabs.js                 # Admin UI JS
├── blocks/                               # Block editor components
│   ├── blocks.php                        # Block registration manager
│   ├── recommend/                        # Recommendation block
│   │   ├── block.php                     # Block registration
│   │   ├── build/                        # Compiled block assets
│   │   │   ├── block.json                # Block metadata
│   │   │   ├── index.asset.php           # Asset dependencies
│   │   │   ├── index.js                  # Compiled JavaScript
│   │   ├── src/                          # Block source files
│   │   │   ├── block.json                # Block configuration
│   │   │   ├── index.js                  # Block implementation
│   │   │   ├── index.php                 # Security file
├── core/                                 # Core functionality
│   ├── class-themeist-irecommendthis.php              # Main plugin class
│   ├── class-themeist-irecommendthis-ajax.php         # AJAX processing
│   ├── class-themeist-irecommendthis-rest.php         # REST API fields and routes
│   ├── class-themeist-irecommendthis-db-upgrader.php  # Database management
│   ├── class-themeist-irecommendthis-shortcodes.php   # Shortcodes
│   ├── functions.php                                  # Public functions
├── public/                               # Public-facing functionality
│   ├── class-themeist-irecommendthis-public.php            # Public class
│   ├── class-themeist-irecommendthis-public-assets.php     # Asset management
│   ├── class-themeist-irecommendthis-public-display.php    # Display handling
│   ├── class-themeist-irecommendthis-public-processor.php  # Processor logic
│   ├── class-themeist-irecommendthis-widget-most-recommended.php # Widget
├── i-recommend-this.php                  # Main plugin file
```

## Core Architecture

The plugin uses a modern component-based architecture that separates responsibilities into distinct classes. The main plugin file (`i-recommend-this.php`) acts as the entry point that initializes the core class and all required components.

Each component focuses on a specific functionality:

1. **Core Plugin Class** (`Themeist_IRecommendThis`)
   - Initializes the plugin
   - Manages activation hooks
   - Loads translations
   - Sets up core hooks
   - Migrates settings from older versions

2. **Database Management** (`Themeist_IRecommendThis_DB_Upgrader`)
   - Creates and updates database tables
   - Handles schema changes
   - Manages IP anonymization
   - Performs database optimization

3. **Admin Components**
   - Settings management
   - UI rendering
   - Database tools
   - Post columns integration

4. **Public Components**
   - Frontend asset loading
   - Display handling
   - Recommendation processing
   - Widget functionality

5. **AJAX Processing** (`Themeist_IRecommendThis_Ajax`)
   - Processes recommendation requests
   - Validates security nonces
   - Returns updated recommendation counts

6. **REST API** (`Themeist_IRecommendThis_Rest`)
   - Registers the `irt_likes` field on allowed post types in `wp/v2` responses
   - Exposes `POST /wp-json/irecommendthis/v1/posts/{id}/like` for authenticated clients
   - Reuses the same recommendation processor as AJAX (cookie/IP rules apply)

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

### REST API path (headless / mobile)

For authenticated HTTP clients (e.g. Application Passwords over HTTPS): **read** the like count via `GET /wp-json/wp/v2/posts` or a single post (`irt_likes`). **Write** a like or unlike using `POST /wp-json/irecommendthis/v1/posts/{id}/like`, optionally with `unrecommend=true` (same semantics as AJAX). Server-side logic matches the AJAX flow; details and hooks are in `docs/developers.md` and `docs/technical-reference.md`.