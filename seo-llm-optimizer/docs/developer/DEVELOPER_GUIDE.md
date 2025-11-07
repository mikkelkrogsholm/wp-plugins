# SEO & LLM Optimizer - Developer Guide

Technical documentation for developers extending or integrating with the SEO & LLM Optimizer plugin.

**Version**: 1.0.0
**Last Updated**: 2025-11-07

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Core Classes](#core-classes)
3. [WordPress Hooks](#wordpress-hooks)
4. [API Integration](#api-integration)
5. [Extending the Plugin](#extending-the-plugin)
6. [Code Examples](#code-examples)
7. [Testing](#testing)

---

## Architecture Overview

### Design Pattern

The plugin uses the **Singleton pattern** for all main classes, ensuring only one instance of each component exists.

```php
class SLO_Example_Class {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Initialize
    }
}
```

### File Structure

```
seo-llm-optimizer/
├── seo-llm-optimizer.php        # Plugin entry point, loads all components
├── uninstall.php                 # Cleanup on plugin deletion
├── composer.json                 # Composer dependencies
│
├── includes/                     # Core functionality
│   ├── class-content-processor.php     # Markdown conversion orchestrator
│   ├── class-content-cleaner.php       # HTML cleaning and markdown conversion
│   ├── class-chunking-engine.php       # Content chunking with multiple strategies
│   ├── class-cache-manager.php         # WordPress transient-based caching
│   ├── class-rest-api.php              # REST API endpoints
│   ├── class-frontend-button.php       # Frontend button rendering
│   ├── class-modal-handler.php         # Modal dialog handling
│   └── class-admin-settings.php        # Settings page management
│
├── admin/                        # Admin-specific functionality
│   └── class-meta-boxes.php            # Post meta boxes
│
├── assets/                       # Frontend assets
│   ├── css/
│   │   ├── frontend.css                # Frontend button and modal styles
│   │   └── admin.css                   # Admin settings styles
│   └── js/
│       ├── frontend.js                 # Frontend button and modal logic
│       └── admin.js                    # Admin settings interactions
│
├── templates/                    # PHP templates
│   ├── frontend/
│   │   └── modal.php                   # Modal HTML structure
│   └── admin/
│       └── settings-page.php           # Settings page HTML
│
├── languages/                    # Translation files
│   └── seo-llm-optimizer.pot          # Translation template
│
└── vendor/                       # Composer dependencies
    └── league/html-to-markdown/        # HTML to Markdown library
```

### Component Interactions

```
User Action (Frontend)
    ↓
Frontend Button (class-frontend-button.php)
    ↓
AJAX Request
    ↓
Content Processor (class-content-processor.php)
    ├── Content Cleaner (class-content-cleaner.php)
    ├── Chunking Engine (class-chunking-engine.php)
    └── Cache Manager (class-cache-manager.php)
    ↓
Response (JSON)
    ↓
Frontend Modal Display
```

```
REST API Request
    ↓
REST API Handler (class-rest-api.php)
    ├── Authentication Check
    ├── Rate Limiting
    └── Permission Verification
    ↓
Content Processor / Chunking Engine
    ↓
Cached Result (if available)
    ↓
JSON Response
```

---

## Core Classes

### 1. SLO_Content_Processor

**Location**: `includes/class-content-processor.php`

**Purpose**: Main orchestrator for content processing. Coordinates cleaning, conversion, and metadata generation.

**Key Methods**:

```php
// Convert post to markdown
$processor = SLO_Content_Processor::get_instance();
$markdown = $processor->convert_to_markdown($post_id, array(
    'include_metadata' => true,
    'include_images'   => true,
    'preserve_links'   => true,
));

// Returns: string (markdown) or WP_Error
```

**Processing Pipeline**:
1. Detect content type (Gutenberg vs Classic)
2. Process blocks or apply filters
3. Clean WordPress elements
4. Enhance semantic structure
5. Convert to markdown
6. Add YAML frontmatter (optional)

**Filters Available**:
```php
// Modify markdown before returning
apply_filters('slo_markdown_output', $markdown, $post_id);

// Modify YAML frontmatter data
apply_filters('slo_frontmatter_data', $data, $post_id);
```

---

### 2. SLO_Content_Cleaner

**Location**: `includes/class-content-cleaner.php`

**Purpose**: Cleans HTML and converts to markdown using League\HTMLToMarkdown.

**Key Methods**:

```php
$cleaner = SLO_Content_Cleaner::get_instance();

// Remove shortcodes but keep content
$html = $cleaner->strip_shortcodes_preserve_content($html);

// Remove embeds
$html = $cleaner->remove_wordpress_embeds($html);

// Strip theme elements
$html = $cleaner->strip_theme_elements($html);

// Enhance semantic structure
$html = $cleaner->enhance_semantic_structure($html);

// Convert to markdown
$markdown = $cleaner->convert_to_markdown($html);
```

**Cleaning Operations**:
- Removes navigation menus
- Strips sidebars and widgets
- Removes theme-specific elements
- Preserves semantic content
- Converts tables, lists, and formatting

**Markdown Conversion**:
Uses `league/html-to-markdown` library with custom configuration:
- Preserves headers (H1-H6)
- Converts links and images
- Handles lists and tables
- Strips scripts and styles

---

### 3. SLO_Chunking_Engine

**Location**: `includes/class-chunking-engine.php`

**Purpose**: Splits content into semantic chunks using multiple strategies.

**Key Methods**:

```php
$chunker = SLO_Chunking_Engine::get_instance();

// Create chunks
$chunks = $chunker->create_chunks($post_id, 'hierarchical', array(
    'chunk_size' => 512,
    'overlap'    => 128,
    'format'     => 'universal',
));

// Get cached chunks (faster)
$chunks = $chunker->get_cached_chunks($post_id, 'hierarchical', $options);

// Format for specific systems
$langchain_format = $chunker->format_for_langchain($chunks);
$llamaindex_format = $chunker->format_for_llamaindex($chunks);
$universal_format = $chunker->format_universal($chunks, $post_id);
```

**Chunking Strategies**:

1. **Hierarchical** (`chunk_by_headers`):
   ```php
   // Splits by markdown headers
   // Preserves document structure
   // Each section = one chunk
   ```

2. **Fixed Size** (`chunk_fixed_size`):
   ```php
   // Fixed-size chunks with overlap
   // Respects sentence boundaries
   // Consistent chunk sizes
   ```

3. **Semantic** (`chunk_semantically`):
   ```php
   // Paragraph-based chunking
   // Keeps related content together
   // Smart splitting for large paragraphs
   ```

**Token Estimation**:
```php
// Simple estimation: 1 token ≈ 4 characters
private function estimate_tokens($text) {
    return (int) ceil(strlen($text) / 4);
}
```

**Chunk Structure**:
```php
array(
    'content'  => 'The chunk text...',
    'metadata' => array(
        'post_id'           => 123,
        'chunk_index'       => 0,
        'total_chunks'      => 5,
        'source_type'       => 'post',
        'title'             => 'Post Title',
        'url'               => 'https://...',
        'section_title'     => 'Introduction',
        'heading_level'     => 1,
        'token_count'       => 150,
        'char_count'        => 600,
        'chunking_strategy' => 'hierarchical',
        'categories'        => array('Tech'),
        'tags'              => array('WordPress'),
    ),
)
```

---

### 4. SLO_Cache_Manager

**Location**: `includes/class-cache-manager.php`

**Purpose**: Manages WordPress transient-based caching for performance.

**Key Methods**:

```php
$cache = SLO_Cache_Manager::get_instance();

// Store in cache
$cache->set('key', $data, $expiration);

// Retrieve from cache
$data = $cache->get('key'); // Returns false if not found

// Delete specific cache
$cache->delete('key');

// Clear all plugin caches
$cache->clear_all();

// Get cache statistics
$stats = $cache->get_stats();
```

**Cache Keys**:
```php
// Markdown cache
slo_markdown_{post_id}

// Chunks cache
slo_chunks_{post_id}_{strategy}_{size}_{overlap}

// Rate limit tracking
slo_rate_limit_{user_id_or_ip}
```

**Cache Duration**:
- Default: 3600 seconds (1 hour)
- Configurable via settings
- Automatically cleared on post update

---

### 5. SLO_REST_API

**Location**: `includes/class-rest-api.php`

**Purpose**: Provides REST API endpoints for programmatic access.

**Namespace**: `slo/v1`

**Endpoints**:

```php
// Health check
GET /slo/v1/health

// Get markdown
GET /slo/v1/posts/{id}/markdown

// Get chunks
GET /slo/v1/posts/{id}/chunks

// Batch markdown
POST /slo/v1/batch/markdown

// Batch chunks
POST /slo/v1/batch/chunks

// Cache stats
GET /slo/v1/cache/stats

// Clear post cache
DELETE /slo/v1/cache/{post_id}

// Clear all cache
DELETE /slo/v1/cache
```

**Permission Callbacks**:
```php
// Public access (health check)
public function check_public_permission() {
    return true;
}

// Read permission (get content)
public function check_read_permission() {
    return is_user_logged_in();
}

// Admin permission (cache management)
public function check_admin_permission() {
    return current_user_can('manage_options');
}
```

**Rate Limiting**:
```php
private function check_rate_limit($user_identifier) {
    $key = 'slo_rate_limit_' . $user_identifier;
    $requests = get_transient($key);
    $limit = get_option('slo_rate_limit', 60);

    if ($requests >= $limit) {
        return new WP_Error(
            'rate_limit_exceeded',
            'Rate limit exceeded. Try again later.',
            array('status' => 429)
        );
    }

    // Increment counter
    set_transient($key, $requests + 1, HOUR_IN_SECONDS);
    return true;
}
```

---

### 6. SLO_Frontend_Button

**Location**: `includes/class-frontend-button.php`

**Purpose**: Renders frontend button and handles AJAX requests.

**Key Methods**:

```php
$button = SLO_Frontend_Button::get_instance();

// Enqueue assets (automatic via hooks)
public function enqueue_assets();

// Render button HTML (automatic via hooks)
public function render_button();

// Handle AJAX requests (automatic via hooks)
public function ajax_get_markdown();
```

**Conditions for Display**:
```php
// Only on singular posts
is_singular()

// Only on enabled post types
in_array(get_post_type(), $enabled_types)

// Only if setting enabled
get_option('slo_enable_frontend_button', true)

// Respects visibility settings
// - all: Everyone
// - logged_in: Logged in users only
// - admin: Administrators only
```

---

### 7. SLO_Admin_Settings

**Location**: `includes/class-admin-settings.php`

**Purpose**: Manages plugin settings page and admin UI.

**Settings Registration**:
```php
// Feature settings
register_setting('slo_settings', 'slo_enable_frontend_button');
register_setting('slo_settings', 'slo_enabled_post_types');
register_setting('slo_settings', 'slo_button_visibility');
register_setting('slo_settings', 'slo_enable_rest_api');

// Export options
register_setting('slo_settings', 'slo_chunk_size');
register_setting('slo_settings', 'slo_chunk_overlap');
register_setting('slo_settings', 'slo_chunking_strategy');
register_setting('slo_settings', 'slo_include_metadata');

// Advanced settings
register_setting('slo_settings', 'slo_cache_duration');
register_setting('slo_settings', 'slo_rate_limit');
register_setting('slo_settings', 'slo_enable_caching');
```

---

## WordPress Hooks

### Actions

**Plugin Initialization**:
```php
// Load textdomain for translations
add_action('init', array($this, 'load_textdomain'));

// Register REST API routes
add_action('rest_api_init', array($this, 'register_routes'));

// Add admin menu
add_action('admin_menu', array($this, 'add_settings_page'));

// Register settings
add_action('admin_init', array($this, 'register_settings'));
```

**Asset Enqueuing**:
```php
// Frontend assets
add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

// Admin assets
add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
```

**Content Display**:
```php
// Render button in footer
add_action('wp_footer', array($this, 'render_button'));

// Render modal in footer
add_action('wp_footer', array($this, 'render_modal'));
```

**AJAX Handlers**:
```php
// Get markdown (logged in)
add_action('wp_ajax_slo_get_markdown', array($this, 'ajax_get_markdown'));

// Get markdown (public)
add_action('wp_ajax_nopriv_slo_get_markdown', array($this, 'ajax_get_markdown'));

// Get chunks (logged in)
add_action('wp_ajax_slo_get_chunks', array($this, 'ajax_get_chunks'));

// Get chunks (public)
add_action('wp_ajax_nopriv_slo_get_chunks', array($this, 'ajax_get_chunks'));

// Clear cache (admin)
add_action('wp_ajax_slo_clear_cache', array($this, 'ajax_clear_cache'));
```

**Cache Clearing**:
```php
// Clear cache when post is updated
add_action('save_post', function($post_id) {
    $cache = SLO_Cache_Manager::get_instance();
    $cache->delete('slo_markdown_' . $post_id);
    // Clear all chunk caches for this post
});
```

### Filters

**Markdown Processing**:
```php
// Modify markdown before returning
apply_filters('slo_markdown_output', $markdown, $post_id);

// Example usage:
add_filter('slo_markdown_output', function($markdown, $post_id) {
    // Add custom footer
    $markdown .= "\n\n---\nGenerated by My Site";
    return $markdown;
}, 10, 2);
```

**YAML Frontmatter**:
```php
// Modify frontmatter data
apply_filters('slo_frontmatter_data', $data, $post_id);

// Example usage:
add_filter('slo_frontmatter_data', function($data, $post_id) {
    // Add custom field
    $data['custom_field'] = get_post_meta($post_id, 'my_field', true);
    return $data;
}, 10, 2);
```

**Chunk Metadata**:
```php
// Modify chunk metadata
apply_filters('slo_chunk_metadata', $metadata, $post_id, $chunk_index);

// Example usage:
add_filter('slo_chunk_metadata', function($metadata, $post_id, $chunk_index) {
    // Add custom data
    $metadata['custom_data'] = 'value';
    return $metadata;
}, 10, 3);
```

**Export Formats**:
```php
// Add custom export format
apply_filters('slo_export_formats', $formats);

// Example usage:
add_filter('slo_export_formats', function($formats) {
    $formats['custom'] = 'Custom Format';
    return $formats;
});
```

---

## API Integration

### PHP API Usage

**Get Markdown for Post**:
```php
// Get processor instance
$processor = SLO_Content_Processor::get_instance();

// Convert to markdown
$markdown = $processor->convert_to_markdown(123, array(
    'include_metadata' => true,
    'include_images'   => true,
    'preserve_links'   => true,
));

if (is_wp_error($markdown)) {
    echo 'Error: ' . $markdown->get_error_message();
} else {
    echo $markdown;
}
```

**Generate Chunks**:
```php
// Get chunking engine
$chunker = SLO_Chunking_Engine::get_instance();

// Create chunks with caching
$chunks = $chunker->get_cached_chunks(123, 'hierarchical', array(
    'chunk_size' => 512,
    'overlap'    => 128,
));

// Format for LangChain
$formatted = $chunker->format_for_langchain($chunks);

// Use the chunks
foreach ($formatted['documents'] as $doc) {
    echo "Content: " . $doc['page_content'] . "\n";
    echo "Metadata: " . print_r($doc['metadata'], true) . "\n";
}
```

**Cache Management**:
```php
// Get cache manager
$cache = SLO_Cache_Manager::get_instance();

// Clear specific post cache
$cache->delete('slo_markdown_123');

// Clear all caches
$cache->clear_all();

// Get statistics
$stats = $cache->get_stats();
echo "Cached items: " . $stats['cache_count'];
```

### REST API Integration

See [REST_API_DOCUMENTATION.md](REST_API_DOCUMENTATION.md) for complete API reference.

**Quick Example (cURL)**:
```bash
# Get markdown
curl -u "username:password" \
  "https://site.com/wp-json/slo/v1/posts/123/markdown"

# Get chunks
curl -u "username:password" \
  "https://site.com/wp-json/slo/v1/posts/123/chunks?strategy=hierarchical&format=langchain"
```

---

## Extending the Plugin

### Adding Custom Chunking Strategy

**Step 1**: Create custom strategy method:

```php
add_filter('slo_chunking_strategies', function($strategies) {
    $strategies['custom'] = 'Custom Strategy';
    return $strategies;
});

add_action('slo_chunk_content', function($markdown, $strategy, $options) {
    if ($strategy === 'custom') {
        // Your custom chunking logic
        $chunks = custom_chunk_logic($markdown, $options);
        return $chunks;
    }
    return null; // Not our strategy
}, 10, 3);

function custom_chunk_logic($markdown, $options) {
    // Implement your chunking algorithm
    $chunks = array();

    // Example: Split by double newline
    $sections = explode("\n\n", $markdown);

    foreach ($sections as $index => $section) {
        $chunks[] = array(
            'content'  => $section,
            'metadata' => array(
                'chunk_index'       => $index,
                'custom_field'      => 'custom_value',
                'token_count'       => ceil(strlen($section) / 4),
                'chunking_strategy' => 'custom',
            ),
        );
    }

    return $chunks;
}
```

**Step 2**: Use your strategy:

```php
$chunker = SLO_Chunking_Engine::get_instance();
$chunks = $chunker->create_chunks($post_id, 'custom', $options);
```

### Adding Custom Export Format

**Step 1**: Register format:

```php
add_filter('slo_export_formats', function($formats) {
    $formats['pinecone'] = 'Pinecone Vector DB';
    return $formats;
});
```

**Step 2**: Implement formatter:

```php
add_filter('slo_format_chunks', function($output, $chunks, $format, $post_id) {
    if ($format === 'pinecone') {
        $vectors = array();

        foreach ($chunks as $chunk) {
            $vectors[] = array(
                'id'       => 'post_' . $post_id . '_chunk_' . $chunk['chunk_index'],
                'values'   => array(), // Add embeddings here
                'metadata' => array(
                    'text'     => $chunk['content'],
                    'post_id'  => $post_id,
                    'chunk_id' => $chunk['chunk_index'],
                ),
            );
        }

        return array(
            'vectors'  => $vectors,
            'namespace' => 'wordpress_content',
        );
    }

    return $output;
}, 10, 4);
```

### Hooking into Processing Pipeline

**Modify Content Before Processing**:

```php
add_filter('slo_pre_process_content', function($content, $post_id) {
    // Add custom processing before markdown conversion
    $content = str_replace('[custom-shortcode]', 'Replaced text', $content);
    return $content;
}, 10, 2);
```

**Modify HTML Before Conversion**:

```php
add_filter('slo_pre_markdown_html', function($html, $post_id) {
    // Modify HTML before it's converted to markdown
    // Add custom classes, clean specific elements, etc.
    return $html;
}, 10, 2);
```

**Add Custom Metadata to Frontmatter**:

```php
add_filter('slo_frontmatter_data', function($data, $post_id) {
    // Add custom fields
    $data['word_count'] = str_word_count(get_post_field('post_content', $post_id));
    $data['reading_time'] = ceil($data['word_count'] / 200) . ' minutes';
    $data['custom_meta'] = get_post_meta($post_id, '_my_custom_field', true);

    return $data;
}, 10, 2);
```

### Creating Custom Settings Tab

```php
add_action('slo_settings_tabs', function() {
    ?>
    <div id="custom-tab" class="slo-tab-content" style="display:none;">
        <h2>Custom Settings</h2>
        <!-- Your custom settings HTML -->
    </div>
    <?php
});

add_action('slo_register_settings', function() {
    register_setting('slo_settings', 'my_custom_setting');

    add_settings_field(
        'my_custom_setting',
        'My Custom Setting',
        'my_custom_field_callback',
        'seo-llm-optimizer',
        'slo_custom_section'
    );
});
```

---

## Code Examples

### Example 1: Bulk Export All Posts

```php
/**
 * Export all published posts to markdown files
 */
function export_all_posts_to_markdown() {
    $processor = SLO_Content_Processor::get_instance();
    $export_dir = WP_CONTENT_DIR . '/exports/markdown/';

    // Create directory if it doesn't exist
    if (!file_exists($export_dir)) {
        wp_mkdir_p($export_dir);
    }

    // Get all published posts
    $posts = get_posts(array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ));

    $success = 0;
    $errors = 0;

    foreach ($posts as $post) {
        $markdown = $processor->convert_to_markdown($post->ID);

        if (!is_wp_error($markdown)) {
            $filename = $export_dir . $post->post_name . '.md';
            file_put_contents($filename, $markdown);
            $success++;
        } else {
            $errors++;
            error_log('Export failed for post ' . $post->ID . ': ' . $markdown->get_error_message());
        }
    }

    return array(
        'success' => $success,
        'errors'  => $errors,
        'total'   => count($posts),
    );
}

// Usage
$result = export_all_posts_to_markdown();
echo "Exported {$result['success']} posts, {$result['errors']} errors";
```

### Example 2: Generate Embeddings for Chunks

```php
/**
 * Generate embeddings for post chunks using OpenAI
 */
function generate_chunk_embeddings($post_id, $api_key) {
    $chunker = SLO_Chunking_Engine::get_instance();

    // Get chunks
    $chunks = $chunker->get_cached_chunks($post_id, 'hierarchical', array(
        'chunk_size' => 512,
        'overlap'    => 128,
    ));

    if (is_wp_error($chunks)) {
        return $chunks;
    }

    $embeddings = array();

    foreach ($chunks as $chunk) {
        // Call OpenAI API for embeddings
        $response = wp_remote_post('https://api.openai.com/v1/embeddings', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode(array(
                'input' => $chunk['content'],
                'model' => 'text-embedding-ada-002',
            )),
        ));

        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);

            $embeddings[] = array(
                'chunk_id'  => $chunk['chunk_index'],
                'content'   => $chunk['content'],
                'embedding' => $body['data'][0]['embedding'],
                'metadata'  => $chunk['metadata'],
            );
        }
    }

    return $embeddings;
}

// Usage
$embeddings = generate_chunk_embeddings(123, 'your-openai-api-key');
```

### Example 3: Custom WP-CLI Command

```php
/**
 * Custom WP-CLI command for bulk operations
 */
if (defined('WP_CLI') && WP_CLI) {
    class SLO_CLI_Commands {
        /**
         * Export posts to markdown
         *
         * ## OPTIONS
         *
         * [--post-type=<type>]
         * : Post type to export (default: post)
         *
         * [--output=<dir>]
         * : Output directory (default: wp-content/exports)
         *
         * ## EXAMPLES
         *
         *     wp slo export --post-type=post --output=/tmp/markdown
         */
        public function export($args, $assoc_args) {
            $post_type = isset($assoc_args['post-type']) ? $assoc_args['post-type'] : 'post';
            $output_dir = isset($assoc_args['output']) ? $assoc_args['output'] : WP_CONTENT_DIR . '/exports';

            if (!file_exists($output_dir)) {
                wp_mkdir_p($output_dir);
            }

            $processor = SLO_Content_Processor::get_instance();

            $posts = get_posts(array(
                'post_type'      => $post_type,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            ));

            $progress = \WP_CLI\Utils\make_progress_bar('Exporting posts', count($posts));

            foreach ($posts as $post) {
                $markdown = $processor->convert_to_markdown($post->ID);

                if (!is_wp_error($markdown)) {
                    $filename = $output_dir . '/' . $post->post_name . '.md';
                    file_put_contents($filename, $markdown);
                }

                $progress->tick();
            }

            $progress->finish();

            WP_CLI::success('Exported ' . count($posts) . ' posts to ' . $output_dir);
        }

        /**
         * Clear plugin caches
         *
         * ## EXAMPLES
         *
         *     wp slo clear-cache
         */
        public function clear_cache() {
            $cache = SLO_Cache_Manager::get_instance();
            $cache->clear_all();

            WP_CLI::success('All plugin caches cleared');
        }
    }

    WP_CLI::add_command('slo', 'SLO_CLI_Commands');
}
```

### Example 4: Integrate with Custom Post Type

```php
/**
 * Add LLM optimization support to custom post type
 */
add_filter('slo_enabled_post_types', function($post_types) {
    $post_types[] = 'book'; // Your custom post type
    return $post_types;
});

// Add custom metadata for books
add_filter('slo_frontmatter_data', function($data, $post_id) {
    if (get_post_type($post_id) === 'book') {
        $data['isbn'] = get_post_meta($post_id, '_book_isbn', true);
        $data['author'] = get_post_meta($post_id, '_book_author', true);
        $data['publisher'] = get_post_meta($post_id, '_book_publisher', true);
        $data['year'] = get_post_meta($post_id, '_book_year', true);
    }
    return $data;
}, 10, 2);
```

---

## Testing

### Manual Testing Checklist

**Frontend Button**:
- [ ] Button appears on single posts
- [ ] Button appears on single pages
- [ ] Button doesn't appear on archives
- [ ] Modal opens when button clicked
- [ ] All three tabs work
- [ ] Copy buttons work
- [ ] Modal closes with Escape key
- [ ] Mobile responsive

**Settings Page**:
- [ ] All settings save correctly
- [ ] Cache clear button works
- [ ] Settings validate correctly
- [ ] Help text displays
- [ ] Tabs switch correctly

**REST API**:
- [ ] Health endpoint returns 200
- [ ] Markdown endpoint works with auth
- [ ] Chunks endpoint works with auth
- [ ] Batch endpoints work
- [ ] Rate limiting works
- [ ] Authentication required
- [ ] Error responses correct

**Content Processing**:
- [ ] Gutenberg content converts correctly
- [ ] Classic editor content converts correctly
- [ ] Headers preserved in markdown
- [ ] Links converted correctly
- [ ] Images preserved (when enabled)
- [ ] Shortcodes processed
- [ ] Theme elements removed

**Chunking**:
- [ ] Hierarchical strategy works
- [ ] Fixed size strategy works
- [ ] Semantic strategy works
- [ ] Chunk sizes respected
- [ ] Overlap works (fixed strategy)
- [ ] Metadata included correctly

**Caching**:
- [ ] Caching improves performance
- [ ] Cache clears on post update
- [ ] Manual cache clear works
- [ ] Cache duration respected

### Unit Testing (PHPUnit)

**Setup**:
```bash
composer require --dev phpunit/phpunit
composer require --dev yoast/phpunit-polyfills
```

**Example Test**:
```php
<?php
/**
 * Test content processor
 */
class Test_Content_Processor extends WP_UnitTestCase {

    private $processor;

    public function setUp(): void {
        parent::setUp();
        $this->processor = SLO_Content_Processor::get_instance();
    }

    public function test_convert_to_markdown() {
        // Create test post
        $post_id = $this->factory->post->create(array(
            'post_title'   => 'Test Post',
            'post_content' => '<h1>Header</h1><p>Content</p>',
            'post_status'  => 'publish',
        ));

        // Convert to markdown
        $markdown = $this->processor->convert_to_markdown($post_id);

        // Assert not error
        $this->assertNotInstanceOf('WP_Error', $markdown);

        // Assert contains header
        $this->assertStringContainsString('# Header', $markdown);

        // Assert contains content
        $this->assertStringContainsString('Content', $markdown);
    }

    public function test_invalid_post_id() {
        $markdown = $this->processor->convert_to_markdown(999999);

        $this->assertInstanceOf('WP_Error', $markdown);
        $this->assertEquals('invalid_post', $markdown->get_error_code());
    }
}
```

**Run Tests**:
```bash
vendor/bin/phpunit
```

### Integration Testing

**Test REST API**:
```bash
# Set up test environment
export TEST_SITE="https://test.example.com"
export TEST_USER="admin"
export TEST_PASS="app-password"

# Test health endpoint
curl $TEST_SITE/wp-json/slo/v1/health

# Test markdown endpoint
curl -u $TEST_USER:$TEST_PASS \
  "$TEST_SITE/wp-json/slo/v1/posts/1/markdown"

# Test chunks endpoint
curl -u $TEST_USER:$TEST_PASS \
  "$TEST_SITE/wp-json/slo/v1/posts/1/chunks?strategy=hierarchical"

# Test batch endpoint
curl -X POST \
  -u $TEST_USER:$TEST_PASS \
  -H "Content-Type: application/json" \
  -d '{"post_ids": [1, 2, 3]}' \
  "$TEST_SITE/wp-json/slo/v1/batch/markdown"
```

### Performance Testing

```php
/**
 * Benchmark content processing
 */
function benchmark_processing($post_id, $iterations = 10) {
    $processor = SLO_Content_Processor::get_instance();
    $cache = SLO_Cache_Manager::get_instance();

    // Clear cache
    $cache->clear_all();

    // Benchmark without cache
    $start = microtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $cache->clear_all();
        $processor->convert_to_markdown($post_id);
    }
    $no_cache_time = (microtime(true) - $start) / $iterations;

    // Benchmark with cache
    $processor->convert_to_markdown($post_id); // Warm cache
    $start = microtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $processor->convert_to_markdown($post_id);
    }
    $cached_time = (microtime(true) - $start) / $iterations;

    return array(
        'no_cache'     => $no_cache_time,
        'cached'       => $cached_time,
        'improvement'  => $no_cache_time / $cached_time,
    );
}

// Usage
$result = benchmark_processing(123);
echo "No cache: {$result['no_cache']}s\n";
echo "Cached: {$result['cached']}s\n";
echo "Improvement: {$result['improvement']}x faster\n";
```

---

## Additional Resources

- **WordPress Coding Standards**: https://developer.wordpress.org/coding-standards/
- **WordPress Plugin Handbook**: https://developer.wordpress.org/plugins/
- **REST API Handbook**: https://developer.wordpress.org/rest-api/
- **League HTML-to-Markdown**: https://github.com/thephpleague/html-to-markdown

---

**Last Updated**: 2025-11-07
**Plugin Version**: 1.0.0
**Maintainer**: Mikkel Krogsholm
