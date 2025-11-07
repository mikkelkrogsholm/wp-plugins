<?php
/**
 * Display cluster links on frontend
 *
 * @package SEO_Cluster_Links
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles display of cluster links
 */
class SCL_Link_Display {

    /**
     * Singleton instance
     */
    private static $instance = null;

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
        add_filter('the_content', array($this, 'append_cluster_links'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_shortcode('cluster_links', array($this, 'cluster_links_shortcode'));
    }

    /**
     * Enqueue frontend CSS
     *
     * Uses WordPress 6.8+ conditional loading for performance
     */
    public function enqueue_frontend_assets() {
        // Early return for better performance
        if (!is_singular('post')) {
            return;
        }

        wp_enqueue_style(
            'scl-frontend',
            SCL_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            SCL_VERSION,
            'all'
        );
    }

    /**
     * Append cluster links to post content
     */
    public function append_cluster_links($content) {
        if (!is_singular('post') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        $links = $this->get_cluster_links_html();

        if ($links) {
            $content .= $links;
        }

        return $content;
    }

    /**
     * Shortcode for manual placement
     */
    public function cluster_links_shortcode($atts) {
        return $this->get_cluster_links_html();
    }

    /**
     * Get cluster links HTML
     */
    private function get_cluster_links_html() {
        global $post;

        $post_type = get_post_meta($post->ID, SCL_Meta_Boxes::META_POST_TYPE, true);

        // Early return if not a cluster post
        if (!$post_type) {
            return '';
        }

        $html = '<div class="scl-links-container">';

        if ($post_type === 'pillar') {
            $html .= $this->render_pillar_links($post->ID);
        } elseif ($post_type === 'cluster') {
            $html .= $this->render_cluster_links($post->ID);
        }

        $html .= '</div>';

        // Allow filtering of complete HTML output
        return apply_filters('scl_cluster_links_html', $html, $post->ID, $post_type);
    }

    /**
     * Render links for pillar post
     */
    private function render_pillar_links($post_id) {
        $clusters = $this->get_cluster_posts($post_id);

        // Early return if no clusters found
        if (empty($clusters)) {
            return '';
        }

        // Allow filtering the maximum number of clusters to display
        $max_clusters = apply_filters('scl_max_clusters_display', -1, $post_id);
        if ($max_clusters > 0) {
            $clusters = array_slice($clusters, 0, $max_clusters);
        }

        // Allow filtering the pillar heading text
        $heading_text = apply_filters('scl_pillar_heading', __('Related Topics', 'seo-cluster-links'), $post_id);

        $html = '<div class="scl-pillar-links">';
        $html .= '<h3 class="scl-heading">' . esc_html($heading_text) . '</h3>';
        $html .= '<ul class="scl-links-list">';

        foreach ($clusters as $cluster) {
            $html .= sprintf(
                '<li><a href="%s">%s</a></li>',
                esc_url(get_permalink($cluster->ID)),
                esc_html($cluster->post_title)
            );
        }

        $html .= '</ul></div>';

        return $html;
    }

    /**
     * Render links for cluster post
     */
    private function render_cluster_links($post_id) {
        $pillar_id = get_post_meta($post_id, SCL_Meta_Boxes::META_PILLAR_ID, true);

        // Early return if no pillar ID
        if (!$pillar_id) {
            error_log(sprintf('SCL: No pillar ID found for cluster post %d', $post_id));
            return '';
        }

        // Validate pillar ID
        $pillar_id = absint($pillar_id);
        if (!$pillar_id) {
            error_log(sprintf('SCL: Invalid pillar ID for cluster post %d', $post_id));
            return '';
        }

        $pillar = get_post($pillar_id);

        // Early return if pillar doesn't exist or isn't published
        if (!$pillar || $pillar->post_status !== 'publish') {
            error_log(sprintf('SCL: Pillar post %d not found or not published for cluster post %d', $pillar_id, $post_id));
            return '';
        }

        $other_clusters = $this->get_cluster_posts($pillar_id, $post_id);

        // Allow filtering the maximum number of related clusters to display
        $max_clusters = apply_filters('scl_max_clusters_display', -1, $post_id);
        if ($max_clusters > 0 && !empty($other_clusters)) {
            $other_clusters = array_slice($other_clusters, 0, $max_clusters);
        }

        // Allow filtering the cluster heading text (for pillar link section)
        $pillar_heading = apply_filters('scl_cluster_pillar_heading', __('Main Topic', 'seo-cluster-links'), $post_id, $pillar_id);

        // Allow filtering the cluster heading text (for related clusters section)
        $cluster_heading = apply_filters('scl_cluster_heading', __('Related Topics', 'seo-cluster-links'), $post_id, $pillar_id);

        $html = '<div class="scl-cluster-links">';

        // Link back to pillar
        $html .= '<div class="scl-pillar-link">';
        $html .= '<h3 class="scl-heading">' . esc_html($pillar_heading) . '</h3>';
        $html .= sprintf(
            '<p><a href="%s" class="scl-pillar-link-item">%s</a></p>',
            esc_url(get_permalink($pillar->ID)),
            esc_html($pillar->post_title)
        );
        $html .= '</div>';

        // Links to other cluster posts
        if (!empty($other_clusters)) {
            $html .= '<div class="scl-related-clusters">';
            $html .= '<h3 class="scl-heading">' . esc_html($cluster_heading) . '</h3>';
            $html .= '<ul class="scl-links-list">';

            foreach ($other_clusters as $cluster) {
                $html .= sprintf(
                    '<li><a href="%s">%s</a></li>',
                    esc_url(get_permalink($cluster->ID)),
                    esc_html($cluster->post_title)
                );
            }

            $html .= '</ul></div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Get cluster posts for a pillar
     *
     * Uses WordPress object caching for performance (1 hour cache)
     *
     * @param int $pillar_id Pillar post ID
     * @param int|null $exclude_id Post ID to exclude from results
     * @return array Array of post objects
     */
    private function get_cluster_posts($pillar_id, $exclude_id = null) {
        // Validate and sanitize post IDs
        $pillar_id = absint($pillar_id);

        if (!$pillar_id) {
            error_log('SCL: Invalid pillar ID provided to get_cluster_posts()');
            return array();
        }

        // Build cache key including exclude_id for uniqueness
        $cache_key = 'cluster_posts_' . $pillar_id . '_' . ($exclude_id ? absint($exclude_id) : '0');
        $cache_group = 'seo_cluster_links';

        // Try to get cached results
        $cached_posts = wp_cache_get($cache_key, $cache_group);
        if (false !== $cached_posts) {
            return $cached_posts;
        }

        // Build query arguments
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => SCL_Meta_Boxes::META_POST_TYPE,
                    'value' => 'cluster',
                ),
                array(
                    'key' => SCL_Meta_Boxes::META_PILLAR_ID,
                    'value' => $pillar_id,
                    'type' => 'NUMERIC',
                ),
            ),
            'orderby' => 'date',
            'order' => 'DESC',
            'fields' => 'all', // Get full post objects
            'no_found_rows' => true, // Performance: skip pagination count query
            'update_post_meta_cache' => false, // Performance: skip meta cache update (we only need specific meta)
        );

        if ($exclude_id) {
            $exclude_id = absint($exclude_id);
            if ($exclude_id) {
                $args['post__not_in'] = array($exclude_id);
            }
        }

        // Allow filtering of query arguments for extensibility
        $args = apply_filters('scl_cluster_query_args', $args, $pillar_id, $exclude_id);

        // Execute query
        $posts = get_posts($args);

        // Cache results for 1 hour
        wp_cache_set($cache_key, $posts, $cache_group, HOUR_IN_SECONDS);

        return $posts;
    }
}
