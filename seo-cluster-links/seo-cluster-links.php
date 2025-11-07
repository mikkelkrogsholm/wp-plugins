<?php
/**
 * Plugin Name: SEO Cluster Links
 * Plugin URI: https://github.com/mikkelkrogsholm/wp-plugins
 * Description: Link pillar posts and cluster posts together automatically for better SEO and user experience
 * Version: 1.0.0
 * Author: Mikkel Krogsholm
 * Text Domain: seo-cluster-links
 * Domain Path: /languages
 *
 * @package SEO_Cluster_Links
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SCL_VERSION', '1.0.0');
define('SCL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SCL_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class SEO_Cluster_Links {

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
        require_once SCL_PLUGIN_DIR . 'includes/class-meta-boxes.php';
        require_once SCL_PLUGIN_DIR . 'includes/class-link-display.php';
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'load_textdomain'));

        // Initialize components
        SCL_Meta_Boxes::get_instance();
        SCL_Link_Display::get_instance();
    }

    /**
     * Load plugin textdomain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain('seo-cluster-links', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

// Initialize the plugin
SEO_Cluster_Links::get_instance();
