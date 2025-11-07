<?php
/**
 * Plugin Name: SEO & LLM Optimizer
 * Plugin URI: https://github.com/mikkelkrogsholm/wp-plugins
 * Description: Optimize your WordPress content for both search engines and LLM systems. Generates clean, semantic content suitable for AI training and retrieval.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: Mikkel Krogsholm
 * Text Domain: seo-llm-optimizer
 * Domain Path: /languages
 *
 * @package SEO_LLM_Optimizer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SLO_VERSION', '1.0.0');
define('SLO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SLO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SLO_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Load Composer autoloader if available
if (file_exists(SLO_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once SLO_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * Activation hook
 */
function slo_activate() {
    // Store plugin version
    update_option('slo_version', SLO_VERSION);

    // Set default options
    update_option('slo_chunk_size', 1000);
    update_option('slo_cache_duration', 3600);
    update_option('slo_enable_frontend_button', true);

    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'slo_activate');

/**
 * Deactivation hook
 */
function slo_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'slo_deactivate');

/**
 * Main plugin class
 */
class SEO_LLM_Optimizer {

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
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        // Core processing classes
        require_once SLO_PLUGIN_DIR . 'includes/class-content-processor.php';
        require_once SLO_PLUGIN_DIR . 'includes/class-content-cleaner.php';
        require_once SLO_PLUGIN_DIR . 'includes/class-cache-manager.php';
        require_once SLO_PLUGIN_DIR . 'includes/class-chunking-engine.php';

        // Frontend classes
        require_once SLO_PLUGIN_DIR . 'includes/class-frontend-button.php';
        require_once SLO_PLUGIN_DIR . 'includes/class-modal-handler.php';

        // Admin classes
        require_once SLO_PLUGIN_DIR . 'includes/class-admin-settings.php';
        require_once SLO_PLUGIN_DIR . 'admin/class-meta-boxes.php';

        // REST API
        require_once SLO_PLUGIN_DIR . 'includes/class-rest-api.php';
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'load_textdomain'));

        // Initialize core components
        SLO_Content_Processor::get_instance();
        SLO_Content_Cleaner::get_instance();
        SLO_Cache_Manager::get_instance();
        SLO_Chunking_Engine::get_instance();

        // Initialize frontend components
        SLO_Frontend_Button::get_instance();
        SLO_Modal_Handler::get_instance();

        // Initialize admin components
        SLO_Admin_Settings::get_instance();
        SLO_Meta_Boxes::get_instance();

        // Initialize REST API
        SLO_REST_API::get_instance();
    }

    /**
     * Load plugin textdomain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain('seo-llm-optimizer', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

// Initialize the plugin
SEO_LLM_Optimizer::get_instance();
