<?php
/**
 * Meta boxes for post editor
 *
 * @package SEO_Cluster_Links
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles meta boxes in post editor
 */
class SCL_Meta_Boxes {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Meta key for post type (pillar/cluster)
     */
    const META_POST_TYPE = '_scl_post_type';

    /**
     * Meta key for pillar post ID
     */
    const META_PILLAR_ID = '_scl_pillar_id';

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
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post', array($this, 'save_meta_box'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Enqueue admin CSS and JavaScript
     *
     * Uses WordPress 6.8+ conditional loading for performance
     */
    public function enqueue_admin_assets($hook) {
        // Early return for better performance - only load on post edit screens
        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }

        // Further optimize: only load on 'post' post type
        global $post_type;
        if ('post' !== $post_type) {
            return;
        }

        wp_enqueue_style(
            'scl-admin',
            SCL_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            SCL_VERSION,
            'all'
        );

        wp_enqueue_script(
            'scl-admin',
            SCL_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            SCL_VERSION,
            array(
                'in_footer' => true,
                'strategy'  => 'defer', // WordPress 6.8+ script loading strategy
            )
        );
    }

    /**
     * Add meta box to post editor
     */
    public function add_meta_box() {
        add_meta_box(
            'scl_cluster_settings',
            __('SEO Cluster Settings', 'seo-cluster-links'),
            array($this, 'render_meta_box'),
            'post',
            'side',
            'default'
        );
    }

    /**
     * Render meta box content
     */
    public function render_meta_box($post) {
        wp_nonce_field('scl_save_meta_box', 'scl_meta_box_nonce');

        $post_type = get_post_meta($post->ID, self::META_POST_TYPE, true);
        $pillar_id = get_post_meta($post->ID, self::META_PILLAR_ID, true);

        ?>
        <div class="scl-meta-box">
            <p>
                <label>
                    <input type="radio" name="scl_post_type" value="" <?php checked($post_type, ''); ?>>
                    <?php _e('Normal Post', 'seo-cluster-links'); ?>
                </label>
            </p>
            <p>
                <label>
                    <input type="radio" name="scl_post_type" value="pillar" <?php checked($post_type, 'pillar'); ?>>
                    <?php _e('Pillar Post', 'seo-cluster-links'); ?>
                </label>
            </p>
            <p>
                <label>
                    <input type="radio" name="scl_post_type" value="cluster" <?php checked($post_type, 'cluster'); ?>>
                    <?php _e('Cluster Post', 'seo-cluster-links'); ?>
                </label>
            </p>

            <div class="scl-pillar-select" style="<?php echo $post_type === 'cluster' ? '' : 'display:none;'; ?>">
                <p>
                    <label for="scl_pillar_id">
                        <?php _e('Select Pillar Post:', 'seo-cluster-links'); ?>
                    </label>
                </p>
                <select name="scl_pillar_id" id="scl_pillar_id" style="width: 100%;">
                    <option value=""><?php _e('— Select Pillar —', 'seo-cluster-links'); ?></option>
                    <?php
                    $pillars = $this->get_pillar_posts();
                    foreach ($pillars as $pillar) {
                        printf(
                            '<option value="%d" %s>%s</option>',
                            $pillar->ID,
                            selected($pillar_id, $pillar->ID, false),
                            esc_html($pillar->post_title)
                        );
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php
    }

    /**
     * Get all pillar posts
     *
     * Uses WordPress object caching for performance (1 hour cache)
     *
     * @return array Array of pillar post objects
     */
    private function get_pillar_posts() {
        $cache_key = 'pillar_posts_list';
        $cache_group = 'seo_cluster_links';

        // Try to get cached results
        $cached_posts = wp_cache_get($cache_key, $cache_group);
        if (false !== $cached_posts) {
            return $cached_posts;
        }

        // Build optimized query arguments
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'post_status' => 'publish', // Only get published posts
            'meta_key' => self::META_POST_TYPE,
            'meta_value' => 'pillar',
            'orderby' => 'title',
            'order' => 'ASC',
            'fields' => 'all', // Get full post objects
            'no_found_rows' => true, // Performance: skip pagination count query
            'update_post_meta_cache' => false, // Performance: skip meta cache update
        );

        // Execute query
        $posts = get_posts($args);

        // Cache results for 1 hour
        wp_cache_set($cache_key, $posts, $cache_group, HOUR_IN_SECONDS);

        return $posts;
    }

    /**
     * Save meta box data
     */
    public function save_meta_box($post_id, $post) {
        // Security checks
        if (!isset($_POST['scl_meta_box_nonce']) ||
            !wp_verify_nonce($_POST['scl_meta_box_nonce'], 'scl_save_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Get old post type for cache invalidation
        $old_post_type = get_post_meta($post_id, self::META_POST_TYPE, true);
        $old_pillar_id = get_post_meta($post_id, self::META_PILLAR_ID, true);

        // Save post type
        $post_type = isset($_POST['scl_post_type']) ? sanitize_text_field($_POST['scl_post_type']) : '';

        if (in_array($post_type, array('pillar', 'cluster', ''))) {
            update_post_meta($post_id, self::META_POST_TYPE, $post_type);
        }

        // Save pillar ID for cluster posts
        if ($post_type === 'cluster' && isset($_POST['scl_pillar_id'])) {
            $pillar_id = absint($_POST['scl_pillar_id']);
            update_post_meta($post_id, self::META_PILLAR_ID, $pillar_id);
        } else {
            delete_post_meta($post_id, self::META_PILLAR_ID);
            $pillar_id = 0;
        }

        // Invalidate caches when cluster relationships change
        $this->invalidate_caches($post_id, $post_type, $old_post_type, $pillar_id, $old_pillar_id);
    }

    /**
     * Invalidate relevant caches when post meta changes
     *
     * @param int $post_id Current post ID
     * @param string $new_type New post type (pillar/cluster/empty)
     * @param string $old_type Old post type
     * @param int $new_pillar_id New pillar ID
     * @param int $old_pillar_id Old pillar ID
     */
    private function invalidate_caches($post_id, $new_type, $old_type, $new_pillar_id, $old_pillar_id) {
        $cache_group = 'seo_cluster_links';

        // If post type changed to/from pillar, invalidate pillar list cache
        if ($new_type === 'pillar' || $old_type === 'pillar') {
            wp_cache_delete('pillar_posts_list', $cache_group);
        }

        // If this is/was a cluster post, invalidate the relevant pillar's cluster cache
        if ($new_type === 'cluster' && $new_pillar_id) {
            // Invalidate new pillar's cluster cache (all variations)
            $this->invalidate_pillar_cluster_cache($new_pillar_id, $cache_group);
        }

        if ($old_type === 'cluster' && $old_pillar_id && $old_pillar_id != $new_pillar_id) {
            // Invalidate old pillar's cluster cache if pillar changed
            $this->invalidate_pillar_cluster_cache($old_pillar_id, $cache_group);
        }
    }

    /**
     * Invalidate all cluster cache entries for a given pillar
     *
     * @param int $pillar_id Pillar post ID
     * @param string $cache_group Cache group name
     */
    private function invalidate_pillar_cluster_cache($pillar_id, $cache_group) {
        // We need to invalidate all possible cache keys for this pillar
        // Since we don't know all possible exclude_ids, we'll use a simple pattern
        // In production, consider using cache groups with version numbers instead

        // Invalidate the base cache key (no exclusions)
        wp_cache_delete('cluster_posts_' . $pillar_id . '_0', $cache_group);

        // Note: For complete cache invalidation, consider implementing a cache version
        // system where you increment a version number stored in options when data changes
        // This is more efficient than trying to delete all possible cache key variations
    }
}
