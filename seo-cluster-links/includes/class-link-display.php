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
     */
    public function enqueue_frontend_assets() {
        if (is_singular('post')) {
            wp_enqueue_style(
                'scl-frontend',
                SCL_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                SCL_VERSION
            );
        }
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

        return $html;
    }

    /**
     * Render links for pillar post
     */
    private function render_pillar_links($post_id) {
        $clusters = $this->get_cluster_posts($post_id);

        if (empty($clusters)) {
            return '';
        }

        $html = '<div class="scl-pillar-links">';
        $html .= '<h3 class="scl-heading">' . __('Related Topics', 'seo-cluster-links') . '</h3>';
        $html .= '<ul class="scl-links-list">';

        foreach ($clusters as $cluster) {
            $html .= sprintf(
                '<li><a href="%s">%s</a></li>',
                get_permalink($cluster->ID),
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

        if (!$pillar_id) {
            return '';
        }

        $pillar = get_post($pillar_id);

        if (!$pillar || $pillar->post_status !== 'publish') {
            return '';
        }

        $other_clusters = $this->get_cluster_posts($pillar_id, $post_id);

        $html = '<div class="scl-cluster-links">';

        // Link back to pillar
        $html .= '<div class="scl-pillar-link">';
        $html .= '<h3 class="scl-heading">' . __('Main Topic', 'seo-cluster-links') . '</h3>';
        $html .= sprintf(
            '<p><a href="%s" class="scl-pillar-link-item">%s</a></p>',
            get_permalink($pillar->ID),
            esc_html($pillar->post_title)
        );
        $html .= '</div>';

        // Links to other cluster posts
        if (!empty($other_clusters)) {
            $html .= '<div class="scl-related-clusters">';
            $html .= '<h3 class="scl-heading">' . __('Related Topics', 'seo-cluster-links') . '</h3>';
            $html .= '<ul class="scl-links-list">';

            foreach ($other_clusters as $cluster) {
                $html .= sprintf(
                    '<li><a href="%s">%s</a></li>',
                    get_permalink($cluster->ID),
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
     */
    private function get_cluster_posts($pillar_id, $exclude_id = null) {
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
                ),
            ),
            'orderby' => 'date',
            'order' => 'DESC',
        );

        if ($exclude_id) {
            $args['post__not_in'] = array($exclude_id);
        }

        return get_posts($args);
    }
}
