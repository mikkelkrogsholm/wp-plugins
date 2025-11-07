<?php
/**
 * Meta Boxes
 *
 * Handles post editor meta boxes for LLM optimization settings.
 *
 * @package SEO_LLM_Optimizer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles meta boxes in post editor
 */
class SLO_Meta_Boxes {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Meta key for LLM optimization status
     */
    const META_OPTIMIZE = '_slo_optimize';

    /**
     * Meta key for custom chunk size
     */
    const META_CHUNK_SIZE = '_slo_chunk_size';

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
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post', array($this, 'save_meta_box'), 10, 2);
    }

    /**
     * Add meta box to post editor
     */
    public function add_meta_box() {
        add_meta_box(
            'slo_optimization_settings',
            __('LLM Optimization Settings', 'seo-llm-optimizer'),
            array($this, 'render_meta_box'),
            'post',
            'side',
            'default'
        );
    }

    /**
     * Render meta box content
     *
     * @param WP_Post $post Current post object
     */
    public function render_meta_box($post) {
        wp_nonce_field('slo_save_meta_box', 'slo_meta_box_nonce');

        $optimize = get_post_meta($post->ID, self::META_OPTIMIZE, true);
        $chunk_size = get_post_meta($post->ID, self::META_CHUNK_SIZE, true);

        ?>
        <div class="slo-meta-box">
            <p>
                <label>
                    <input type="checkbox" name="slo_optimize" value="1" <?php checked($optimize, '1'); ?>>
                    <?php esc_html_e('Enable LLM Optimization', 'seo-llm-optimizer'); ?>
                </label>
            </p>

            <p>
                <label for="slo_chunk_size">
                    <?php esc_html_e('Custom Chunk Size:', 'seo-llm-optimizer'); ?>
                </label>
                <input
                    type="number"
                    name="slo_chunk_size"
                    id="slo_chunk_size"
                    value="<?php echo esc_attr($chunk_size); ?>"
                    placeholder="<?php echo esc_attr(get_option('slo_chunk_size', 1000)); ?>"
                    min="100"
                    max="10000"
                    style="width: 100%;"
                >
                <small class="description">
                    <?php esc_html_e('Leave blank to use default setting', 'seo-llm-optimizer'); ?>
                </small>
            </p>
        </div>
        <?php
    }

    /**
     * Save meta box data
     *
     * @param int $post_id Post ID
     * @param WP_Post $post Post object
     */
    public function save_meta_box($post_id, $post) {
        // Security checks
        if (!isset($_POST['slo_meta_box_nonce']) ||
            !wp_verify_nonce($_POST['slo_meta_box_nonce'], 'slo_save_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save optimization status
        if (isset($_POST['slo_optimize'])) {
            update_post_meta($post_id, self::META_OPTIMIZE, '1');
        } else {
            delete_post_meta($post_id, self::META_OPTIMIZE);
        }

        // Save custom chunk size
        if (isset($_POST['slo_chunk_size']) && !empty($_POST['slo_chunk_size'])) {
            $chunk_size = absint($_POST['slo_chunk_size']);
            if ($chunk_size >= 100 && $chunk_size <= 10000) {
                update_post_meta($post_id, self::META_CHUNK_SIZE, $chunk_size);
            }
        } else {
            delete_post_meta($post_id, self::META_CHUNK_SIZE);
        }

        // Invalidate cache when settings change
        $cache_manager = SLO_Cache_Manager::get_instance();
        $cache_manager->invalidate_post($post_id);
    }
}
