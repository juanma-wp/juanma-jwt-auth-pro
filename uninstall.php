<?php
/**
 * Uninstall Script for JuanMa JWT Auth Pro
 *
 * This file is executed when the plugin is deleted through the WordPress admin.
 * It completely removes all plugin data from the database.
 *
 * @package   JM_JWTAuthPro
 * @author    Juan Manuel Garrido
 * @copyright 2025 Juan Manuel Garrido
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Delete all refresh tokens from database table.
$jmjap_table_name = $wpdb->prefix . 'jwt_refresh_tokens';

// Validate table name contains only safe characters (alphanumeric + underscore).
if ( preg_match( '/^[a-zA-Z0-9_]+$/', $jmjap_table_name ) ) {
	// Drop the refresh tokens table.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.NotPrepared
	$wpdb->query( 'DROP TABLE IF EXISTS `' . esc_sql( $jmjap_table_name ) . '`' );
}

// Delete all WordPress options created by the plugin.
delete_option( 'jwt_auth_pro_settings' );
delete_option( 'jwt_auth_pro_general_settings' );
delete_option( 'jwt_auth_cookie_config' );

// Clear any transients.
delete_transient( 'jwt_auth_pro_version' );
delete_transient( 'jwt_auth_pro_deactivation_notice' );

// Clear any user meta that might have been set for JWT tokens.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->delete(
	$wpdb->usermeta,
	array( 'meta_key' => '_jwt_refresh_token_count' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	array( '%s' )
);

// Clear any scheduled cron jobs.
wp_clear_scheduled_hook( 'jwt_auth_pro_clean_expired_tokens' );

// For multisite installations, check if we need to clean up network options.
if ( is_multisite() ) {
	delete_site_option( 'jwt_auth_pro_settings' );
	delete_site_option( 'jwt_auth_pro_general_settings' );
	delete_site_option( 'jwt_auth_cookie_config' );
}

// Note: Constants defined in wp-config.php cannot be removed programmatically.
// Users must manually remove: JMJAP_SECRET, JMJAP_ACCESS_TTL, JMJAP_REFRESH_TTL.
