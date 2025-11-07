<?php
/**
 * Uninstall script
 *
 * Fired when the plugin is uninstalled.
 *
 * @package SEO_LLM_Optimizer
 */

// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all plugin options
delete_option('slo_version');
delete_option('slo_chunk_size');
delete_option('slo_cache_duration');
delete_option('slo_enable_frontend_button');

// Delete all transients with slo_ prefix
global $wpdb;
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        $wpdb->esc_like('_transient_slo_') . '%',
        $wpdb->esc_like('_transient_timeout_slo_') . '%'
    )
);

// Clear any cached data
wp_cache_flush();
