<?php
/**
 * Cache Manager
 *
 * Manages caching of processed content to improve performance.
 *
 * @package SEO_LLM_Optimizer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles cache operations
 */
class SLO_Cache_Manager {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Cache group name
     */
    const CACHE_GROUP = 'seo_llm_optimizer';

    /**
     * Get singleton instance
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
     * Get cached content
     *
     * @param string $key Cache key
     * @return mixed|false Cached value or false if not found
     */
    public function get($key) {
        return wp_cache_get($key, self::CACHE_GROUP);
    }

    /**
     * Set cached content
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $expiration Cache expiration in seconds
     * @return bool True on success, false on failure
     */
    public function set($key, $value, $expiration = null) {
        if (null === $expiration) {
            $expiration = (int) get_option('slo_cache_duration', 3600);
        }
        return wp_cache_set($key, $value, self::CACHE_GROUP, $expiration);
    }

    /**
     * Delete cached content
     *
     * @param string $key Cache key
     * @return bool True on success, false on failure
     */
    public function delete($key) {
        return wp_cache_delete($key, self::CACHE_GROUP);
    }

    /**
     * Invalidate all cache for a post
     *
     * @param int $post_id Post ID
     */
    public function invalidate_post($post_id) {
        $this->delete('processed_' . $post_id);
        $this->delete('chunks_' . $post_id);
        $this->delete('markdown_' . $post_id);
    }
}
