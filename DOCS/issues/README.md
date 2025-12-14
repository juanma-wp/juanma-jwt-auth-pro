# Documentation Issues Tracker

This directory contains organized lists of documentation issues found during the codebase audit on 2025-01-14.

## Files

### 🔴 [01-HIGH-PRIORITY.md](./01-HIGH-PRIORITY.md)
**Severity**: Critical - Broken Code Examples

**Issues**:
- Class name mismatches (`Auth_JWT` vs `JuanMa_JWT_Auth_Pro`)
- Constant name mismatches (`JWT_AUTH_PRO_SECRET` vs `JMJAP_SECRET`)
- TTL constant mismatches
- Filter hook name mismatches

**Impact**: Code examples will fail when copied by developers

**Est. Fix Time**: 2-3 hours

---

### 🟡 [02-MEDIUM-PRIORITY.md](./02-MEDIUM-PRIORITY.md)
**Severity**: Medium - Confusing References

**Issues**:
- Missing `COOKIE_PATH` constant references
- Cookie config class name variations
- Cookie constants need verification
- Filter names in configuration docs

**Impact**: Confusion and workarounds needed, but functionality works

**Est. Fix Time**: 3-4 hours (includes investigation)

---

### 🟢 [03-LOW-PRIORITY.md](./03-LOW-PRIORITY.md)
**Severity**: Low - Organization & Cleanup

**Issues**:
- Deprecated function still referenced
- Historical planning documents need archiving
- Duplicate cookie documentation files
- Minimal DOCS/README.md
- Missing version info and update dates

**Impact**: Minor - affects documentation quality and maintenance

**Est. Fix Time**: 2-3 hours

---

## Priority Order

1. ✅ **Start Here**: `01-HIGH-PRIORITY.md` - Fix broken code examples first
2. ⚠️ **Then**: `02-MEDIUM-PRIORITY.md` - Clarify confusing references
3. 📝 **Finally**: `03-LOW-PRIORITY.md` - Organizational improvements

## Total Estimated Time

**7-10 hours** for all documentation fixes and improvements.

## How to Use These Files

Each file is formatted as a GitHub Issue and includes:
- Detailed problem description
- Impact assessment
- Affected files with line numbers
- Current (incorrect) vs Expected (correct) examples
- Step-by-step fix instructions
- Verification checklists
- Suggested GitHub labels

You can:
1. **Copy directly to GitHub Issues** - Each section can be a separate issue
2. **Use as a checklist** - Work through fixes in order
3. **Reference during code reviews** - Ensure no new similar issues are introduced

## Quick Fix Commands

### High Priority Fixes
```bash
# Find all Auth_JWT references
grep -rn "Auth_JWT" DOCS/*.md

# Find all JWT_AUTH_PRO_SECRET references
grep -rn "JWT_AUTH_PRO_SECRET" DOCS/*.md

# Find all wp_auth_jwt_ filter references
grep -rn "apply_filters.*wp_auth_jwt" DOCS/*.md
```

### Verification
```bash
# Verify correct class exists
grep -r "class JuanMa_JWT_Auth_Pro" plugin/

# Verify correct constants exist
grep -r "JMJAP_SECRET\|JMJAP_ACCESS_TTL\|JMJAP_REFRESH_TTL" plugin/

# Verify correct filters exist
grep -r "juanma_jwt_auth_pro_rotate\|juanma_jwt_auth_pro_cookie_samesite" plugin/
```

## Contributing

When fixing these issues:
1. Check off completed items in the verification checklists
2. Test code examples in a WordPress environment
3. Update this tracker with completion status
4. Consider adding automated tests to prevent regression

---

**Created**: 2025-01-14
**Plugin Version**: 1.2.x
**Audit Status**: Complete

For the main plugin documentation, see [../README.md](../README.md)
