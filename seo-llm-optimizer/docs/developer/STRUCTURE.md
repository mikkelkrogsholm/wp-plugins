# SEO & LLM Optimizer - Plugin Structure

## Overview

Complete foundation structure for the SEO & LLM Optimizer WordPress plugin, following WordPress coding standards and patterns from the seo-cluster-links plugin.

## File Structure

```
seo-llm-optimizer/
├── seo-llm-optimizer.php          # Main plugin file with header and initialization
├── uninstall.php                   # Cleanup script for plugin uninstallation
├── composer.json                   # Composer dependencies configuration
├── phpcs.xml                       # WordPress coding standards configuration
├── .gitignore                      # Git ignore patterns
├── README.md                       # GitHub README
├── readme.txt                      # WordPress.org README
├── INSTALLATION.md                 # Detailed installation guide
├── includes/
│   ├── class-content-processor.php # Main content processing orchestrator
│   ├── class-content-cleaner.php  # HTML cleaning and markdown conversion
│   ├── class-cache-manager.php    # Cache management system
│   ├── class-chunking-engine.php  # Content chunking for LLM processing
│   ├── class-frontend-button.php  # Frontend button rendering
│   ├── class-modal-handler.php    # Modal window management
│   ├── class-admin-settings.php   # Admin settings page
│   └── class-rest-api.php         # REST API endpoints
├── admin/
│   └── class-meta-boxes.php       # Post editor meta boxes
├── assets/
│   ├── css/
│   │   ├── admin.css              # Admin area styles
│   │   └── frontend.css           # Frontend styles (button & modal)
│   ├── js/
│   │   ├── admin.js               # Admin JavaScript
│   │   └── frontend.js            # Frontend JavaScript (AJAX, modal)
│   └── images/                    # Plugin images (empty, ready for use)
├── templates/
│   ├── admin/
│   │   └── settings-page.php      # Settings page template
│   └── frontend/
│       └── modal.php               # Modal template
└── languages/
    └── seo-llm-optimizer.pot       # Translation template
```

## Plugin Constants

Defined in main plugin file:

- `SLO_VERSION` - Plugin version (1.0.0)
- `SLO_PLUGIN_DIR` - Absolute path to plugin directory
- `SLO_PLUGIN_URL` - URL to plugin directory
- `SLO_PLUGIN_BASENAME` - Plugin basename for hooks

## Plugin Options

Default options set on activation:

- `slo_version` - Plugin version
- `slo_chunk_size` - Default chunk size (1000 characters)
- `slo_cache_duration` - Cache duration (3600 seconds)
- `slo_enable_frontend_button` - Show frontend button (true)

## Post Meta Keys

- `_slo_optimize` - Enable optimization for post (boolean)
- `_slo_chunk_size` - Custom chunk size for post (integer)

## Class Architecture

All classes follow singleton pattern:

### Core Processing Classes (includes/)

1. **SLO_Content_Processor**
   - Main orchestrator for content processing
   - Coordinates cleaning, conversion, and chunking

2. **SLO_Content_Cleaner**
   - Cleans HTML content
   - Converts HTML to Markdown using league/html-to-markdown
   - Removes unnecessary elements

3. **SLO_Cache_Manager**
   - Manages WordPress object cache
   - Cache group: 'seo_llm_optimizer'
   - Methods: get(), set(), delete(), invalidate_post()

4. **SLO_Chunking_Engine**
   - Splits content into semantic chunks
   - Configurable chunk size
   - Intelligent splitting algorithm

### Frontend Classes (includes/)

5. **SLO_Frontend_Button**
   - Adds optimization button to post content
   - Enqueues frontend assets
   - Hooks: the_content, wp_enqueue_scripts

6. **SLO_Modal_Handler**
   - Renders modal HTML in footer
   - Displays optimized content
   - Template: templates/frontend/modal.php

### Admin Classes

7. **SLO_Admin_Settings** (includes/)
   - Settings page registration
   - Settings API integration
   - Menu: Settings > LLM Optimizer

8. **SLO_Meta_Boxes** (admin/)
   - Post editor meta boxes
   - Per-post optimization settings
   - Cache invalidation on save

### API Classes

9. **SLO_REST_API** (includes/)
   - REST API endpoints
   - Namespace: slo/v1
   - Endpoint: /process/{id}

## Hooks & Actions

### Actions Used

- `init` - Load textdomain
- `admin_menu` - Add settings page
- `admin_init` - Register settings
- `add_meta_boxes` - Add meta boxes
- `save_post` - Save meta box data
- `admin_enqueue_scripts` - Enqueue admin assets
- `wp_enqueue_scripts` - Enqueue frontend assets
- `wp_footer` - Render modal
- `rest_api_init` - Register REST routes

### Filters Used

- `the_content` - Add button to content

## Security Features

All classes include:

- ABSPATH security check
- Nonce verification for forms
- Capability checks (manage_options, edit_post)
- Input sanitization (sanitize_text_field, absint)
- Output escaping (esc_html, esc_attr, esc_url)
- Prepared statements for database queries

## Composer Dependencies

- `league/html-to-markdown` ^5.1 - HTML to Markdown conversion
- PHP requirement: >=7.4

## WordPress Requirements

- WordPress: 6.4+
- PHP: 7.4+
- Tested up to: WordPress 6.8

## Asset Loading Strategy

Following WordPress 6.8+ standards:

- Scripts loaded in footer with 'defer' strategy
- Conditional loading (only on relevant pages)
- Early return for performance
- Localized script data for AJAX

## Cache Strategy

- Uses WordPress object cache API
- Cache group: 'seo_llm_optimizer'
- Keys pattern:
  - `processed_{post_id}` - Processed content
  - `chunks_{post_id}` - Content chunks
  - `markdown_{post_id}` - Markdown version
- Automatic invalidation on post save

## Translation Support

- Text domain: 'seo-llm-optimizer'
- Domain path: /languages
- POT file included
- All strings properly wrapped with translation functions

## Ready for Development

The plugin structure is complete and ready for:

1. Implementation of content processing logic
2. Integration with league/html-to-markdown
3. Advanced chunking algorithms
4. Custom caching strategies
5. Extended REST API endpoints
6. Additional meta box fields
7. Frontend UI enhancements

## Activation Checklist

- [x] Main plugin file with proper header
- [x] Security checks (ABSPATH, nonces, capabilities)
- [x] Singleton pattern for all classes
- [x] WordPress coding standards compliance
- [x] Proper hook initialization
- [x] Asset enqueuing with modern strategies
- [x] Translation support
- [x] Activation/deactivation hooks
- [x] Uninstall script
- [x] Documentation (README, INSTALLATION)

## Next Steps

1. Run `composer install` to install dependencies
2. Activate plugin in WordPress
3. Configure settings under Settings > LLM Optimizer
4. Test with a sample post
5. Implement core processing logic in the class stubs

## Plugin Can Be Activated

Yes! The plugin structure is complete and can be activated in WordPress without errors. All required files are present, all classes follow proper WordPress patterns, and the initialization sequence is correct.

**Note:** You'll need to run `composer install` before activation to install the league/html-to-markdown dependency. However, the plugin includes checks to prevent fatal errors if the vendor directory is missing.
