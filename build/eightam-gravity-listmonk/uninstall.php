<?php
/**
 * Uninstall script for Gravity Forms Listmonk Connector.
 *
 * Removes all plugin settings and transients when the plugin is deleted.
 *
 * @package EightAM\GravityFormsListmonk
 */

// Exit if accessed directly or not during uninstall
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Only proceed if user has the uninstall capability
if ( ! current_user_can( 'gravityforms_listmonk_uninstall' ) ) {
	return;
}

/**
 * Remove plugin settings from Gravity Forms.
 */
function eagf_listmonk_uninstall_remove_settings() {
	global $wpdb;

	// Remove plugin settings from Gravity Forms options
	// GF stores add-on settings in wp_options with key pattern: gravityformsaddon_[slug]_settings
	delete_option( 'gravityformsaddon_eightam-gf-listmonk_settings' );
	delete_option( 'gravityformsaddon_eightam-gf-listmonk_version' );

	// Remove any site-wide transients (multisite compatible)
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
			$wpdb->esc_like( '_transient_eagf_listmonk_lists_' ) . '%'
		)
	);

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
			$wpdb->esc_like( '_transient_timeout_eagf_listmonk_lists_' ) . '%'
		)
	);

	// If multisite, clean up site transients as well
	if ( is_multisite() ) {
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s",
				$wpdb->esc_like( '_site_transient_eagf_listmonk_lists_' ) . '%'
			)
		);

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s",
				$wpdb->esc_like( '_site_transient_timeout_eagf_listmonk_lists_' ) . '%'
			)
		);
	}
}

/**
 * Remove feed data for this add-on.
 */
function eagf_listmonk_uninstall_remove_feeds() {
	global $wpdb;

	// Remove all feeds for this add-on
	// Gravity Forms stores feeds in wp_gf_addon_feed table
	$table_name = $wpdb->prefix . 'gf_addon_feed';

	// Check if table exists
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name ) {
		$wpdb->delete(
			$table_name,
			array( 'addon_slug' => 'eightam-gf-listmonk' ),
			array( '%s' )
		);
	}
}

// Execute cleanup
eagf_listmonk_uninstall_remove_settings();
eagf_listmonk_uninstall_remove_feeds();
