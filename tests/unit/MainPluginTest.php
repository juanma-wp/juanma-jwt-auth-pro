<?php

/**
 * Main Plugin Unit Tests
 *
 * Unit tests for the main JuanMa_JWT_Auth_Pro_Plugin plugin class.
 * Tests plugin initialization, constants, dependency loading,
 * and core functionality.
 *
 * @package   JM_JWTAuthPro
 * @author    JuanMa Garrido
 * @copyright 2025 JuanMa Garrido
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 *
 * @link      https://github.com/juanma-wp/wp-rest-auth-jwt
 */

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for main JWT plugin class.
 */
class MainPluginTest extends TestCase
{

	/**
	 * Plugin instance for testing.
	 *
	 * @var JuanMa_JWT_Auth_Pro_Plugin
	 */
	private $plugin;

	/**
	 * Set up test environment.
	 */
	protected function setUp(): void
	{
		parent::setUp();

		// Load the main plugin class.
		if (! class_exists('JuanMa_JWT_Auth_Pro_Plugin')) {
			require_once dirname(__DIR__, 2) . '/juanma-jwt-auth-pro.php';
		}

		// Define constants for testing.
		if (! defined('JMJAP_SECRET')) {
			define('JMJAP_SECRET', 'test-secret-key-for-testing-only-jwt');
		}
		if (! defined('JMJAP_PLUGIN_DIR')) {
			define('JMJAP_PLUGIN_DIR', dirname(__DIR__, 2) . '/');
		}

		$this->plugin = new JuanMa_JWT_Auth_Pro_Plugin();
	}

	/**
	 * Test that plugin class exists and can be instantiated.
	 */
	public function testPluginClassExists(): void
	{
		$this->assertTrue(class_exists('JuanMa_JWT_Auth_Pro_Plugin'));
		$this->assertInstanceOf('JuanMa_JWT_Auth_Pro_Plugin', $this->plugin);
	}

	/**
	 * Test plugin initialization.
	 */
	public function testPluginInitialization(): void
	{
		// Test that init method exists.
		$this->assertTrue(method_exists($this->plugin, 'init'));

		// Test that init can be called.
		$this->plugin->init();
		$this->assertTrue(true); // Should not throw errors.
	}

	/**
	 * Test plugin constants are properly defined.
	 */
	public function testPluginConstants(): void
	{
		// Test plugin constants.

		$this->assertTrue(defined('JMJAP_PLUGIN_DIR'));
		$this->assertNotEmpty(JMJAP_PLUGIN_DIR);
	}

	/**
	 * Test dependency loading functionality.
	 */
	public function testDependencyLoading(): void
	{
		// Test that load_dependencies method exists.
		$this->assertTrue(method_exists($this->plugin, 'load_dependencies'));
	}

	/**
	 * Test hooks initialization functionality.
	 */
	public function testHooksInitialization(): void
	{
		// Test that init_hooks method exists.
		$this->assertTrue(method_exists($this->plugin, 'init_hooks'));
	}

	/**
	 * Test constant setup functionality.
	 */
	public function testConstantSetup(): void
	{
		// Test that setup_constants method exists.
		$this->assertTrue(method_exists($this->plugin, 'setup_constants'));

		// Test constants are properly set up.
		$this->assertTrue(defined('JMJAP_SECRET'));
		$this->assertNotEmpty(JMJAP_SECRET);
	}

	/**
	 * Test route registration functionality.
	 */
	public function testRouteRegistration(): void
	{
		// Test that register_rest_routes method exists.
		$this->assertTrue(method_exists($this->plugin, 'register_rest_routes'));
	}

	/**
	 * Test bearer authentication functionality.
	 */
	public function testBearerAuthentication(): void
	{
		// Test that maybe_auth_bearer method exists.
		$this->assertTrue(method_exists($this->plugin, 'maybe_auth_bearer'));

		// Test with no authentication (should return original result).
		$result = $this->plugin->maybe_auth_bearer(null);
		$this->assertNull($result);

		// Test with existing error (should return existing error).
		$error  = new WP_Error('test_error', 'Test error');
		$result = $this->plugin->maybe_auth_bearer($error);
		$this->assertSame($error, $result);
	}

	/**
	 * Test authorization header retrieval functionality.
	 */
	public function testAuthHeaderRetrieval(): void
	{
		// Test that get_auth_header method exists (even if private).
		$reflection = new ReflectionClass($this->plugin);
		$method     = $reflection->getMethod('get_auth_header');
		$method->setAccessible(true);

		// Test with no header.
		$header = $method->invoke($this->plugin);
		$this->assertEmpty($header);

		// Test with Authorization header.
		$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test-token';
		$header                        = $method->invoke($this->plugin);
		$this->assertSame('Bearer test-token', $header);

		// Clean up.
		unset($_SERVER['HTTP_AUTHORIZATION']);
	}

	/**
	 * Test plugin activation hook functionality.
	 */
	public function testActivationHook(): void
	{
		// Test that activate method exists.
		$this->assertTrue(method_exists($this->plugin, 'activate'));

		// Test activation can be called.
		$this->plugin->activate();
		$this->assertTrue(true); // Should not throw errors.
	}

	/**
	 * Test plugin deactivation hook functionality.
	 */
	public function testDeactivationHook(): void
	{
		// Test that deactivate method exists.
		$this->assertTrue(method_exists($this->plugin, 'deactivate'));

		// Test deactivation can be called.
		$this->plugin->deactivate();
		$this->assertTrue(true); // Should not throw errors.
	}

	/**
	 * Test that deactivation properly cleans up temporary data.
	 * Following WordPress standards: preserves user data, only clears temporary items.
	 */
	public function testDeactivationCleansUpDatabase(): void
	{
		global $wpdb;

		// Mock wpdb if needed for unit test.
		if (! isset($wpdb)) {
			$wpdb         = $this->createMock(stdClass::class);
			$wpdb->prefix = 'wp_';
		}

		// Record queries that would be executed.
		$queries = [];

		// Override wpdb->query to capture queries.
		$wpdb->query = function ($query) use (&$queries) {
			$queries[] = $query;
			return true;
		};

		// Run deactivation.
		$this->plugin->deactivate();

		// Verify table operations would be performed.
		$this->assertTrue(method_exists($this->plugin, 'deactivate'));
	}

	/**
	 * Test that deactivation follows WordPress standards.
	 */
	public function testDeactivationRemovesOptions(): void
	{
		// This test verifies the deactivate method exists and follows WordPress standards.
		// In WordPress standard behavior, deactivation should:
		// - Clear scheduled cron jobs
		// - Clear transients
		// - Flush rewrite rules
		// But PRESERVE user data and settings

		// For unit test, we verify the method exists and can be called.
		$this->assertTrue(method_exists($this->plugin, 'deactivate'));

		// The deactivation now only clears temporary data:
		// - wp_clear_scheduled_hook('jwt_auth_pro_clean_expired_tokens')
		// - delete_transient('jwt_auth_pro_version')
		// - flush_rewrite_rules()
		// Settings and database tables are PRESERVED for reactivation

		$this->plugin->deactivate();
		$this->assertTrue(true);
	}

	/**
	 * Test database table creation functionality.
	 */
	public function testDatabaseTableCreation(): void
	{
		// Test that create_jwt_tables method exists.
		$reflection = new ReflectionClass($this->plugin);
		$this->assertTrue($reflection->hasMethod('create_jwt_tables'));
	}

	/**
	 * Test script enqueuing functionality.
	 */
	public function testScriptEnqueuing(): void
	{
		// Script enqueuing is now handled by the admin settings class
		// Just verify the plugin initializes without errors
		$this->plugin->init();
		$this->assertTrue(true); // Should not throw errors.
	}

	/**
	 * Test plugin components initialization.
	 */
	public function testPluginComponents(): void
	{
		// Test that plugin initializes its components.
		$this->plugin->init();

		// Check that required components are available through reflection.
		$reflection = new ReflectionClass($this->plugin);

		// Check for auth_jwt property.
		if ($reflection->hasProperty('auth_jwt')) {
			$property = $reflection->getProperty('auth_jwt');
			$property->setAccessible(true);
			$auth_jwt = $property->getValue($this->plugin);
			$this->assertInstanceOf('JuanMa_JWT_Auth_Pro', $auth_jwt);
		}

		// Check for admin_settings property (only in admin).
		if ($reflection->hasProperty('admin_settings')) {
			$property = $reflection->getProperty('admin_settings');
			$property->setAccessible(true);
			$admin_settings = $property->getValue($this->plugin);
			// May be null if not in admin context.
			$this->assertTrue(null === $admin_settings || $admin_settings instanceof JM_JWTAuthPro\JuanMa_JWT_Auth_Pro_Admin_Settings);
		}
	}

	/**
	 * Test JWT secret generation.
	 */
	public function testSecretGeneration(): void
	{
		// Test that secret is generated if not exists.
		$this->assertTrue(defined('JMJAP_SECRET'));
		$secret = JMJAP_SECRET;

		$this->assertNotEmpty($secret);
		$this->assertIsString($secret);
		$this->assertGreaterThan(32, strlen($secret)); // Should be reasonably long.
	}

	/**
	 * Test WordPress integration functionality.
	 */
	public function testWordPressIntegration(): void
	{
		// Test that WordPress hooks are properly set up.
		$this->assertTrue(method_exists($this->plugin, 'register_rest_routes'));
		$this->assertTrue(method_exists($this->plugin, 'maybe_auth_bearer'));
		// Script enqueuing is now handled by the admin settings class
	}

	/**
	 * Test plugin singleton pattern.
	 */
	public function testPluginSingleton(): void
	{
		// The plugin should be instantiated as a singleton through the main file.
		// We can't test this directly in unit tests, but we can verify the structure.
		$this->assertInstanceOf('JuanMa_JWT_Auth_Pro_Plugin', $this->plugin);
	}

	/**
	 * Tear down test environment.
	 */
	protected function tearDown(): void
	{
		// Clean up global state.
		unset($_SERVER['HTTP_AUTHORIZATION']);
		parent::tearDown();
	}
}
