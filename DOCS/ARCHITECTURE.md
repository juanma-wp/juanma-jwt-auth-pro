# Architecture

## Overview

JWT Auth Pro is a WordPress plugin built on modern PHP architecture principles with PSR-4 autoloading and dependency management through Composer. It provides JWT-based authentication for the WordPress REST API with secure refresh token management.

## System Requirements

- WordPress 5.6+
- PHP 7.4+
- HTTPS (recommended for production; localhost HTTP supported for development)

## Core Architecture

### Namespace Structure

All plugin classes use the `JM_JWTAuthPro` namespace:

```
JM_JWTAuthPro\
├── Plugin              # Main orchestrator
├── Auth_Handler        # Authentication logic
├── Cookie_Config       # Cookie management
├── OpenAPI_Spec        # API documentation
└── Admin_Settings      # Admin interface
```

### Component Overview

| Component | Responsibility | File |
|-----------|---------------|------|
| **Plugin** | Plugin initialization, hooks registration, component orchestration | `includes/Plugin.php` |
| **Auth_Handler** | JWT validation, bearer token authentication, refresh token flow | `includes/Auth_Handler.php` |
| **Cookie_Config** | Cookie settings, SameSite/Secure/HttpOnly configuration | `includes/Cookie_Config.php` |
| **OpenAPI_Spec** | REST API schema, endpoint documentation | `includes/OpenAPI_Spec.php` |
| **Admin_Settings** | Settings page, CORS configuration, admin UI | `includes/Admin_Settings.php` |

### Dependency: wp-rest-auth-toolkit

JWT Auth Pro is built on [wp-rest-auth-toolkit](https://github.com/juanma-wp/wp-rest-auth-toolkit), a reusable Composer package that provides:

- **JWT Encoding/Decoding** (`WPRestAuth\AuthToolkit\JWT\Encoder`)
- **Secure Token Generation** (`WPRestAuth\AuthToolkit\Token\Generator`)
- **Refresh Token Management** (`WPRestAuth\AuthToolkit\Token\RefreshTokenManager`)
- **CORS Handling** (`WPRestAuth\AuthToolkit\CORS\Handler`)

This separation allows the core authentication logic to be reused across multiple WordPress plugins.

## Public API

### Helper Functions

WordPress-friendly helper functions provide a stable public API in `includes/helpers.php`:

```php
<?php
// Token operations
wp_auth_jwt_generate_token( $length )
wp_auth_jwt_encode( $payload, $secret )
wp_auth_jwt_decode( $token, $secret )

// Cookie management
wp_auth_jwt_set_cookie( $name, $value, $expires, $options )

// User data formatting
wp_auth_jwt_format_user_data( $user )

// Response helpers
wp_auth_jwt_error_response( $code, $message, $status )
wp_auth_jwt_success_response( $data, $message )
```

### Class API

Direct class usage for advanced integrations:

```php
<?php
use JM_JWTAuthPro\Auth_Handler;
use JM_JWTAuthPro\Cookie_Config;

// Authentication
$auth = new Auth_Handler();
$result = $auth->authenticate_bearer( $token );

// Cookie configuration
$config = new Cookie_Config();
$settings = $config->get_cookie_settings();
```

## Request Flow

### Authentication Flow

1. **Login Request** → `/wp-json/jwt/v1/token`
   - User credentials validated
   - Access token (JWT) generated
   - Refresh token stored in database
   - Refresh token set as HttpOnly cookie
   - Access token returned in response

2. **Authenticated Request** → Any REST endpoint
   - `Authorization: Bearer <token>` header required
   - JWT validated and decoded
   - User authenticated via WordPress `determine_current_user` filter

3. **Token Refresh** → `/wp-json/jwt/v1/refresh`
   - Refresh token read from HttpOnly cookie
   - Validated against database
   - New access token generated
   - New refresh token issued (rotation)
   - Old refresh token invalidated

### CORS Handling

For cross-origin requests:

1. **Preflight (OPTIONS)** → CORS middleware responds
2. **Actual Request** → CORS headers added, credentials allowed
3. **Cookie Transmission** → `SameSite=None; Secure` enables cross-origin cookies

## Data Storage

### Database Tables

**Refresh Tokens**: `wp_jwt_refresh_tokens`
```sql
CREATE TABLE wp_jwt_refresh_tokens (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint(20) unsigned NOT NULL,
  token_hash varchar(255) NOT NULL,
  expires bigint(20) unsigned NOT NULL,
  created bigint(20) unsigned NOT NULL,
  ip_address varchar(100) DEFAULT NULL,
  user_agent varchar(255) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY token_hash (token_hash),
  KEY expires (expires)
);
```

### WordPress Options

- `jwt_auth_settings`: Plugin configuration (CORS origins, cookie settings)
- `jwt_auth_secret_key`: JWT signing secret (auto-generated)

## Extension Points

### Filters

```php
// Customize JWT claims
add_filter( 'jwt_auth_token_claims', function( $claims, $user ) {
    $claims['custom_field'] = 'value';
    return $claims;
}, 10, 2 );

// Modify user response data
add_filter( 'jwt_auth_user_data', function( $data, $user ) {
    $data['custom'] = get_user_meta( $user->ID, 'custom', true );
    return $data;
}, 10, 2 );

// Cookie configuration
add_filter( 'jwt_auth_cookie_samesite', fn() => 'None' );
add_filter( 'jwt_auth_cookie_secure', fn() => false );
```

### Actions

```php
// After successful login
add_action( 'jwt_auth_after_login', function( $user ) {
    // Log login event
}, 10, 1 );

// Before token validation
add_action( 'jwt_auth_before_validate', function( $token ) {
    // Custom logging or analytics
}, 10, 1 );
```

### Class Inheritance

```php
<?php
namespace MyPlugin;

use JM_JWTAuthPro\Auth_Handler;

class Custom_JWT_Auth extends Auth_Handler {
    public function custom_validation( $token ) {
        // Your custom logic
        return parent->authenticate_bearer( $token );
    }
}
```

## Security Considerations

### Token Security

- **Access tokens**: Short-lived JWT (15 minutes default), stateless
- **Refresh tokens**: Long-lived (7 days default), stored hashed in database
- **Token rotation**: New refresh token issued on each refresh
- **Automatic cleanup**: Expired tokens removed via cron job

### Cookie Security

- **HttpOnly**: Prevents JavaScript access (XSS protection)
- **Secure**: HTTPS-only in production
- **SameSite**: `Strict`/`Lax` in production, `None` in development for CORS

### CORS Security

- **Origin validation**: Only configured origins allowed
- **Credentials**: Only sent to allowed origins
- **Preflight caching**: Reduces OPTIONS requests

## Environment Detection

The plugin automatically adjusts behavior based on environment:

| Environment | Detection | Cookie Settings |
|-------------|-----------|-----------------|
| **Development** | `localhost`, `*.local`, `*.test`, or `WP_DEBUG=true` | `SameSite=None; Secure=false` |
| **Staging** | Domains containing "staging", "dev", "test" | `SameSite=None; Secure=true` |
| **Production** | All other domains | `SameSite=Lax; Secure=true` |

## Performance Considerations

- **Stateless authentication**: JWT validation doesn't require database queries
- **Refresh token caching**: Uses WordPress object cache
- **Lazy loading**: Components initialized only when needed
- **Composer autoloading**: Optimized classmap generation

---

**Documentation Metadata**

- **Last Updated**: 2025-12-16
- **Plugin Version**: 2.0.x
- **Compatibility**: WordPress 5.6+
- **Status**: Current
