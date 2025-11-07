# RAG Chunking Engine - Quick Start Guide

## Installation

The chunking engine is automatically loaded when the SEO & LLM Optimizer plugin is activated.

```php
// Already initialized in main plugin file
SLO_Chunking_Engine::get_instance();
```

---

## Basic Usage

### 1. Simple Chunking

```php
// Get instance
$engine = SLO_Chunking_Engine::get_instance();

// Create chunks from post ID
$chunks = $engine->create_chunks(123, 'hierarchical');

if (is_wp_error($chunks)) {
    echo 'Error: ' . $chunks->get_error_message();
} else {
    echo 'Created ' . count($chunks) . ' chunks';
}
```

### 2. With Custom Options

```php
$chunks = $engine->create_chunks(123, 'fixed', array(
    'chunk_size' => 1024,
    'overlap'    => 256,
));
```

### 3. Using Cache

```php
// Automatically caches and retrieves
$chunks = $engine->get_cached_chunks(123, 'semantic');
```

---

## Chunking Strategies

### Hierarchical (By Headers)
```php
$chunks = $engine->create_chunks($post_id, 'hierarchical');
```
- Splits by markdown headers (# to ######)
- Best for structured content
- Preserves document hierarchy

### Fixed-Size (Consistent)
```php
$chunks = $engine->create_chunks($post_id, 'fixed', array(
    'chunk_size' => 512,  // tokens
    'overlap'    => 128,  // tokens
));
```
- Creates uniform-sized chunks
- Includes sentence-boundary overlap
- Best for vector databases

### Semantic (By Paragraphs)
```php
$chunks = $engine->create_chunks($post_id, 'semantic', array(
    'chunk_size' => 512,
));
```
- Chunks by paragraph boundaries
- Keeps related content together
- Best for natural language processing

---

## Export Formats

### Universal Format (Default)
```php
$chunks = $engine->create_chunks($post_id, 'hierarchical');
$output = $engine->format_universal($chunks, $post_id);

// Save to file
file_put_contents('chunks.json', json_encode($output, JSON_PRETTY_PRINT));
```

### LangChain Format
```php
$langchain = $engine->format_for_langchain($chunks);

// Use with Python LangChain
// from langchain.docstore.document import Document
// documents = [Document(page_content=d['page_content'], metadata=d['metadata']) for d in data['documents']]
```

### LlamaIndex Format
```php
$llamaindex = $engine->format_for_llamaindex($chunks);

// Use with Python LlamaIndex
// from llama_index import Document
// documents = [Document(text=d['text'], doc_id=d['id_'], metadata=d['metadata']) for d in data['documents']]
```

---

## AJAX Usage

### JavaScript Example

```javascript
jQuery.ajax({
    url: sloData.ajaxUrl,
    type: 'POST',
    data: {
        action: 'slo_get_chunks',
        nonce: sloData.nonce,
        post_id: 123,
        strategy: 'hierarchical',  // or 'fixed', 'semantic'
        format: 'universal',        // or 'langchain', 'llamaindex'
        chunk_size: 512,
        overlap: 128
    },
    success: function(response) {
        if (response.success) {
            const chunks = response.data.chunks;
            console.log(`Created ${chunks.length} chunks`);

            chunks.forEach(chunk => {
                console.log(`Chunk ${chunk.chunk_index}:`, chunk.metadata.token_count, 'tokens');
            });
        } else {
            console.error('Error:', response.data.message);
        }
    }
});
```

### Fetch API Example

```javascript
const response = await fetch(sloData.ajaxUrl, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
        action: 'slo_get_chunks',
        nonce: sloData.nonce,
        post_id: 123,
        strategy: 'fixed',
        format: 'langchain',
        chunk_size: 512,
        overlap: 128
    })
});

const data = await response.json();
if (data.success) {
    console.log('LangChain documents:', data.data.documents);
}
```

---

## Common Patterns

### Pattern 1: Export for Training Data

```php
function export_all_posts_for_training() {
    $engine = SLO_Chunking_Engine::get_instance();

    $posts = get_posts(array(
        'post_type'   => 'post',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));

    $all_documents = array();

    foreach ($posts as $post) {
        $chunks = $engine->get_cached_chunks($post->ID, 'semantic', array(
            'chunk_size' => 512,
            'overlap'    => 128,
        ));

        if (!is_wp_error($chunks)) {
            $formatted = $engine->format_for_langchain($chunks);
            $all_documents = array_merge($all_documents, $formatted['documents']);
        }
    }

    file_put_contents(
        'training_data.json',
        json_encode(array('documents' => $all_documents), JSON_PRETTY_PRINT)
    );

    return count($all_documents);
}
```

### Pattern 2: Compare Strategies

```php
function compare_chunking_strategies($post_id) {
    $engine = SLO_Chunking_Engine::get_instance();
    $stats = $engine->get_chunking_stats($post_id);

    echo "Chunking Strategy Comparison for Post $post_id:\n\n";

    foreach ($stats as $strategy => $data) {
        echo ucfirst($strategy) . ":\n";
        echo "  Total chunks: " . $data['total_chunks'] . "\n";
        echo "  Avg tokens: " . round($data['avg_tokens']) . "\n";
        echo "  Min tokens: " . $data['min_tokens'] . "\n";
        echo "  Max tokens: " . $data['max_tokens'] . "\n\n";
    }
}
```

### Pattern 3: Batch Processing

```php
function batch_process_posts($post_ids) {
    $engine = SLO_Chunking_Engine::get_instance();

    $results = array();

    foreach ($post_ids as $post_id) {
        $chunks = $engine->get_cached_chunks($post_id, 'hierarchical');

        if (!is_wp_error($chunks)) {
            $results[$post_id] = array(
                'success'      => true,
                'chunk_count'  => count($chunks),
                'total_tokens' => array_sum(array_column(
                    array_column($chunks, 'metadata'),
                    'token_count'
                )),
            );
        } else {
            $results[$post_id] = array(
                'success' => false,
                'error'   => $chunks->get_error_message(),
            );
        }
    }

    return $results;
}
```

### Pattern 4: Preview Before Export

```php
function preview_chunks($post_id, $strategy = 'hierarchical') {
    $engine = SLO_Chunking_Engine::get_instance();
    $chunks = $engine->get_cached_chunks($post_id, $strategy);

    if (is_wp_error($chunks)) {
        return $chunks;
    }

    $preview = array();

    foreach ($chunks as $chunk) {
        $preview[] = array(
            'index'        => $chunk['chunk_index'],
            'tokens'       => $chunk['metadata']['token_count'],
            'chars'        => $chunk['metadata']['char_count'],
            'preview'      => substr($chunk['content'], 0, 200) . '...',
            'section'      => isset($chunk['metadata']['section_title'])
                ? $chunk['metadata']['section_title']
                : 'N/A',
        );
    }

    return $preview;
}
```

---

## Settings Integration

### Get Plugin Settings

```php
// Current defaults from admin settings
$chunk_size = (int) get_option('slo_chunk_size', 512);
$overlap = (int) get_option('slo_chunk_overlap', 128);
$strategy = get_option('slo_chunking_strategy', 'hierarchical');

// Use in chunking
$chunks = $engine->create_chunks($post_id, $strategy, array(
    'chunk_size' => $chunk_size,
    'overlap'    => $overlap,
));
```

### Update Settings Programmatically

```php
// Update chunking settings
update_option('slo_chunking_strategy', 'semantic');
update_option('slo_chunk_size', 1024);
update_option('slo_chunk_overlap', 256);
```

---

## Cache Management

### Manual Cache Clearing

```php
$cache_manager = SLO_Cache_Manager::get_instance();

// Clear all chunks for a post
$cache_manager->invalidate_post($post_id);

// Clear specific cache
$cache_key = sprintf('chunks_%d_hierarchical_512_128', $post_id);
$cache_manager->delete($cache_key);
```

### Force Refresh

```php
// Bypass cache and regenerate
$cache_manager->invalidate_post($post_id);
$chunks = $engine->create_chunks($post_id, 'hierarchical');
```

---

## Error Handling

### Check for Errors

```php
$chunks = $engine->create_chunks($post_id, 'hierarchical');

if (is_wp_error($chunks)) {
    $error_code = $chunks->get_error_code();
    $error_message = $chunks->get_error_message();

    switch ($error_code) {
        case 'invalid_post_id':
            // Handle invalid post ID
            break;
        case 'invalid_post':
            // Handle post not found or not published
            break;
        case 'invalid_strategy':
            // Handle invalid strategy
            break;
        default:
            // Handle other errors
            break;
    }
}
```

### Error Codes

- `invalid_post_id` - Post ID is not a valid integer
- `invalid_post` - Post not found or not published
- `invalid_strategy` - Invalid chunking strategy specified
- `processing_error` - Error during content processing

---

## Performance Tips

### 1. Use Caching
```php
// ✓ Good - uses cache
$chunks = $engine->get_cached_chunks($post_id, 'hierarchical');

// ✗ Avoid - always regenerates
$chunks = $engine->create_chunks($post_id, 'hierarchical');
```

### 2. Choose Appropriate Strategy
```php
// Fast (less processing)
$chunks = $engine->create_chunks($post_id, 'hierarchical');

// Medium (sentence splitting)
$chunks = $engine->create_chunks($post_id, 'semantic');

// Slower (sentence splitting + overlap)
$chunks = $engine->create_chunks($post_id, 'fixed');
```

### 3. Optimize Chunk Size
```php
// More chunks, faster processing per chunk
$chunks = $engine->create_chunks($post_id, 'fixed', array(
    'chunk_size' => 256,
));

// Fewer chunks, slower processing
$chunks = $engine->create_chunks($post_id, 'fixed', array(
    'chunk_size' => 2048,
));
```

---

## WordPress Hooks

### Extend Chunking Engine

```php
// Add custom metadata to chunks
add_filter('slo_chunk_metadata', function($metadata, $post_id, $chunk_index) {
    $metadata['custom_field'] = get_post_meta($post_id, 'my_custom_field', true);
    return $metadata;
}, 10, 3);
```

### Monitor Chunking

```php
// Log chunking operations
add_action('slo_chunks_created', function($post_id, $strategy, $chunk_count) {
    error_log("Created $chunk_count chunks for post $post_id using $strategy strategy");
}, 10, 3);
```

---

## Troubleshooting

### Issue: Empty Chunks Array

**Problem:** `create_chunks()` returns empty array

**Solutions:**
1. Check post content exists and is published
2. Verify markdown conversion succeeded
3. Check for extremely short content

```php
$chunks = $engine->create_chunks($post_id, 'hierarchical');

if (empty($chunks) && !is_wp_error($chunks)) {
    $processor = SLO_Content_Processor::get_instance();
    $markdown = $processor->convert_to_markdown($post_id);

    if (is_wp_error($markdown)) {
        echo 'Markdown conversion failed: ' . $markdown->get_error_message();
    } else if (empty($markdown)) {
        echo 'Post has no content';
    }
}
```

### Issue: Chunks Too Large/Small

**Problem:** Chunk sizes not matching expectations

**Solutions:**
1. Adjust chunk_size parameter
2. Try different strategy
3. Check token estimation

```php
// Get statistics first
$stats = $engine->get_chunking_stats($post_id);

// Adjust based on results
$target_size = 512;
if ($stats['fixed']['avg_tokens'] > $target_size * 1.5) {
    $target_size = 384; // Smaller chunks
}

$chunks = $engine->create_chunks($post_id, 'fixed', array(
    'chunk_size' => $target_size,
));
```

### Issue: Cache Not Working

**Problem:** Chunks regenerate on every call

**Solutions:**
1. Check cache duration setting
2. Verify object cache is enabled
3. Check for cache clearing on post update

```php
// Check cache status
$cache_manager = SLO_Cache_Manager::get_instance();
$cache_key = sprintf('chunks_%d_hierarchical_512_128', $post_id);
$cached = $cache_manager->get($cache_key);

if (false === $cached) {
    echo 'Cache miss';
} else {
    echo 'Cache hit';
}
```

---

## Configuration Reference

### Default Settings

```php
slo_chunking_strategy = 'hierarchical'
slo_chunk_size = 512
slo_chunk_overlap = 128
slo_cache_duration = 3600  // seconds
```

### Valid Ranges

```php
chunk_size: 128 to 2048 tokens
overlap: 0 to 512 tokens
```

### Valid Strategies

```php
'hierarchical' - Split by headers
'fixed'        - Fixed-size with overlap
'semantic'     - Paragraph-based
```

### Valid Formats

```php
'universal'   - Framework-agnostic JSON
'langchain'   - LangChain Document format
'llamaindex'  - LlamaIndex Document format
```

---

## Next Steps

1. **Test Basic Chunking**
   ```php
   $chunks = SLO_Chunking_Engine::get_instance()->create_chunks(123, 'hierarchical');
   ```

2. **Review Chunk Output**
   ```php
   var_dump($chunks[0]);  // Examine structure
   ```

3. **Try Different Strategies**
   ```php
   $stats = $engine->get_chunking_stats(123);
   print_r($stats);
   ```

4. **Export for Your RAG System**
   ```php
   $output = $engine->format_for_langchain($chunks);
   file_put_contents('export.json', json_encode($output));
   ```

5. **Integrate with Frontend**
   - Add AJAX call to modal
   - Display chunk preview
   - Export button for each format

---

## Support

For issues or questions:
1. Check the main documentation: `CHUNKING_ENGINE_IMPLEMENTATION.md`
2. Review code comments in `class-chunking-engine.php`
3. Test with different post types and content structures
4. Enable WordPress debug mode: `define('WP_DEBUG', true);`

---

## Quick Reference Card

```php
// Instance
$engine = SLO_Chunking_Engine::get_instance();

// Create chunks
$chunks = $engine->create_chunks($post_id, $strategy, $options);

// With cache
$chunks = $engine->get_cached_chunks($post_id, $strategy, $options);

// Format
$universal = $engine->format_universal($chunks, $post_id);
$langchain = $engine->format_for_langchain($chunks);
$llamaindex = $engine->format_for_llamaindex($chunks);

// Statistics
$stats = $engine->get_chunking_stats($post_id);

// Strategies: 'hierarchical', 'fixed', 'semantic'
// Formats: 'universal', 'langchain', 'llamaindex'
// Options: chunk_size (128-2048), overlap (0-512)
```
