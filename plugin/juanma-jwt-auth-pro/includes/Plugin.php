<?php
/**
 * Main Plugin Class
 *
 * @package   JM_JWTAuthPro
 * @author    JuanMa Garrido
 * @copyright 2025 JuanMa Garrido
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

namespace JM_JWTAuthPro;

use WPRestAuth\AuthToolkit\Http\Cors;

/**
 * Main plugin class for JWT Auth Pro.
 *
 * @package JM_JWTAuthPro
 */
class Plugin {

	/**
	 * Auth JWT instance.
	 *
	 * @var Auth_Handler
	 */
	private $auth_jwt;

	/**
	 * OpenAPI Spec instance.
	 *
	 * @var OpenAPI_Spec
	 */
	private $openapi_spec;

	/**
	 * Main plugin file path.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_file Main plugin file path.
	 */
	public function __construct( $plugin_file = '' ) {
		$this->plugin_file = $plugin_file;
		$this->init();

		// Register lifecycle hooks.
		if ( $this->plugin_file ) {
			register_activation_hook( $this->plugin_file, array( $this, 'activate' ) );
			register_deactivation_hook( $this->plugin_file, array( $this, 'deactivate' ) );
		}
	}

	/**
	 * Initialize the plugin.
	 */
	public function init(): void {
		$this->setup_constants();
		$this->init_components();
		$this->init_hooks();
	}

	/**
	 * Setup plugin constants.
	 *
	 * These constants can be defined in wp-config.php for early availability.
	 * Admin panel settings will be used at runtime when actually needed.
	 */
	private function setup_constants(): void {
		// Set default token expiration times if not defined in wp-config.php.
		if ( ! defined( 'JMJAP_ACCESS_TTL' ) ) {
			define( 'JMJAP_ACCESS_TTL', 3600 ); // 1 hour default
		}

		if ( ! defined( 'JMJAP_REFRESH_TTL' ) ) {
			define( 'JMJAP_REFRESH_TTL', 2592000 ); // 30 days default
		}
	}

	/**
	 * Initialize plugin components.
	 */
	private function init_components(): void {
		// Initialize admin settings.
		if ( is_admin() ) {
			new Admin_Settings();
		}

		$this->auth_jwt     = new Auth_Handler();
		$this->openapi_spec = new OpenAPI_Spec();
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks(): void {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_filter( 'rest_authentication_errors', array( $this, 'maybe_auth_bearer' ), 20 );

		// Check if JWT secret is configured and show admin notice if not.
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'check_jwt_secret' ) );
		}

		// Initialize CORS support.
		$this->init_cors();
	}

	/**
	 * Initialize CORS support using Cors from auth-toolkit.
	 *
	 * Uses the clean Cors implementation that properly handles:
	 * - Origin validation
	 * - Preflight OPTIONS requests
	 * - Header management
	 * - Pattern matching for origins
	 */
	private function init_cors(): void {
		// Get CORS settings directly from database (lazy loading).
		// Avoid loading admin settings class during initialization.
		$general_settings = get_option( 'jwt_auth_pro_general_settings', array() );
		$allowed_origins  = $general_settings['cors_allowed_origins'] ?? '';

		// Enable CORS using the toolkit's Cors class.
		// This handles everything: validation, preflight, headers.
		if ( class_exists( Cors::class ) ) {
			Cors::enableForWordPress( $allowed_origins );
		}
	}

	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes(): void {
		$this->auth_jwt->register_routes();
		$this->openapi_spec->register_routes();
	}

	/**
	 * Maybe authenticate with bearer token.
	 *
	 * @param mixed $result The current authentication result.
	 * @return mixed Authentication result.
	 */
	public function maybe_auth_bearer( $result ) {
		if ( ! empty( $result ) ) {
			return $result;
		}

		$auth_header = $this->get_auth_header();
		if ( ! $auth_header || stripos( $auth_header, 'Bearer ' ) !== 0 ) {
			return $result;
		}

		$token = trim( substr( $auth_header, 7 ) );

		// Try JWT authentication.
		$jwt_result = $this->auth_jwt->authenticate_bearer( $token );
		if ( ! is_wp_error( $jwt_result ) ) {
			return $jwt_result;
		}

		return $jwt_result;
	}

	/**
	 * Get the authorization header.
	 *
	 * @return string Authorization header value.
	 */
	private function get_auth_header(): string {
		$auth_header = '';

		if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			$auth_header = sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) );
		} elseif ( isset( $_SERVER['Authorization'] ) ) {
			$auth_header = sanitize_text_field( wp_unslash( $_SERVER['Authorization'] ) );
		} elseif ( function_exists( 'apache_request_headers' ) ) {
			$headers     = apache_request_headers();
			$auth_header = $headers['Authorization'] ?? '';
		}

		return $auth_header;
	}

	/**
	 * Activate the plugin.
	 */
	public function activate(): void {
		$this->create_refresh_tokens_table();
	}

	/**
	 * Deactivate the plugin.
	 *
	 * Following WordPress standards: only clear temporary data on deactivation.
	 * User data and settings are preserved for reactivation.
	 * Complete removal happens via uninstall.php when plugin is deleted.
	 */
	public function deactivate(): void {
		// Clear any scheduled cron jobs for token cleanup.
		wp_clear_scheduled_hook( 'jwt_auth_pro_clean_expired_tokens' );

		// Clear any transients that might have been set.
		delete_transient( 'jwt_auth_pro_version' );

		// Flush rewrite rules to remove any custom endpoints.
		flush_rewrite_rules();
	}

	/**
	 * Create the refresh tokens table.
	 */
	private function create_refresh_tokens_table(): void {
		global $wpdb;

		$table_name = $wpdb->prefix . 'jwt_refresh_tokens';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            token_hash varchar(255) NOT NULL,
            expires_at bigint(20) NOT NULL,
            revoked_at bigint(20) DEFAULT NULL,
            issued_at bigint(20) NOT NULL,
            user_agent varchar(500) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            created_at bigint(20) DEFAULT NULL,
            is_revoked tinyint(1) DEFAULT 0,
            token_type varchar(50) DEFAULT 'jwt',
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY token_hash (token_hash),
            KEY expires_at (expires_at),
            KEY token_type (token_type)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Back-compat public wrapper expected by tests.
	 */
	public function create_jwt_tables(): void {
		$this->create_refresh_tokens_table();
	}

	/**
	 * Check if JWT secret is configured.
	 * Shows admin notice if not configured.
	 */
	public function check_jwt_secret(): void {
		// Check if secret is defined in constant.
		if ( defined( 'JMJAP_SECRET' ) && JMJAP_SECRET !== '' ) {
			return;
		}

		// Check if secret is in admin settings (lazy check without loading admin class).
		$jwt_settings = get_option( 'jwt_auth_pro_settings', array() );
		if ( ! empty( $jwt_settings['secret_key'] ) ) {
			return;
		}

		// No secret found - show admin notice.
		add_action( 'admin_notices', array( $this, 'missing_config_notice' ) );
	}

	/**
	 * Display missing configuration notice.
	 */
	public function missing_config_notice(): void {
		$settings_url = admin_url( 'options-general.php?page=juanma-jwt-auth-pro' );
		echo '<div class="notice notice-error"><p>';
		echo '<strong>JuanMa JWT Auth Pro:</strong> JWT Secret Key is required for the plugin to work. ';
		echo '<a href="' . esc_url( $settings_url ) . '">Configure it in the plugin settings</a> ';
		echo 'or define <code>JMJAP_SECRET</code> in your wp-config.php file.';
		echo '</p></div>';
	}
}
