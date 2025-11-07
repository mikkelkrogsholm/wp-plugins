<?php
/**
 * Content Processor
 *
 * Main content processing orchestrator that coordinates content cleaning,
 * conversion, and chunking operations.
 *
 * @package SEO_LLM_Optimizer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles content processing operations
 */
class SLO_Content_Processor {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Content cleaner instance
     *
     * @var SLO_Content_Cleaner
     */
    private $cleaner;

    /**
     * Get singleton instance
     *
     * @return SLO_Content_Processor
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
        // AJAX handler for frontend
        add_action('wp_ajax_slo_get_markdown', array($this, 'ajax_get_markdown'));
        add_action('wp_ajax_nopriv_slo_get_markdown', array($this, 'ajax_get_markdown'));
    }

    /**
     * Get content cleaner instance
     *
     * @return SLO_Content_Cleaner
     */
    private function get_cleaner() {
        if (null === $this->cleaner) {
            $this->cleaner = SLO_Content_Cleaner::get_instance();
        }
        return $this->cleaner;
    }

    /**
     * Convert post content to markdown
     *
     * @param int   $post_id Post ID to process
     * @param array $options Processing options
     *                       - include_metadata: Add YAML frontmatter (default: true)
     *                       - include_images: Preserve image markdown (default: true)
     *                       - preserve_links: Convert links to markdown (default: true)
     * @return string|WP_Error Markdown content or error
     */
    public function convert_to_markdown($post_id, $options = array()) {
        // Sanitize and validate post ID
        $post_id = absint($post_id);
        if (!$post_id) {
            return new WP_Error('invalid_post_id', __('Invalid post ID', 'seo-llm-optimizer'));
        }

        // Get post object
        $post = get_post($post_id);
        if (!$post || 'publish' !== $post->post_status) {
            return new WP_Error('invalid_post', __('Post not found or not published', 'seo-llm-optimizer'));
        }

        // Parse options with defaults
        $options = wp_parse_args($options, array(
            'include_metadata' => true,
            'include_images'   => true,
            'preserve_links'   => true,
        ));

        try {
            // Step 1: Detect content type
            $content_type = $this->detect_content_type($post->post_content);

            // Step 2: Process content based on type
            if ('gutenberg' === $content_type) {
                $html = $this->process_gutenberg_content($post);
            } else {
                $html = $this->process_classic_content($post);
            }

            // Step 3: Clean WordPress elements
            $cleaner = $this->get_cleaner();
            $html = $cleaner->strip_shortcodes_preserve_content($html);
            $html = $cleaner->remove_wordpress_embeds($html);
            $html = $cleaner->strip_theme_elements($html);

            // Step 4: Enhance structure
            $html = $cleaner->enhance_semantic_structure($html);

            if ($options['preserve_links']) {
                $html = $cleaner->enhance_links($html);
            }

            // Step 5: Convert to markdown
            $markdown = $cleaner->convert_to_markdown($html);

            // Step 6: Add YAML frontmatter if requested
            if ($options['include_metadata']) {
                $markdown = $this->add_yaml_frontmatter($post_id, $markdown);
            }

            return $markdown;

        } catch (Exception $e) {
            return new WP_Error('processing_error', $e->getMessage());
        }
    }

    /**
     * Detect content type (Gutenberg vs Classic)
     *
     * @param string $content Post content
     * @return string 'gutenberg' or 'classic'
     */
    private function detect_content_type($content) {
        // Gutenberg blocks contain HTML comments with wp: prefix
        if (false !== strpos($content, '<!-- wp:')) {
            return 'gutenberg';
        }
        return 'classic';
    }

    /**
     * Process Gutenberg block content
     *
     * @param WP_Post $post Post object
     * @return string Processed HTML content
     */
    private function process_gutenberg_content($post) {
        // Parse blocks
        $blocks = parse_blocks($post->post_content);

        $html_parts = array();

        // Process each block
        foreach ($blocks as $block) {
            $content = $this->process_block($block);
            if (!empty($content)) {
                $html_parts[] = $content;
            }
        }

        return implode("\n\n", $html_parts);
    }

    /**
     * Process individual block (recursive for nested blocks)
     *
     * @param array $block Block data
     * @return string Block HTML content
     */
    private function process_block($block) {
        // Skip empty blocks
        if (empty($block['blockName'])) {
            return '';
        }

        // Skip navigation, social, and widget blocks
        $skip_blocks = array(
            'core/navigation',
            'core/social-links',
            'core/widget-area',
            'core/widget-group',
            'core/legacy-widget',
        );

        if (in_array($block['blockName'], $skip_blocks, true)) {
            return '';
        }

        $content = '';

        // Get block HTML content
        if (!empty($block['innerHTML'])) {
            $content = $block['innerHTML'];
        }

        // Process nested blocks recursively
        if (!empty($block['innerBlocks'])) {
            $inner_parts = array();
            foreach ($block['innerBlocks'] as $inner_block) {
                $inner_content = $this->process_block($inner_block);
                if (!empty($inner_content)) {
                    $inner_parts[] = $inner_content;
                }
            }
            $content .= implode("\n", $inner_parts);
        }

        return $content;
    }

    /**
     * Process classic editor content
     *
     * @param WP_Post $post Post object
     * @return string Processed HTML content
     */
    private function process_classic_content($post) {
        // Apply the_content filter to process shortcodes, embeds, etc.
        $content = apply_filters('the_content', $post->post_content);
        return $content;
    }

    /**
     * Add YAML frontmatter to markdown
     *
     * @param int    $post_id  Post ID
     * @param string $markdown Markdown content
     * @return string Markdown with YAML frontmatter
     */
    private function add_yaml_frontmatter($post_id, $markdown) {
        $post = get_post($post_id);
        if (!$post) {
            return $markdown;
        }

        // Get post metadata
        $title    = get_the_title($post_id);
        $date     = get_the_date('Y-m-d H:i:s', $post_id);
        $author   = get_the_author_meta('display_name', $post->post_author);
        $url      = get_permalink($post_id);
        $excerpt  = get_the_excerpt($post_id);

        // Get categories
        $categories = get_the_category($post_id);
        $cat_names  = array();
        if ($categories) {
            foreach ($categories as $category) {
                $cat_names[] = $category->name;
            }
        }

        // Get tags
        $tags      = get_the_tags($post_id);
        $tag_names = array();
        if ($tags) {
            foreach ($tags as $tag) {
                $tag_names[] = $tag->name;
            }
        }

        // Build YAML frontmatter
        $yaml = "---\n";
        $yaml .= "title: \"" . addslashes($title) . "\"\n";
        $yaml .= "date: \"" . $date . "\"\n";
        $yaml .= "author: \"" . addslashes($author) . "\"\n";
        $yaml .= "url: \"" . $url . "\"\n";

        if (!empty($excerpt)) {
            $yaml .= "excerpt: \"" . addslashes($excerpt) . "\"\n";
        }

        if (!empty($cat_names)) {
            $yaml .= "categories:\n";
            foreach ($cat_names as $cat) {
                $yaml .= "  - \"" . addslashes($cat) . "\"\n";
            }
        }

        if (!empty($tag_names)) {
            $yaml .= "tags:\n";
            foreach ($tag_names as $tag) {
                $yaml .= "  - \"" . addslashes($tag) . "\"\n";
            }
        }

        $yaml .= "---\n\n";

        return $yaml . $markdown;
    }

    /**
     * AJAX handler for getting markdown content
     */
    public function ajax_get_markdown() {
        // Verify nonce
        check_ajax_referer('slo_get_content', 'nonce');

        // Get and validate post ID
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (!$post_id) {
            wp_send_json_error(array(
                'message' => __('Invalid post ID', 'seo-llm-optimizer'),
            ));
        }

        // Get options
        $options = array(
            'include_metadata' => isset($_POST['include_metadata']) ? (bool) $_POST['include_metadata'] : true,
            'include_images'   => isset($_POST['include_images']) ? (bool) $_POST['include_images'] : true,
            'preserve_links'   => isset($_POST['preserve_links']) ? (bool) $_POST['preserve_links'] : true,
        );

        // Convert to markdown
        $markdown = $this->convert_to_markdown($post_id, $options);

        // Handle errors
        if (is_wp_error($markdown)) {
            wp_send_json_error(array(
                'message' => $markdown->get_error_message(),
            ));
        }

        // Return success
        wp_send_json_success(array(
            'markdown' => $markdown,
            'post_id'  => $post_id,
        ));
    }

    /**
     * Process post content for LLM optimization
     *
     * @param int $post_id Post ID to process
     * @return array|WP_Error Processed content data or error
     */
    public function process_post($post_id) {
        // Validate post ID
        $post_id = absint($post_id);
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('invalid_post', __('Invalid post ID', 'seo-llm-optimizer'));
        }

        // Convert to markdown
        $markdown = $this->convert_to_markdown($post_id);

        if (is_wp_error($markdown)) {
            return $markdown;
        }

        return array(
            'post_id'  => $post_id,
            'markdown' => $markdown,
            'processed' => true,
        );
    }
}
