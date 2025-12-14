# 🟡 MEDIUM PRIORITY: Documentation Has Confusing References

## Severity: **Medium** ⚠️

Documentation contains references that don't match implementation, causing confusion but with workarounds available.

---

## Issue 1: Missing COOKIE_PATH Constant

### Problem
Documentation references `self::COOKIE_PATH` constant which doesn't exist in the actual class.

### Impact
- Developers looking for COOKIE_PATH in code won't find it
- Causes confusion about how cookie paths are configured
- Code examples won't work as shown
- Workaround exists (use `null` for auto-detection)

### Affected Files
- `DOCS/RFC-7009-COMPLIANCE.md` (line 49)
- `DOCS/RFC-9700-COMPLIANCE.md` (line 188)

### Current (Incorrect) Code
```php
// From documentation - CONSTANT DOESN'T EXIST
wp_auth_jwt_delete_cookie( self::REFRESH_COOKIE_NAME, self::COOKIE_PATH );
```

### Expected (Correct) Code
```php
// Option 1: Use auto-detection (recommended)
wp_auth_jwt_delete_cookie( self::REFRESH_COOKIE_NAME );

// Option 2: Be explicit about null
wp_auth_jwt_delete_cookie( self::REFRESH_COOKIE_NAME, null );

// Option 3: Get from config
$config = JuanMa_JWT_Auth_Pro_Cookie_Config::get_config();
wp_auth_jwt_delete_cookie( self::REFRESH_COOKIE_NAME, $config['path'] );
```

### Why This Happened
Looking at `/plugin/juanma-jwt-auth-pro/includes/helpers.php:192`:
```php
function wp_auth_jwt_delete_cookie( string $name, ?string $path = null ): bool {
    return wp_auth_jwt_set_cookie( $name, '', time() - 3600, $path );
}
```

The path parameter is **optional** with a default of `null`. When `null`, the function auto-detects the path via `JuanMa_JWT_Auth_Pro_Cookie_Config::get_config()`.

### Fix Steps
1. Open `/DOCS/RFC-7009-COMPLIANCE.md`
2. Line 49: Remove `, self::COOKIE_PATH` parameter
3. Open `/DOCS/RFC-9700-COMPLIANCE.md`
4. Line 188: Remove `, self::COOKIE_PATH` parameter
5. Optionally add a comment explaining auto-detection:
   ```php
   // Cookie path is auto-detected based on environment
   wp_auth_jwt_delete_cookie( self::REFRESH_COOKIE_NAME );
   ```

---

## Issue 2: Cookie Configuration Class Name Variation

### Problem
Documentation uses simplified class name `JWT_Cookie_Config` but actual class is `JuanMa_JWT_Auth_Pro_Cookie_Config`.

### Impact
- API reference examples won't work
- Developers can't find the class
- Autocomplete won't work with wrong name
- Minor impact: easy to figure out the correct name

### Affected Files
- `DOCS/cookie-configuration.md` (lines 349-398)

### Current (Incorrect) Code
```php
// From documentation - SIMPLIFIED NAME
$config = JWT_Cookie_Config::get_config();
$env = JWT_Cookie_Config::get_environment();
if (JWT_Cookie_Config::is_development()) {
    // ...
}
JWT_Cookie_Config::clear_cache();
```

### Expected (Correct) Code
```php
// Should use full class name
$config = JuanMa_JWT_Auth_Pro_Cookie_Config::get_config();
$env = JuanMa_JWT_Auth_Pro_Cookie_Config::get_environment();
if (JuanMa_JWT_Auth_Pro_Cookie_Config::is_development()) {
    // ...
}
JuanMa_JWT_Auth_Pro_Cookie_Config::clear_cache();
```

### Fix Steps
1. Open `/DOCS/cookie-configuration.md`
2. Find and replace all `JWT_Cookie_Config` → `JuanMa_JWT_Auth_Pro_Cookie_Config`
3. Verify the class actually exists: `grep -r "class JuanMa_JWT_Auth_Pro_Cookie_Config" plugin/`
4. Test the examples in a WordPress environment

### Alternative Consideration
If you want to keep docs cleaner, consider:
1. Creating a class alias in the plugin:
   ```php
   class_alias('JuanMa_JWT_Auth_Pro_Cookie_Config', 'JWT_Cookie_Config');
   ```
2. Or add a note in docs explaining the full name but using simplified for readability

---

## Issue 3: Cookie Constants Need Verification

### Problem
The `cookie-configuration.md` file documents many `JWT_AUTH_COOKIE_*` constants, but it's unclear if these are actually implemented in the current codebase.

### Potentially Unimplemented Constants
```php
JWT_AUTH_COOKIE_ENABLED
JWT_AUTH_COOKIE_NAME
JWT_AUTH_COOKIE_SAMESITE
JWT_AUTH_COOKIE_SECURE
JWT_AUTH_COOKIE_HTTPONLY
JWT_AUTH_COOKIE_PATH
JWT_AUTH_COOKIE_DOMAIN
JWT_AUTH_COOKIE_LIFETIME
JWT_AUTH_COOKIE_AUTO_DETECT
```

### Impact
- If constants aren't implemented, configuration examples won't work
- Developers will define constants that have no effect
- Creates confusion about how to actually configure cookies
- Medium priority because cookie config clearly works via some method

### Investigation Needed
1. Check `/plugin/juanma-jwt-auth-pro/includes/class-jwt-cookie-config.php`
2. Search for how these constants are actually used:
   ```bash
   grep -r "JWT_AUTH_COOKIE_" plugin/
   ```
3. Determine if:
   - These constants are implemented but with different names
   - Cookie config works via different mechanism (admin settings, filters, etc.)
   - Documentation is aspirational/planned features

### Fix Steps
1. Audit the actual cookie configuration implementation
2. If constants exist: Update docs with correct names
3. If constants don't exist: Update docs to show actual configuration method
4. If some exist and some don't: Clearly mark which are available
5. Add a configuration compatibility matrix showing all available methods

---

## Issue 4: Filter Documentation in cookie-configuration.md

### Problem
Similar to HIGH PRIORITY Issue 4, but specifically in the cookie configuration guide. Filter names may use incorrect prefixes.

### Affected Files
- `DOCS/cookie-configuration.md` (various lines)

### Investigation Needed
Search the file for filter references:
```bash
grep -n "jwt_auth_cookie_" DOCS/cookie-configuration.md
```

### Potential Issues
```php
// If documentation shows:
add_filter('jwt_auth_cookie_config', ...);
add_filter('jwt_auth_cookie_samesite', ...);

// Should be (need to verify):
add_filter('juanma_jwt_auth_pro_cookie_config', ...);
add_filter('juanma_jwt_auth_pro_cookie_samesite', ...);
```

### Fix Steps
1. List all filters documented in `cookie-configuration.md`
2. Cross-reference with actual implementation in:
   - `plugin/juanma-jwt-auth-pro/includes/class-jwt-cookie-config.php`
   - `plugin/juanma-jwt-auth-pro/includes/helpers.php`
3. Update filter names to match implementation
4. Verify each filter actually exists and works

---

## Issue 5: DEVELOPMENT.md Filter Names

### Problem
Similar filter name issues may exist in development documentation.

### Affected Files
- `DOCS/DEVELOPMENT.md` (lines 128-136)

### Current Code (lines 128-136)
```php
// Override SameSite for development
add_filter('jwt_auth_cookie_samesite', function() {
    return 'None';
});

// Override Secure flag
add_filter('jwt_auth_cookie_secure', function() {
    return false;
});
```

### Needs Verification
Check if these should be:
```php
add_filter('juanma_jwt_auth_pro_cookie_samesite', ...);
add_filter('juanma_jwt_auth_pro_cookie_secure', ...);
```

### Fix Steps
1. Verify actual filter names in implementation
2. Update DEVELOPMENT.md with correct names
3. Test examples in development environment

---

## Testing & Verification Checklist

After fixes:

- [ ] All class names can be found in codebase: `grep -r "class.*Config" plugin/`
- [ ] All documented constants exist or docs updated to show actual method
- [ ] All filter names match implementation
- [ ] Cookie configuration examples work in test environment
- [ ] Auto-detection behavior is documented clearly
- [ ] Alternative configuration methods are shown

---

## Suggested GitHub Labels

```
documentation
enhancement
medium-priority
needs-investigation
configuration
```

---

## Estimated Fix Time

**3-4 hours** - Includes investigation time to verify actual implementation details before updating docs.

---

## Additional Notes

- These issues suggest the cookie configuration system may have evolved
- Consider adding a "Configuration Quick Reference" table showing all available methods
- May want to deprecate some configuration methods if too many exist
- Document the priority order if multiple configuration methods are available

---

**Created**: 2025-01-14
**Plugin Version**: 1.2.x
**Priority**: 🟡 **Medium - Fix After High Priority Issues**
