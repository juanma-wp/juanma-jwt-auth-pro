# 🔴 HIGH PRIORITY: Documentation Code Examples Are Broken

## Severity: **Critical** 🚨

Code examples in RFC compliance documentation will fail when copied and executed by developers.

---

## Issue 1: Class Name Mismatch

### Problem
Documentation uses `Auth_JWT` class name, but the actual implementation uses `JuanMa_JWT_Auth_Pro`.

### Impact
- Developers copying code examples will get fatal errors: `Class 'Auth_JWT' not found`
- Breaks trust in documentation accuracy
- Wastes developer time debugging

### Affected Files
- `DOCS/RFC-7009-COMPLIANCE.md` (line 287)
- `DOCS/RFC-9700-COMPLIANCE.md` (line 464)

### Current (Incorrect) Code
```php
// From documentation - WON'T WORK
$auth_jwt = new Auth_JWT();
$tokens = $auth_jwt->get_user_refresh_tokens( $user_id );
```

### Expected (Correct) Code
```php
// Should be
$auth_jwt = new JuanMa_JWT_Auth_Pro();
$tokens = $auth_jwt->get_user_refresh_tokens( $user_id );
```

### Fix Steps
1. Open `/DOCS/RFC-7009-COMPLIANCE.md`
2. Line 287: Replace `new Auth_JWT()` with `new JuanMa_JWT_Auth_Pro()`
3. Open `/DOCS/RFC-9700-COMPLIANCE.md`
4. Line 464: Replace `new Auth_JWT()` with `new JuanMa_JWT_Auth_Pro()`
5. Search both files for any additional `Auth_JWT` references
6. Verify all replacements are correct

---

## Issue 2: JWT Secret Constant Name Mismatch

### Problem
Documentation uses `JWT_AUTH_PRO_SECRET` but actual constant is `JMJAP_SECRET`.

### Impact
- Code examples produce undefined constant errors
- Security-critical configuration examples fail
- Developers may create wrong constants, breaking authentication

### Affected Files
- `DOCS/RFC-7009-COMPLIANCE.md` (lines 87, 157)
- `DOCS/RFC-9700-COMPLIANCE.md` (lines 47, 86, 157, 247)

### Current (Incorrect) Code
```php
// From documentation - WRONG CONSTANT
$token_hash = wp_auth_jwt_hash_token( $refresh_token, JWT_AUTH_PRO_SECRET );
return wp_auth_jwt_encode( $claims, JWT_AUTH_PRO_SECRET );
```

### Expected (Correct) Code
```php
// Should be
$token_hash = wp_auth_jwt_hash_token( $refresh_token, JMJAP_SECRET );
return wp_auth_jwt_encode( $claims, JMJAP_SECRET );
```

### Fix Steps
1. Open `/DOCS/RFC-7009-COMPLIANCE.md`
2. Find and replace all `JWT_AUTH_PRO_SECRET` → `JMJAP_SECRET`
3. Open `/DOCS/RFC-9700-COMPLIANCE.md`
4. Find and replace all `JWT_AUTH_PRO_SECRET` → `JMJAP_SECRET`
5. Verify with grep: `grep -n "JWT_AUTH_PRO_SECRET" DOCS/*.md` (should return nothing)

---

## Issue 3: TTL Constant Names Mismatch

### Problem
Documentation uses `JWT_AUTH_ACCESS_TTL` and `JWT_AUTH_REFRESH_TTL` but actual constants use `JMJAP_` prefix.

### Impact
- Configuration examples use wrong constant names
- Token expiration settings won't work as documented
- Default values may not apply correctly

### Affected Files
- `DOCS/RFC-9700-COMPLIANCE.md` (lines 78, 93, 95, 315)

### Current (Incorrect) Code
```php
// From documentation - WRONG CONSTANTS
'exp' => $now + JWT_AUTH_ACCESS_TTL,
define('JWT_AUTH_ACCESS_TTL', 3600);
define('JWT_AUTH_REFRESH_TTL', 2592000);
```

### Expected (Correct) Code
```php
// Should be
'exp' => $now + JMJAP_ACCESS_TTL,
define('JMJAP_ACCESS_TTL', 3600);
define('JMJAP_REFRESH_TTL', 2592000);
```

### Fix Steps
1. Open `/DOCS/RFC-9700-COMPLIANCE.md`
2. Find and replace all `JWT_AUTH_ACCESS_TTL` → `JMJAP_ACCESS_TTL`
3. Find and replace all `JWT_AUTH_REFRESH_TTL` → `JMJAP_REFRESH_TTL`
4. Check actual implementation in `plugin/juanma-jwt-auth-pro/juanma-jwt-auth-pro.php` to confirm
5. Verify: `grep -n "JWT_AUTH.*TTL" DOCS/*.md` (should only show correct `JMJAP_` prefix)

---

## Issue 4: Filter Hook Name Mismatches

### Problem
Documentation uses `wp_auth_jwt_` prefix for filters, but actual filters use `juanma_jwt_auth_pro_` prefix.

### Impact
- Developers' filter callbacks won't be called
- Customization examples fail silently
- No errors, just unexpected behavior

### Affected Files
- `DOCS/RFC-9700-COMPLIANCE.md` (lines 21, 119)

### Current (Incorrect) Code
```php
// From documentation - WRONG FILTER NAMES
if ( apply_filters( 'wp_auth_jwt_rotate_refresh_token', true ) ) {
    // ...
}

$samesite = apply_filters( 'wp_auth_jwt_cookie_samesite', 'Strict' );
```

### Expected (Correct) Code
```php
// Should be
if ( apply_filters( 'juanma_jwt_auth_pro_rotate_refresh_token', true ) ) {
    // ...
}

$samesite = apply_filters( 'juanma_jwt_auth_pro_cookie_samesite', $config['samesite'] );
```

### Fix Steps
1. Open `/DOCS/RFC-9700-COMPLIANCE.md`
2. Find and replace `wp_auth_jwt_rotate_refresh_token` → `juanma_jwt_auth_pro_rotate_refresh_token`
3. Find and replace `wp_auth_jwt_cookie_samesite` → `juanma_jwt_auth_pro_cookie_samesite`
4. Search for any other `wp_auth_jwt_` filter references that should be `juanma_jwt_auth_pro_`
5. Cross-reference with actual code in `helpers.php` (line 150, 249)
6. Verify: `grep -n "apply_filters.*wp_auth_jwt" DOCS/*.md` (should return nothing)

---

## Testing & Verification Checklist

After fixing all issues above:

- [ ] Extract all PHP code blocks from RFC documentation
- [ ] Run syntax check: `php -l` on each code block
- [ ] Verify all class names exist: `grep -r "class JuanMa_JWT_Auth_Pro" plugin/`
- [ ] Verify all constants exist: `grep -r "JMJAP_SECRET\|JMJAP_ACCESS_TTL\|JMJAP_REFRESH_TTL" plugin/`
- [ ] Verify all filter names: `grep -r "juanma_jwt_auth_pro_rotate\|juanma_jwt_auth_pro_cookie_samesite" plugin/`
- [ ] Test one complete code example end-to-end in a WordPress install
- [ ] No PHP errors or warnings when running examples
- [ ] Documentation matches actual plugin implementation

---

## Suggested GitHub Labels

```
documentation
bug
high-priority
breaking-examples
needs-review
```

---

## Estimated Fix Time

**2-3 hours** for careful find/replace and thorough verification across both RFC documents.

---

## Additional Notes

- These are not aspirational features - the code exists, just with different names
- This suggests docs may have been written for an earlier version or prototype
- After fixing, consider adding automated tests to verify documentation code examples
- May want to add a note about the class rename if `Auth_JWT` was the old name

---

**Created**: 2025-01-14
**Plugin Version**: 1.2.x
**Priority**: 🔴 **Critical - Fix First**
