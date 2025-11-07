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
     * Enqueue admin CSS
     */
    public function enqueue_admin_assets($hook) {
        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'scl-admin',
            SCL_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            SCL_VERSION
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

        <script>
        jQuery(document).ready(function($) {
            $('input[name="scl_post_type"]').on('change', function() {
                if ($(this).val() === 'cluster') {
                    $('.scl-pillar-select').slideDown();
                } else {
                    $('.scl-pillar-select').slideUp();
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Get all pillar posts
     */
    private function get_pillar_posts() {
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'meta_key' => self::META_POST_TYPE,
            'meta_value' => 'pillar',
            'orderby' => 'title',
            'order' => 'ASC',
        );

        return get_posts($args);
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
        }
    }
}
