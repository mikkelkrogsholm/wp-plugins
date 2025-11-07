<?php
/**
 * Chunking Engine
 *
 * Splits content into semantic chunks for LLM processing with multiple strategies.
 * Supports hierarchical, fixed-size, and semantic chunking with various output formats.
 *
 * @package SEO_LLM_Optimizer
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles content chunking operations for RAG systems
 *
 * Provides multiple chunking strategies:
 * - Hierarchical: Splits by markdown headers (# to ######)
 * - Fixed: Fixed-size chunks with sentence-boundary overlap
 * - Semantic: Paragraph-based semantic chunking
 *
 * Supports multiple export formats:
 * - Universal: Standard format for any system
 * - LangChain: Python LangChain document format
 * - LlamaIndex: LlamaIndex document format
 *
 * @since 1.0.0
 */
class SLO_Chunking_Engine {

    /**
     * Singleton instance
     *
     * @var SLO_Chunking_Engine
     */
    private static $instance = null;

    /**
     * Cache manager instance
     *
     * @var SLO_Cache_Manager
     */
    private $cache_manager;

    /**
     * Get singleton instance
     *
     * @return SLO_Chunking_Engine
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // AJAX handlers for frontend and admin
        add_action('wp_ajax_slo_get_chunks', array($this, 'ajax_get_chunks'));
        add_action('wp_ajax_nopriv_slo_get_chunks', array($this, 'ajax_get_chunks'));
    }

    /**
     * Get cache manager instance (lazy loading)
     *
     * @return SLO_Cache_Manager
     */
    private function get_cache_manager() {
        if (null === $this->cache_manager) {
            $this->cache_manager = SLO_Cache_Manager::get_instance();
        }
        return $this->cache_manager;
    }

    /**
     * Create chunks from post content
     *
     * Main entry point for chunking operations. Converts post to markdown
     * and splits into chunks using the specified strategy.
     *
     * @param int    $post_id  Post ID to chunk
     * @param string $strategy Chunking strategy: 'hierarchical', 'fixed', 'semantic'
     * @param array  $options  Chunking options
     *                         - chunk_size: Target chunk size in tokens (default: 512)
     *                         - overlap: Overlap size in tokens (default: 128)
     *                         - format: Output format (default: 'universal')
     * @return array|WP_Error Array of chunks or error
     */
    public function create_chunks($post_id, $strategy = 'hierarchical', $options = array()) {
        // Sanitize and validate post ID
        $post_id = absint($post_id);
        if (!$post_id) {
            return new WP_Error('invalid_post_id', __('Invalid post ID', 'seo-llm-optimizer'));
        }

        // Check if post exists and is published
        $post = get_post($post_id);
        if (!$post || 'publish' !== $post->post_status) {
            return new WP_Error('invalid_post', __('Post not found or not published', 'seo-llm-optimizer'));
        }

        // Parse options with defaults
        $defaults = array(
            'chunk_size' => (int) get_option('slo_chunk_size', 512),
            'overlap'    => (int) get_option('slo_chunk_overlap', 128),
            'format'     => 'universal',
        );
        $options = wp_parse_args($options, $defaults);

        // Validate strategy
        $valid_strategies = array('hierarchical', 'fixed', 'semantic');
        if (!in_array($strategy, $valid_strategies, true)) {
            return new WP_Error('invalid_strategy', __('Invalid chunking strategy', 'seo-llm-optimizer'));
        }

        // Get markdown from content processor
        $processor = SLO_Content_Processor::get_instance();
        $markdown = $processor->convert_to_markdown($post_id, array(
            'include_metadata' => false,  // Don't include frontmatter in chunks
            'include_images'   => true,
            'preserve_links'   => true,
        ));

        if (is_wp_error($markdown)) {
            return $markdown;
        }

        // Get base metadata for chunks
        $base_metadata = $this->create_chunk_metadata($post_id, 0, 0);

        // Route to appropriate chunking strategy
        switch ($strategy) {
            case 'hierarchical':
                $chunks = $this->chunk_by_headers($markdown, $base_metadata);
                break;

            case 'fixed':
                $chunks = $this->chunk_fixed_size(
                    $markdown,
                    $options['chunk_size'],
                    $options['overlap'],
                    $base_metadata
                );
                break;

            case 'semantic':
                $chunks = $this->chunk_semantically(
                    $markdown,
                    $options['chunk_size'],
                    $options['overlap'],
                    $base_metadata
                );
                break;

            default:
                return new WP_Error('invalid_strategy', __('Invalid chunking strategy', 'seo-llm-optimizer'));
        }

        // Update chunk indices and total count
        $total = count($chunks);
        foreach ($chunks as $index => &$chunk) {
            $chunk['chunk_index'] = $index;
            $chunk['total_chunks'] = $total;
            $chunk['metadata']['chunk_index'] = $index;
            $chunk['metadata']['total_chunks'] = $total;
        }

        return $chunks;
    }

    /**
     * Get cached chunks or create new ones
     *
     * Checks cache before creating chunks. Caches results for performance.
     *
     * @param int    $post_id  Post ID
     * @param string $strategy Chunking strategy
     * @param array  $options  Chunking options
     * @return array|WP_Error Array of chunks or error
     */
    public function get_cached_chunks($post_id, $strategy, $options = array()) {
        // Create cache key based on post ID, strategy, and options
        $cache_key = sprintf(
            'chunks_%d_%s_%d_%d',
            $post_id,
            $strategy,
            isset($options['chunk_size']) ? $options['chunk_size'] : 512,
            isset($options['overlap']) ? $options['overlap'] : 128
        );

        // Try to get from cache
        $cache_manager = $this->get_cache_manager();
        $cached = $cache_manager->get($cache_key);

        if (false !== $cached) {
            return $cached;
        }

        // Create new chunks
        $chunks = $this->create_chunks($post_id, $strategy, $options);

        // Cache if successful
        if (!is_wp_error($chunks)) {
            $cache_manager->set($cache_key, $chunks);
        }

        return $chunks;
    }

    /**
     * Chunk content by markdown headers (hierarchical strategy)
     *
     * Splits markdown content by headers (# through ######), preserving
     * semantic structure. Each section becomes a separate chunk.
     *
     * @param string $markdown Markdown content
     * @param array  $metadata Base metadata for chunks
     * @return array Array of chunks
     */
    private function chunk_by_headers($markdown, $metadata) {
        $chunks = array();

        // Split by lines for processing
        $lines = explode("\n", $markdown);
        $current_section = '';
        $current_header = '';
        $header_level = 0;

        foreach ($lines as $line) {
            // Check if line is a markdown header
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                // Save previous section if it has content
                if (!empty(trim($current_section))) {
                    $chunks[] = array(
                        'content'  => trim($current_section),
                        'metadata' => array_merge($metadata, array(
                            'section_title'    => $current_header,
                            'heading_level'    => $header_level,
                            'token_count'      => $this->estimate_tokens($current_section),
                            'char_count'       => strlen($current_section),
                            'chunking_strategy' => 'hierarchical',
                        )),
                    );
                }

                // Start new section
                $header_level = strlen($matches[1]);
                $current_header = $matches[2];
                $current_section = $line . "\n";
            } else {
                $current_section .= $line . "\n";
            }
        }

        // Add final section
        if (!empty(trim($current_section))) {
            $chunks[] = array(
                'content'  => trim($current_section),
                'metadata' => array_merge($metadata, array(
                    'section_title'    => $current_header,
                    'heading_level'    => $header_level,
                    'token_count'      => $this->estimate_tokens($current_section),
                    'char_count'       => strlen($current_section),
                    'chunking_strategy' => 'hierarchical',
                )),
            );
        }

        return $chunks;
    }

    /**
     * Chunk content with fixed size and overlap
     *
     * Creates fixed-size chunks with sentence-boundary overlap. Ensures
     * sentences are not split across chunks for readability.
     *
     * @param string $markdown   Markdown content
     * @param int    $chunk_size Target chunk size in tokens
     * @param int    $overlap    Overlap size in tokens
     * @param array  $metadata   Base metadata for chunks
     * @return array Array of chunks
     */
    private function chunk_fixed_size($markdown, $chunk_size, $overlap, $metadata) {
        $chunks = array();

        // Split into sentences
        $sentences = $this->split_into_sentences($markdown);

        $current_chunk = '';
        $current_tokens = 0;
        $sentence_buffer = array();

        foreach ($sentences as $sentence) {
            $sentence_tokens = $this->estimate_tokens($sentence);

            // Check if adding this sentence exceeds chunk size
            if ($current_tokens + $sentence_tokens > $chunk_size && !empty($current_chunk)) {
                // Save current chunk
                $chunks[] = array(
                    'content'  => trim($current_chunk),
                    'metadata' => array_merge($metadata, array(
                        'token_count'       => $current_tokens,
                        'char_count'        => strlen($current_chunk),
                        'chunking_strategy' => 'fixed_size',
                        'overlap_tokens'    => $overlap,
                        'target_size'       => $chunk_size,
                    )),
                );

                // Start new chunk with overlap
                $overlap_text = $this->get_overlap_text($sentence_buffer, $overlap);
                $current_chunk = $overlap_text;
                $current_tokens = $this->estimate_tokens($overlap_text);
            }

            // Add sentence to current chunk
            $current_chunk .= $sentence . ' ';
            $current_tokens += $sentence_tokens;

            // Maintain sentence buffer for overlap
            $sentence_buffer[] = $sentence;
            if (count($sentence_buffer) > 20) {  // Keep last 20 sentences max
                array_shift($sentence_buffer);
            }
        }

        // Add final chunk
        if (!empty(trim($current_chunk))) {
            $chunks[] = array(
                'content'  => trim($current_chunk),
                'metadata' => array_merge($metadata, array(
                    'token_count'       => $current_tokens,
                    'char_count'        => strlen($current_chunk),
                    'chunking_strategy' => 'fixed_size',
                    'overlap_tokens'    => $overlap,
                    'target_size'       => $chunk_size,
                )),
            );
        }

        return $chunks;
    }

    /**
     * Chunk content semantically by paragraphs
     *
     * Creates chunks based on paragraph boundaries. Attempts to keep
     * related content together while respecting size constraints.
     *
     * @param string $markdown   Markdown content
     * @param int    $chunk_size Target chunk size in tokens
     * @param int    $overlap    Overlap size in tokens (not used in semantic chunking)
     * @param array  $metadata   Base metadata for chunks
     * @return array Array of chunks
     */
    private function chunk_semantically($markdown, $chunk_size, $overlap, $metadata) {
        $chunks = array();

        // Split by paragraphs (double newline or more)
        $paragraphs = preg_split('/\n\n+/', $markdown, -1, PREG_SPLIT_NO_EMPTY);

        $current_chunk = '';
        $current_tokens = 0;

        foreach ($paragraphs as $paragraph) {
            $para_tokens = $this->estimate_tokens($paragraph);

            // If single paragraph exceeds chunk size, split it by sentences
            if ($para_tokens > $chunk_size) {
                // Save current chunk first if not empty
                if (!empty(trim($current_chunk))) {
                    $chunks[] = array(
                        'content'  => trim($current_chunk),
                        'metadata' => array_merge($metadata, array(
                            'token_count'       => $current_tokens,
                            'char_count'        => strlen($current_chunk),
                            'chunking_strategy' => 'semantic',
                        )),
                    );
                    $current_chunk = '';
                    $current_tokens = 0;
                }

                // Split large paragraph by sentences
                $sentences = $this->split_into_sentences($paragraph);
                $temp_chunk = '';
                $temp_tokens = 0;

                foreach ($sentences as $sentence) {
                    $sentence_tokens = $this->estimate_tokens($sentence);
                    if ($temp_tokens + $sentence_tokens > $chunk_size && !empty($temp_chunk)) {
                        $chunks[] = array(
                            'content'  => trim($temp_chunk),
                            'metadata' => array_merge($metadata, array(
                                'token_count'       => $temp_tokens,
                                'char_count'        => strlen($temp_chunk),
                                'chunking_strategy' => 'semantic',
                            )),
                        );
                        $temp_chunk = '';
                        $temp_tokens = 0;
                    }
                    $temp_chunk .= $sentence . ' ';
                    $temp_tokens += $sentence_tokens;
                }

                if (!empty(trim($temp_chunk))) {
                    $current_chunk = $temp_chunk;
                    $current_tokens = $temp_tokens;
                }
            } else if ($current_tokens + $para_tokens > $chunk_size && !empty($current_chunk)) {
                // Save current chunk and start new one
                $chunks[] = array(
                    'content'  => trim($current_chunk),
                    'metadata' => array_merge($metadata, array(
                        'token_count'       => $current_tokens,
                        'char_count'        => strlen($current_chunk),
                        'chunking_strategy' => 'semantic',
                    )),
                );

                $current_chunk = $paragraph . "\n\n";
                $current_tokens = $para_tokens;
            } else {
                // Add paragraph to current chunk
                $current_chunk .= $paragraph . "\n\n";
                $current_tokens += $para_tokens;
            }
        }

        // Add final chunk
        if (!empty(trim($current_chunk))) {
            $chunks[] = array(
                'content'  => trim($current_chunk),
                'metadata' => array_merge($metadata, array(
                    'token_count'       => $current_tokens,
                    'char_count'        => strlen($current_chunk),
                    'chunking_strategy' => 'semantic',
                )),
            );
        }

        return $chunks;
    }

    /**
     * Format chunks for LangChain Python library
     *
     * Converts chunks to LangChain Document format with page_content
     * and metadata fields.
     *
     * @param array $chunks Array of chunks
     * @return array LangChain-formatted output
     */
    public function format_for_langchain($chunks) {
        $documents = array();

        foreach ($chunks as $chunk) {
            $documents[] = array(
                'page_content' => $chunk['content'],
                'metadata'     => $chunk['metadata'],
            );
        }

        return array(
            'documents'       => $documents,
            'export_metadata' => array(
                'exported_at'    => current_time('mysql'),
                'plugin_version' => SLO_VERSION,
                'total_documents' => count($documents),
                'format'         => 'langchain',
            ),
        );
    }

    /**
     * Format chunks for LlamaIndex Python library
     *
     * Converts chunks to LlamaIndex Document format with text field
     * and document IDs.
     *
     * @param array $chunks Array of chunks
     * @return array LlamaIndex-formatted output
     */
    public function format_for_llamaindex($chunks) {
        $documents = array();

        foreach ($chunks as $chunk) {
            $documents[] = array(
                'text'      => $chunk['content'],
                'metadata'  => $chunk['metadata'],
                'id_'       => sprintf(
                    'post_%d_chunk_%d',
                    $chunk['metadata']['post_id'],
                    $chunk['chunk_index']
                ),
                'embedding' => null,
            );
        }

        return array(
            'documents'       => $documents,
            'export_metadata' => array(
                'exported_at'    => current_time('mysql'),
                'plugin_version' => SLO_VERSION,
                'total_documents' => count($documents),
                'format'         => 'llamaindex',
            ),
        );
    }

    /**
     * Format chunks in universal format
     *
     * Creates a comprehensive format that works with any RAG system.
     * Includes full metadata and export information.
     *
     * @param array $chunks  Array of chunks
     * @param int   $post_id Source post ID
     * @return array Universal-formatted output
     */
    public function format_universal($chunks, $post_id) {
        $post = get_post($post_id);

        if (!$post) {
            return array(
                'format_version' => '1.0',
                'error'          => 'Post not found',
            );
        }

        return array(
            'format_version' => '1.0',
            'export_info'    => array(
                'exported_at'       => current_time('c'),  // ISO 8601
                'plugin_version'    => SLO_VERSION,
                'wordpress_version' => get_bloginfo('version'),
                'format'            => 'universal',
            ),
            'source_document' => array(
                'id'          => $post_id,
                'type'        => get_post_type($post_id),
                'url'         => get_permalink($post_id),
                'title'       => get_the_title($post_id),
                'total_chunks' => count($chunks),
                'date'        => $post->post_date,
                'modified'    => $post->post_modified,
            ),
            'chunks' => $chunks,
        );
    }

    /**
     * Estimate token count from text
     *
     * Uses rough estimation: 1 token ≈ 4 characters for English.
     * This is a simplification; actual tokenization varies by model.
     *
     * @param string $text Text to estimate
     * @return int Estimated token count
     */
    private function estimate_tokens($text) {
        // Rough estimate: 1 token ≈ 4 characters for English
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Split text into sentences
     *
     * Simple sentence splitting on period, exclamation, question mark
     * followed by space and capital letter.
     *
     * @param string $text Text to split
     * @return array Array of sentences
     */
    private function split_into_sentences($text) {
        // Split on sentence boundaries
        // Pattern: period/exclamation/question followed by space and capital letter
        $sentences = preg_split(
            '/(?<=[.!?])\s+(?=[A-Z])/',
            $text,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        // Fallback: if no sentences found, split by double newline
        if (count($sentences) <= 1) {
            $sentences = preg_split('/\n\n+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        }

        return $sentences;
    }

    /**
     * Get overlap text from sentence buffer
     *
     * Builds overlap text by working backwards through sentence buffer
     * until target token count is reached.
     *
     * @param array $sentence_buffer Array of recent sentences
     * @param int   $target_tokens   Target overlap size in tokens
     * @return string Overlap text
     */
    private function get_overlap_text($sentence_buffer, $target_tokens) {
        $overlap_text = '';
        $overlap_tokens = 0;

        // Work backwards through sentence buffer
        for ($i = count($sentence_buffer) - 1; $i >= 0; $i--) {
            $sentence = $sentence_buffer[$i];
            $sentence_tokens = $this->estimate_tokens($sentence);

            if ($overlap_tokens + $sentence_tokens <= $target_tokens) {
                $overlap_text = $sentence . ' ' . $overlap_text;
                $overlap_tokens += $sentence_tokens;
            } else {
                break;
            }
        }

        return trim($overlap_text);
    }

    /**
     * Create base metadata for chunk
     *
     * Generates metadata including post information, categories, tags,
     * and other relevant data for RAG systems.
     *
     * @param int    $post_id       Post ID
     * @param int    $index         Chunk index
     * @param int    $total         Total chunks
     * @param string $section_title Section title (for hierarchical chunks)
     * @return array Metadata array
     */
    private function create_chunk_metadata($post_id, $index, $total, $section_title = '') {
        $post = get_post($post_id);

        if (!$post) {
            return array();
        }

        // Get categories
        $categories = wp_get_post_categories($post_id, array('fields' => 'names'));

        // Get tags
        $tags = wp_get_post_tags($post_id, array('fields' => 'names'));

        $metadata = array(
            'post_id'      => $post_id,
            'chunk_index'  => $index,
            'total_chunks' => $total,
            'source_type'  => get_post_type($post_id),
            'title'        => get_the_title($post_id),
            'url'          => get_permalink($post_id),
            'date'         => $post->post_date,
            'modified'     => $post->post_modified,
            'author'       => get_the_author_meta('display_name', $post->post_author),
            'categories'   => $categories,
            'tags'         => $tags,
        );

        // Add section title if provided
        if (!empty($section_title)) {
            $metadata['section_title'] = $section_title;
        }

        return $metadata;
    }

    /**
     * AJAX handler for getting chunks
     *
     * Handles frontend and admin AJAX requests for chunk generation.
     * Returns chunks in requested format.
     */
    public function ajax_get_chunks() {
        // Verify nonce
        check_ajax_referer('slo_get_content', 'nonce');

        // Get and validate post ID
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (!$post_id) {
            wp_send_json_error(array(
                'message' => __('Invalid post ID', 'seo-llm-optimizer'),
            ));
        }

        // Get chunking parameters
        $strategy = isset($_POST['strategy']) ? sanitize_text_field($_POST['strategy']) : 'hierarchical';
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'universal';

        // Get options
        $options = array(
            'chunk_size' => isset($_POST['chunk_size']) ? absint($_POST['chunk_size']) : 512,
            'overlap'    => isset($_POST['overlap']) ? absint($_POST['overlap']) : 128,
        );

        // Validate options
        if ($options['chunk_size'] < 128) {
            $options['chunk_size'] = 128;
        }
        if ($options['chunk_size'] > 2048) {
            $options['chunk_size'] = 2048;
        }
        if ($options['overlap'] < 0) {
            $options['overlap'] = 0;
        }
        if ($options['overlap'] > 512) {
            $options['overlap'] = 512;
        }

        // Create chunks
        $chunks = $this->get_cached_chunks($post_id, $strategy, $options);

        if (is_wp_error($chunks)) {
            wp_send_json_error(array(
                'message' => $chunks->get_error_message(),
            ));
        }

        // Format based on requested format
        switch ($format) {
            case 'langchain':
                $output = $this->format_for_langchain($chunks);
                break;

            case 'llamaindex':
                $output = $this->format_for_llamaindex($chunks);
                break;

            case 'universal':
            default:
                $output = $this->format_universal($chunks, $post_id);
                break;
        }

        wp_send_json_success($output);
    }

    /**
     * Get chunking statistics for a post
     *
     * Returns information about chunk count, sizes, and distribution
     * for different chunking strategies.
     *
     * @param int $post_id Post ID
     * @return array|WP_Error Statistics or error
     */
    public function get_chunking_stats($post_id) {
        $stats = array();

        $strategies = array('hierarchical', 'fixed', 'semantic');
        $options = array(
            'chunk_size' => (int) get_option('slo_chunk_size', 512),
            'overlap'    => (int) get_option('slo_chunk_overlap', 128),
        );

        foreach ($strategies as $strategy) {
            $chunks = $this->get_cached_chunks($post_id, $strategy, $options);

            if (is_wp_error($chunks)) {
                continue;
            }

            $token_counts = array();
            foreach ($chunks as $chunk) {
                $token_counts[] = $chunk['metadata']['token_count'];
            }

            $stats[$strategy] = array(
                'total_chunks' => count($chunks),
                'min_tokens'   => !empty($token_counts) ? min($token_counts) : 0,
                'max_tokens'   => !empty($token_counts) ? max($token_counts) : 0,
                'avg_tokens'   => !empty($token_counts) ? array_sum($token_counts) / count($token_counts) : 0,
            );
        }

        return $stats;
    }

    /**
     * Legacy method for backward compatibility
     *
     * @deprecated Use create_chunks() instead
     * @param string $content    Content to chunk
     * @param int    $chunk_size Target chunk size
     * @return array Array of content chunks
     */
    public function chunk_content($content, $chunk_size = null) {
        if (null === $chunk_size) {
            $chunk_size = (int) get_option('slo_chunk_size', 512);
        }

        // Simple backward-compatible implementation
        $sentences = $this->split_into_sentences($content);
        $chunks = array();
        $current_chunk = '';
        $current_tokens = 0;

        foreach ($sentences as $sentence) {
            $sentence_tokens = $this->estimate_tokens($sentence);

            if ($current_tokens + $sentence_tokens > $chunk_size && !empty($current_chunk)) {
                $chunks[] = trim($current_chunk);
                $current_chunk = '';
                $current_tokens = 0;
            }

            $current_chunk .= $sentence . ' ';
            $current_tokens += $sentence_tokens;
        }

        if (!empty(trim($current_chunk))) {
            $chunks[] = trim($current_chunk);
        }

        return $chunks;
    }

    /**
     * Legacy method for backward compatibility
     *
     * @deprecated Use create_chunks() with appropriate strategy
     * @param int $content_length Content length in characters
     * @return int Optimal chunk size
     */
    public function get_optimal_chunk_size($content_length) {
        return (int) get_option('slo_chunk_size', 512);
    }
}
