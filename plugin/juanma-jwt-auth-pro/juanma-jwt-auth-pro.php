<?php

/**
 * Plugin Name: JuanMa JWT Auth Pro
 * Description: Modern JWT authentication with refresh tokens for WordPress REST API - built for SPAs and mobile apps
 * Version: 1.2.1
 * Author: Juan Manuel Garrido
 * Author URI: https://juanma.codes
 * Plugin URI: https://github.com/juanma-wp/jwt-auth-pro-wp-rest-api
 * Text Domain: juanma-jwt-auth-pro
 * Domain Path: /languages
 * Requires at least: 5.6
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Composer autoloader.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Verify required toolkit is loaded.
if ( ! class_exists( 'WPRestAuth\\AuthToolkit\\JWT\\Encoder' ) ) {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-error"><p>';
			echo '<strong>JuanMa JWT Auth Pro:</strong> Required dependency "wp-rest-auth-toolkit" ';
			echo 'is not loaded. Run <code>composer install</code> in the plugin directory.';
			echo '</p></div>';
		}
	);
	return; // Stop loading plugin.
}

define( 'JMJAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JMJAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'JMJAP_VERSION', '1.2.1' );

// Debug: Add a constant to check if plugin is loaded.
if ( ! defined( 'JMJAP_LOADED' ) ) {
	define( 'JMJAP_LOADED', true );
}

// Initialize the plugin.
new JM_JWTAuthPro\Plugin( __FILE__ );
