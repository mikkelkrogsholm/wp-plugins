<?php
/**
 * Admin Settings
 *
 * Manages plugin settings page in WordPress admin.
 *
 * @package SEO_LLM_Optimizer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles admin settings page
 */
class SLO_Admin_Settings {

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
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_slo_clear_cache', array($this, 'ajax_clear_cache'));
    }

    /**
     * Enqueue admin CSS and JavaScript
     */
    public function enqueue_admin_assets($hook) {
        if ('settings_page_seo-llm-optimizer' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'slo-admin',
            SLO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            SLO_VERSION,
            'all'
        );

        wp_enqueue_script(
            'slo-admin',
            SLO_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            SLO_VERSION,
            array(
                'in_footer' => true,
                'strategy'  => 'defer',
            )
        );

        // Pass data to JavaScript
        wp_localize_script('slo-admin', 'sloAdminData', array(
            'nonce' => wp_create_nonce('slo_admin'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ));
    }

    /**
     * Add settings page to admin menu
     */
    public function add_settings_page() {
        add_options_page(
            __('SEO & LLM Optimizer', 'seo-llm-optimizer'),
            __('LLM Optimizer', 'seo-llm-optimizer'),
            'manage_options',
            'seo-llm-optimizer',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        // Section 1: Feature Toggles
        add_settings_section(
            'slo_features',
            __('Feature Settings', 'seo-llm-optimizer'),
            null,
            'seo-llm-optimizer'
        );

        register_setting('slo_settings', 'slo_enable_frontend_button', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));

        register_setting('slo_settings', 'slo_enabled_post_types', array(
            'type' => 'array',
            'default' => array('post', 'page'),
            'sanitize_callback' => array($this, 'sanitize_post_types'),
        ));

        register_setting('slo_settings', 'slo_button_visibility', array(
            'type' => 'string',
            'default' => 'all',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        add_settings_field(
            'slo_enable_frontend_button',
            __('Enable Frontend Button', 'seo-llm-optimizer'),
            array($this, 'render_checkbox_field'),
            'seo-llm-optimizer',
            'slo_features',
            array(
                'label_for' => 'slo_enable_frontend_button',
                'description' => __('Show "Copy for AI" button on posts/pages', 'seo-llm-optimizer'),
            )
        );

        add_settings_field(
            'slo_enabled_post_types',
            __('Enabled Post Types', 'seo-llm-optimizer'),
            array($this, 'render_multiselect_field'),
            'seo-llm-optimizer',
            'slo_features',
            array(
                'label_for' => 'slo_enabled_post_types',
                'description' => __('Select which post types should have LLM optimization available', 'seo-llm-optimizer'),
            )
        );

        add_settings_field(
            'slo_button_visibility',
            __('Button Visibility', 'seo-llm-optimizer'),
            array($this, 'render_select_field'),
            'seo-llm-optimizer',
            'slo_features',
            array(
                'label_for' => 'slo_button_visibility',
                'description' => __('Control who can see the frontend button', 'seo-llm-optimizer'),
                'options' => array(
                    'all' => __('Everyone', 'seo-llm-optimizer'),
                    'logged_in' => __('Logged-in users only', 'seo-llm-optimizer'),
                    'editors' => __('Editors and above', 'seo-llm-optimizer'),
                ),
            )
        );

        // Section 2: Export Options
        add_settings_section(
            'slo_export',
            __('Export Options', 'seo-llm-optimizer'),
            null,
            'seo-llm-optimizer'
        );

        register_setting('slo_settings', 'slo_include_metadata', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));

        register_setting('slo_settings', 'slo_chunking_strategy', array(
            'type' => 'string',
            'default' => 'hierarchical',
            'sanitize_callback' => array($this, 'sanitize_chunking_strategy'),
        ));

        register_setting('slo_settings', 'slo_chunk_size', array(
            'type' => 'integer',
            'default' => 512,
            'sanitize_callback' => 'absint',
        ));

        register_setting('slo_settings', 'slo_chunk_overlap', array(
            'type' => 'integer',
            'default' => 128,
            'sanitize_callback' => 'absint',
        ));

        add_settings_field(
            'slo_include_metadata',
            __('Include Metadata', 'seo-llm-optimizer'),
            array($this, 'render_checkbox_field'),
            'seo-llm-optimizer',
            'slo_export',
            array(
                'label_for' => 'slo_include_metadata',
                'description' => __('Include post metadata (author, date, categories) in exports', 'seo-llm-optimizer'),
            )
        );

        add_settings_field(
            'slo_chunking_strategy',
            __('Chunking Strategy', 'seo-llm-optimizer'),
            array($this, 'render_select_field'),
            'seo-llm-optimizer',
            'slo_export',
            array(
                'label_for' => 'slo_chunking_strategy',
                'description' => __('Strategy for splitting content into chunks', 'seo-llm-optimizer'),
                'options' => array(
                    'hierarchical' => __('Hierarchical (by headings)', 'seo-llm-optimizer'),
                    'fixed' => __('Fixed size', 'seo-llm-optimizer'),
                    'semantic' => __('Semantic (by paragraphs)', 'seo-llm-optimizer'),
                ),
            )
        );

        add_settings_field(
            'slo_chunk_size',
            __('Chunk Size', 'seo-llm-optimizer'),
            array($this, 'render_number_field'),
            'seo-llm-optimizer',
            'slo_export',
            array(
                'label_for' => 'slo_chunk_size',
                'description' => __('Maximum size of each chunk in tokens (128-2048)', 'seo-llm-optimizer'),
                'min' => 128,
                'max' => 2048,
                'step' => 64,
            )
        );

        add_settings_field(
            'slo_chunk_overlap',
            __('Chunk Overlap', 'seo-llm-optimizer'),
            array($this, 'render_number_field'),
            'seo-llm-optimizer',
            'slo_export',
            array(
                'label_for' => 'slo_chunk_overlap',
                'description' => __('Number of overlapping tokens between chunks (0-512)', 'seo-llm-optimizer'),
                'min' => 0,
                'max' => 512,
                'step' => 32,
            )
        );

        // Section 3: Advanced
        add_settings_section(
            'slo_advanced',
            __('Advanced Settings', 'seo-llm-optimizer'),
            null,
            'seo-llm-optimizer'
        );

        register_setting('slo_settings', 'slo_enable_rest_api', array(
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));

        register_setting('slo_settings', 'slo_rate_limit', array(
            'type' => 'integer',
            'default' => 60,
            'sanitize_callback' => 'absint',
        ));

        register_setting('slo_settings', 'slo_cache_duration', array(
            'type' => 'integer',
            'default' => 3600,
            'sanitize_callback' => 'absint',
        ));

        add_settings_field(
            'slo_enable_rest_api',
            __('Enable REST API', 'seo-llm-optimizer'),
            array($this, 'render_checkbox_field'),
            'seo-llm-optimizer',
            'slo_advanced',
            array(
                'label_for' => 'slo_enable_rest_api',
                'description' => __('Enable REST API endpoints for programmatic access', 'seo-llm-optimizer'),
            )
        );

        add_settings_field(
            'slo_rate_limit',
            __('Rate Limit', 'seo-llm-optimizer'),
            array($this, 'render_number_field'),
            'seo-llm-optimizer',
            'slo_advanced',
            array(
                'label_for' => 'slo_rate_limit',
                'description' => __('Maximum API requests per minute (1-1000)', 'seo-llm-optimizer'),
                'min' => 1,
                'max' => 1000,
                'step' => 1,
            )
        );

        add_settings_field(
            'slo_cache_duration',
            __('Cache Duration', 'seo-llm-optimizer'),
            array($this, 'render_number_field'),
            'seo-llm-optimizer',
            'slo_advanced',
            array(
                'label_for' => 'slo_cache_duration',
                'description' => __('Cache duration in seconds (60-86400)', 'seo-llm-optimizer'),
                'min' => 60,
                'max' => 86400,
                'step' => 60,
            )
        );
    }

    /**
     * Sanitize post types array
     */
    public function sanitize_post_types($value) {
        if (!is_array($value)) {
            return array('post', 'page');
        }

        // Get all public post types
        $public_post_types = get_post_types(array('public' => true), 'names');

        // Filter to only valid public post types
        $sanitized = array_intersect($value, $public_post_types);

        return !empty($sanitized) ? array_values($sanitized) : array('post', 'page');
    }

    /**
     * Sanitize chunking strategy
     */
    public function sanitize_chunking_strategy($value) {
        $valid_strategies = array('hierarchical', 'fixed', 'semantic');
        return in_array($value, $valid_strategies, true) ? $value : 'hierarchical';
    }

    /**
     * Render checkbox field
     */
    public function render_checkbox_field($args) {
        $option = get_option($args['label_for']);
        $checked = checked($option, true, false);

        echo '<input type="checkbox" id="' . esc_attr($args['label_for']) . '" name="' . esc_attr($args['label_for']) . '" value="1" ' . $checked . '>';

        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Render select field
     */
    public function render_select_field($args) {
        $option = get_option($args['label_for']);

        echo '<select id="' . esc_attr($args['label_for']) . '" name="' . esc_attr($args['label_for']) . '">';

        foreach ($args['options'] as $value => $label) {
            $selected = selected($option, $value, false);
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
        }

        echo '</select>';

        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Render number field
     */
    public function render_number_field($args) {
        $option = get_option($args['label_for']);

        echo '<input type="number" id="' . esc_attr($args['label_for']) . '" name="' . esc_attr($args['label_for']) . '" value="' . esc_attr($option) . '" min="' . esc_attr($args['min']) . '" max="' . esc_attr($args['max']) . '" step="' . esc_attr($args['step']) . '" class="regular-text">';

        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Render multiselect field (checkboxes for post types)
     */
    public function render_multiselect_field($args) {
        $option = get_option($args['label_for'], array('post', 'page'));
        $post_types = get_post_types(array('public' => true), 'objects');

        echo '<fieldset>';

        foreach ($post_types as $post_type) {
            $checked = checked(in_array($post_type->name, $option, true), true, false);
            echo '<label style="display: block; margin-bottom: 5px;">';
            echo '<input type="checkbox" name="' . esc_attr($args['label_for']) . '[]" value="' . esc_attr($post_type->name) . '" ' . $checked . '> ';
            echo esc_html($post_type->label);
            echo '</label>';
        }

        echo '</fieldset>';

        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * AJAX handler to clear cache
     */
    public function ajax_clear_cache() {
        check_ajax_referer('slo_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'seo-llm-optimizer')));
        }

        // Clear transients
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options}
                WHERE option_name LIKE %s
                OR option_name LIKE %s",
                $wpdb->esc_like('_transient_slo_') . '%',
                $wpdb->esc_like('_transient_timeout_slo_') . '%'
            )
        );

        // Clear object cache
        wp_cache_flush();

        wp_send_json_success(array('message' => __('Cache cleared successfully', 'seo-llm-optimizer')));
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'seo-llm-optimizer'));
        }

        $template_path = SLO_PLUGIN_DIR . 'templates/admin/settings-page.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
    }
}
