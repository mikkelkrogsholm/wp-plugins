<?php
/**
 * Content Cleaner
 *
 * Cleans and sanitizes HTML content, removing unnecessary elements
 * and preparing content for LLM processing.
 *
 * @package SEO_LLM_Optimizer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles content cleaning operations
 */
class SLO_Content_Cleaner {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return SLO_Content_Cleaner
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
        // Hook initialization will be added here
    }

    /**
     * Strip shortcodes but preserve content between tags
     *
     * @param string $content Content with shortcodes
     * @return string Content without shortcodes
     */
    public function strip_shortcodes_preserve_content($content) {
        // First, strip registered shortcodes (WordPress built-in)
        $content = strip_shortcodes($content);

        // Remove unregistered shortcodes but preserve content between tags
        // Pattern: [shortcode]content[/shortcode] -> content
        $content = preg_replace('/\[([^\]]+)\](.*?)\[\/\1\]/s', '$2', $content);

        // Remove standalone shortcodes: [shortcode] or [shortcode attr="value"]
        $content = preg_replace('/\[[^\]]+\]/', '', $content);

        return $content;
    }

    /**
     * Remove theme-specific elements (navigation, sidebars, etc.)
     *
     * @param string $html HTML content
     * @return string Cleaned HTML
     */
    public function strip_theme_elements($html) {
        if (empty($html)) {
            return $html;
        }

        // Suppress errors for malformed HTML
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new DOMXPath($dom);

        // Elements to remove (by tag name)
        $remove_tags = array('nav', 'aside', 'footer', 'header');

        foreach ($remove_tags as $tag) {
            $elements = $xpath->query('//' . $tag);
            foreach ($elements as $element) {
                if ($element->parentNode) {
                    $element->parentNode->removeChild($element);
                }
            }
        }

        // Remove by class name
        $remove_classes = array('sidebar', 'navigation', 'menu', 'comments', 'widget');

        foreach ($remove_classes as $class) {
            // XPath for class contains
            $elements = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')]");
            foreach ($elements as $element) {
                if ($element->parentNode) {
                    $element->parentNode->removeChild($element);
                }
            }
        }

        // Remove by ID
        $remove_ids = array('sidebar', 'navigation', 'footer');

        foreach ($remove_ids as $id) {
            $elements = $xpath->query("//*[@id='{$id}']");
            foreach ($elements as $element) {
                if ($element->parentNode) {
                    $element->parentNode->removeChild($element);
                }
            }
        }

        // Get cleaned HTML
        $cleaned = $dom->saveHTML();

        // Remove XML encoding declaration
        $cleaned = preg_replace('/^<\?xml[^>]+>\s*/', '', $cleaned);

        // Clear errors
        libxml_clear_errors();

        return $cleaned;
    }

    /**
     * Remove WordPress embeds
     *
     * @param string $content Content with embeds
     * @return string Content without embeds
     */
    public function remove_wordpress_embeds($content) {
        // Remove wp-embed divs
        $content = preg_replace('/<div[^>]*wp-embed[^>]*>.*?<\/div>/s', '', $content);

        // Remove embed figures (Gutenberg embed blocks)
        $content = preg_replace('/<figure[^>]*wp-block-embed[^>]*>.*?<\/figure>/s', '', $content);

        return $content;
    }

    /**
     * Enhance semantic structure (images, blockquotes, code)
     *
     * @param string $html HTML content
     * @return string Enhanced HTML
     */
    public function enhance_semantic_structure($html) {
        if (empty($html)) {
            return $html;
        }

        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new DOMXPath($dom);

        // Process images - ensure alt text is preserved
        $images = $xpath->query('//img');
        foreach ($images as $img) {
            if (!$img->hasAttribute('alt')) {
                $img->setAttribute('alt', '');
            }
        }

        // Process figures with captions
        $figures = $xpath->query('//figure');
        foreach ($figures as $figure) {
            $figcaption = $xpath->query('.//figcaption', $figure);
            if ($figcaption->length > 0) {
                // Caption is preserved in the DOM
                continue;
            }
        }

        // Process blockquotes - preserve cite elements
        $blockquotes = $xpath->query('//blockquote');
        foreach ($blockquotes as $blockquote) {
            // Citations are preserved in the DOM
            continue;
        }

        // Process code blocks - preserve class for language hints
        $code_blocks = $xpath->query('//pre/code');
        foreach ($code_blocks as $code) {
            // Language hints preserved in class attribute
            continue;
        }

        $enhanced = $dom->saveHTML();
        $enhanced = preg_replace('/^<\?xml[^>]+>\s*/', '', $enhanced);

        libxml_clear_errors();

        return $enhanced;
    }

    /**
     * Enhance links by converting relative URLs to absolute
     *
     * @param string $html HTML content
     * @return string HTML with absolute URLs
     */
    public function enhance_links($html) {
        if (empty($html)) {
            return $html;
        }

        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new DOMXPath($dom);

        // Get site URL
        $site_url = get_site_url();

        // Process all links
        $links = $xpath->query('//a[@href]');
        foreach ($links as $link) {
            $href = $link->getAttribute('href');

            // Skip if already absolute (has protocol)
            if (preg_match('/^https?:\/\//', $href)) {
                continue;
            }

            // Skip anchors and javascript
            if (preg_match('/^(#|javascript:)/', $href)) {
                continue;
            }

            // Convert relative to absolute
            if (0 === strpos($href, '/')) {
                // Root-relative URL
                $absolute = $site_url . $href;
            } else {
                // Relative URL
                $absolute = $site_url . '/' . ltrim($href, '/');
            }

            $link->setAttribute('href', $absolute);
        }

        $enhanced = $dom->saveHTML();
        $enhanced = preg_replace('/^<\?xml[^>]+>\s*/', '', $enhanced);

        libxml_clear_errors();

        return $enhanced;
    }

    /**
     * Convert HTML to Markdown (lightweight implementation)
     *
     * @param string $html HTML content
     * @return string Markdown content
     */
    public function convert_to_markdown($html) {
        if (empty($html)) {
            return '';
        }

        // Clean up HTML first
        $markdown = $html;

        // Headers (h1-h6)
        $markdown = preg_replace('/<h1[^>]*>(.*?)<\/h1>/is', "\n# $1\n", $markdown);
        $markdown = preg_replace('/<h2[^>]*>(.*?)<\/h2>/is', "\n## $1\n", $markdown);
        $markdown = preg_replace('/<h3[^>]*>(.*?)<\/h3>/is', "\n### $1\n", $markdown);
        $markdown = preg_replace('/<h4[^>]*>(.*?)<\/h4>/is', "\n#### $1\n", $markdown);
        $markdown = preg_replace('/<h5[^>]*>(.*?)<\/h5>/is', "\n##### $1\n", $markdown);
        $markdown = preg_replace('/<h6[^>]*>(.*?)<\/h6>/is', "\n###### $1\n", $markdown);

        // Bold
        $markdown = preg_replace('/<(strong|b)[^>]*>(.*?)<\/\1>/is', '**$2**', $markdown);

        // Italic
        $markdown = preg_replace('/<(em|i)[^>]*>(.*?)<\/\1>/is', '*$2*', $markdown);

        // Images with alt text
        $markdown = preg_replace_callback('/<img[^>]*src=["\']([^"\']+)["\'][^>]*alt=["\']([^"\']*)["\'][^>]*>/i', function($matches) {
            return '![' . $matches[2] . '](' . $matches[1] . ')';
        }, $markdown);
        // Images without alt
        $markdown = preg_replace('/<img[^>]*src=["\']([^"\']+)["\'][^>]*>/i', '![]($1)', $markdown);

        // Links
        $markdown = preg_replace('/<a[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', '[$2]($1)', $markdown);

        // Code blocks (pre > code)
        $markdown = preg_replace('/<pre[^>]*><code[^>]*>(.*?)<\/code><\/pre>/is', "\n```\n$1\n```\n", $markdown);

        // Inline code
        $markdown = preg_replace('/<code[^>]*>(.*?)<\/code>/is', '`$1`', $markdown);

        // Blockquotes
        $markdown = preg_replace_callback('/<blockquote[^>]*>(.*?)<\/blockquote>/is', function($matches) {
            $lines = explode("\n", trim($matches[1]));
            $quoted = array();
            foreach ($lines as $line) {
                $quoted[] = '> ' . $line;
            }
            return "\n" . implode("\n", $quoted) . "\n";
        }, $markdown);

        // Unordered lists
        $markdown = preg_replace('/<ul[^>]*>(.*?)<\/ul>/is', "$1\n", $markdown);
        $markdown = preg_replace('/<li[^>]*>(.*?)<\/li>/is', "- $1\n", $markdown);

        // Ordered lists (simple implementation)
        $markdown = preg_replace_callback('/<ol[^>]*>(.*?)<\/ol>/is', function($matches) {
            $items = preg_split('/<\/li>/', $matches[1]);
            $result = '';
            $counter = 1;
            foreach ($items as $item) {
                $item = preg_replace('/<li[^>]*>/', '', $item);
                $item = trim($item);
                if (!empty($item)) {
                    $result .= $counter . '. ' . $item . "\n";
                    $counter++;
                }
            }
            return $result;
        }, $markdown);

        // Paragraphs
        $markdown = preg_replace('/<p[^>]*>(.*?)<\/p>/is', "$1\n\n", $markdown);

        // Line breaks
        $markdown = preg_replace('/<br\s*\/?>/i', "\n", $markdown);

        // Horizontal rules
        $markdown = preg_replace('/<hr\s*\/?>/i', "\n---\n", $markdown);

        // Remove remaining HTML tags
        $markdown = strip_tags($markdown);

        // Decode HTML entities
        $markdown = html_entity_decode($markdown, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Clean up excessive whitespace
        $markdown = preg_replace('/\n{3,}/', "\n\n", $markdown);
        $markdown = trim($markdown);

        return $markdown;
    }

    /**
     * Clean HTML content (legacy method for compatibility)
     *
     * @param string $content HTML content to clean
     * @return string Cleaned content
     */
    public function clean_content($content) {
        // Strip shortcodes
        $content = $this->strip_shortcodes_preserve_content($content);

        // Remove embeds
        $content = $this->remove_wordpress_embeds($content);

        // Strip theme elements
        $content = $this->strip_theme_elements($content);

        // Enhance structure
        $content = $this->enhance_semantic_structure($content);

        return $content;
    }

    /**
     * Convert HTML to Markdown (legacy alias)
     *
     * @param string $html HTML content
     * @return string Markdown content
     */
    public function html_to_markdown($html) {
        return $this->convert_to_markdown($html);
    }
}
