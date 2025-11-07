<?php
/**
 * Uninstall handler for SEO Cluster Links plugin
 *
 * Fired when the plugin is uninstalled.
 *
 * @package SEO_Cluster_Links
 * @since 1.0.0
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean up post meta data
 */
function scl_delete_post_meta() {
	global $wpdb;

	// Delete all post meta for post type classification
	$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_scl_post_type'" );

	// Delete all post meta for pillar ID associations
	$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_scl_pillar_id'" );
}

/**
 * Clean up plugin options
 */
function scl_delete_options() {
	// Delete plugin version option
	delete_option( 'scl_version' );

	// Delete any other plugin options if they exist
	delete_option( 'scl_settings' );
}

/**
 * Run uninstall procedures
 */
scl_delete_post_meta();
scl_delete_options();

// Clear any cached data
wp_cache_flush();
