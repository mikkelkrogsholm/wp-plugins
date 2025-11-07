<?php
/**
 * Modal Handler
 *
 * Manages the frontend modal for displaying optimized content.
 *
 * @package SEO_LLM_Optimizer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles modal operations
 */
class SLO_Modal_Handler {

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
        add_action('wp_footer', array($this, 'render_modal'));
    }

    /**
     * Render modal HTML in footer
     */
    public function render_modal() {
        if (!is_singular('post')) {
            return;
        }

        $template_path = SLO_PLUGIN_DIR . 'templates/frontend/modal.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
    }
}
