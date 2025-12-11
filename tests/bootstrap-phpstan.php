<?php
/**
 * Minimal PHPStan Bootstrap - Memory Optimized
 *
 * This ultra-minimal bootstrap file is specifically designed for PHPStan static analysis
 * to reduce memory consumption. It only defines the absolute minimum constants and
 * functions needed for analysis without loading unnecessary WordPress stubs or
 * external dependencies.
 *
 * @package   JM_JWTAuthPro
 * @author    JuanMa Garrido
 * @copyright 2025 JuanMa Garrido
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

// Load root Composer autoloader
$composer_autoloader = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $composer_autoloader ) ) {
	require_once $composer_autoloader;
}

// Load plugin Composer autoloader to find wp-rest-auth-toolkit classes
$plugin_autoloader = dirname( __DIR__ ) . '/plugin/juanma-jwt-auth-pro/vendor/autoload.php';
if ( file_exists( $plugin_autoloader ) ) {
	require_once $plugin_autoloader;
}

// Only define essential constants that PHPStan actually needs
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/' );
}

// JWT constants that are referenced in code
if ( ! defined( 'JMJAP_SECRET' ) ) {
	define( 'JMJAP_SECRET', 'phpstan-analysis-secret' );
}

if ( ! defined( 'JMJAP_ACCESS_TTL' ) ) {
	define( 'JMJAP_ACCESS_TTL', 3600 );
}

if ( ! defined( 'JMJAP_REFRESH_TTL' ) ) {
	define( 'JMJAP_REFRESH_TTL', 2592000 );
}

// WordPress version constant
if ( ! defined( 'JMJAP_VERSION' ) ) {
	define( 'JMJAP_VERSION', '1.0.0' );
}

echo "Minimal PHPStan bootstrap loaded (memory optimized)\n";
echo 'PHP version: ' . PHP_VERSION . "\n\n";
