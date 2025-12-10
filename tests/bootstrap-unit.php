<?php

/**
 * PHPUnit Bootstrap for Unit Tests
 *
 * This bootstrap file is designed for testing isolated PHP functions without WordPress
 * dependencies. It loads only the minimum required components to test core JWT helper
 * functions and other standalone utilities.
 *
 * Unit tests using this bootstrap should focus on testing individual functions and
 * methods without relying on WordPress core functionality, database connections,
 * or complex integrations.
 *
 * @package   JM_JWTAuthPro
 * @author    JuanMa Garrido
 * @copyright 2025 JuanMa Garrido
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 *
 * @link      https://github.com/juanma-wp/jwt-auth-pro-wp-rest-api
 */

// Load Composer autoloaders - different paths for wp-env vs local
if ( file_exists( dirname( __DIR__ ) . '/vendor-dev/autoload.php' ) ) {
	// In wp-env, dev dependencies are mapped to vendor-dev
	require_once dirname( __DIR__ ) . '/vendor-dev/autoload.php';
} elseif ( file_exists( dirname( __DIR__ ) . '/vendor/autoload.php' ) ) {
	// Local testing with root vendor
	require_once dirname( __DIR__ ) . '/vendor/autoload.php';
} elseif ( file_exists( dirname( __DIR__ ) . '/plugin/juanma-jwt-auth-pro/vendor/autoload.php' ) ) {
	// Local testing with plugin vendor only
	require_once dirname( __DIR__ ) . '/plugin/juanma-jwt-auth-pro/vendor/autoload.php';
}

// Define minimal constants needed for helpers.php
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/' );
}

if ( ! defined( 'JMJAP_SECRET' ) ) {
	define( 'JMJAP_SECRET', 'test-secret-for-unit-testing' );
}

if ( ! defined( 'JMJAP_ACCESS_TTL' ) ) {
	define( 'JMJAP_ACCESS_TTL', 3600 );
}

if ( ! defined( 'JMJAP_REFRESH_TTL' ) ) {
	define( 'JMJAP_REFRESH_TTL', 2592000 );
}

// Mock only essential WordPress functions needed by helpers.php
if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth );
	}
}

// Load only the helpers.php file for basic function testing
if ( file_exists( dirname( __DIR__ ) . '/includes/helpers.php' ) ) {
	// In wp-env, mounted directly
	require_once dirname( __DIR__ ) . '/includes/helpers.php';
} else {
	// Local testing outside wp-env
	require_once dirname( __DIR__ ) . '/plugin/juanma-jwt-auth-pro/includes/helpers.php';
}

echo "JWT Auth Pro WP REST API Unit Test environment loaded successfully!\n";
echo 'PHP version: ' . PHP_VERSION . "\n\n";
