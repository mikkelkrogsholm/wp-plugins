<?php
/**
 * Frontend Button
 *
 * Renders the floating LLM optimization button on frontend posts and handles
 * AJAX requests for markdown generation.
 *
 * @package SEO_LLM_Optimizer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles frontend button rendering and AJAX requests
 */
class SLO_Frontend_Button {

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
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'render_button'));
        add_action('wp_footer', array($this, 'render_modal'));

        // AJAX handlers for logged-in and public users
        add_action('wp_ajax_slo_get_markdown', array($this, 'ajax_get_markdown'));
        add_action('wp_ajax_nopriv_slo_get_markdown', array($this, 'ajax_get_markdown'));
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        // Early return if not on singular post/page
        if (!is_singular()) {
            return;
        }

        // Early return if post type not supported
        $post_type = get_post_type();
        if (!in_array($post_type, array('post', 'page'), true)) {
            return;
        }

        // Check if frontend button is enabled
        if (!get_option('slo_enable_frontend_button', true)) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'slo-frontend',
            SLO_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            SLO_VERSION,
            'all'
        );

        // Enqueue JavaScript with WordPress 6.8+ defer strategy
        wp_enqueue_script(
            'slo-frontend',
            SLO_PLUGIN_URL . 'assets/js/frontend.js',
            array(),
            SLO_VERSION,
            array(
                'in_footer' => true,
                'strategy'  => 'defer'
            )
        );

        // Localize script with data and translations
        wp_localize_script('slo-frontend', 'seoLlmData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('slo_get_content'),
            'postId'  => get_the_ID(),
            'i18n'    => array(
                'copySuccess' => __('Content copied!', 'seo-llm-optimizer'),
                'copyError'   => __('Failed to copy', 'seo-llm-optimizer'),
                'loading'     => __('Loading...', 'seo-llm-optimizer'),
            ),
        ));
    }

    /**
     * Render floating button in footer
     */
    public function render_button() {
        // Check same conditions as enqueue_assets
        if (!is_singular()) {
            return;
        }

        $post_type = get_post_type();
        if (!in_array($post_type, array('post', 'page'), true)) {
            return;
        }

        if (!get_option('slo_enable_frontend_button', true)) {
            return;
        }

        // Check rate limit
        if (!$this->check_rate_limit()) {
            return;
        }

        // Render button HTML
        ?>
        <button type="button"
                id="slo-copy-button"
                class="slo-trigger"
                aria-label="<?php esc_attr_e('Export for AI', 'seo-llm-optimizer'); ?>"
                aria-expanded="false"
                aria-controls="slo-modal">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"/>
                <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"/>
            </svg>
            <span><?php esc_html_e('Copy for AI', 'seo-llm-optimizer'); ?></span>
        </button>
        <?php
    }

    /**
     * Render modal in footer
     */
    public function render_modal() {
        // Check same conditions
        if (!is_singular()) {
            return;
        }

        $post_type = get_post_type();
        if (!in_array($post_type, array('post', 'page'), true)) {
            return;
        }

        if (!get_option('slo_enable_frontend_button', true)) {
            return;
        }

        // Include modal template
        include SLO_PLUGIN_DIR . 'templates/frontend/modal.php';
    }

    /**
     * AJAX handler to get markdown content
     */
    public function ajax_get_markdown() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'slo_get_content')) {
            wp_send_json_error(array(
                'message' => __('Security check failed', 'seo-llm-optimizer'),
            ), 403);
        }

        // Get and validate post ID
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (!$post_id) {
            wp_send_json_error(array(
                'message' => __('Invalid post ID', 'seo-llm-optimizer'),
            ), 400);
        }

        // Verify post exists
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(array(
                'message' => __('Post not found', 'seo-llm-optimizer'),
            ), 404);
        }

        // Check rate limit
        if (!$this->check_rate_limit()) {
            wp_send_json_error(array(
                'message' => __('Rate limit exceeded. Please try again later.', 'seo-llm-optimizer'),
            ), 429);
        }

        // Get options
        $include_metadata = isset($_POST['include_metadata']) && $_POST['include_metadata'] === '1';
        $include_images = isset($_POST['include_images']) && $_POST['include_images'] === '1';

        // Process content through content processor with options
        $processor = SLO_Content_Processor::get_instance();

        // Convert to markdown with options
        $markdown = $processor->convert_to_markdown($post_id, array(
            'include_metadata' => $include_metadata,
            'include_images'   => $include_images,
            'preserve_links'   => true,
        ));

        if (is_wp_error($markdown)) {
            wp_send_json_error(array(
                'message' => $markdown->get_error_message(),
            ), 500);
        }

        // Return success response
        wp_send_json_success(array(
            'markdown' => $markdown,
            'post_id'  => $post_id,
            'title'    => get_the_title($post_id),
        ));
    }


    /**
     * Check rate limit for current user/IP
     *
     * @return bool True if allowed, false if rate limited
     */
    private function check_rate_limit() {
        // Get client IP
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';

        if (empty($ip)) {
            return true; // Allow if we can't determine IP
        }

        // Create transient key
        $transient_key = 'slo_rate_limit_' . md5($ip);

        // Get current count
        $count = get_transient($transient_key);

        if ($count === false) {
            $count = 0;
        }

        // Check if limit exceeded (60 requests per hour)
        if ($count >= 60) {
            return false;
        }

        // Increment and save
        set_transient($transient_key, $count + 1, HOUR_IN_SECONDS);

        return true;
    }
}
