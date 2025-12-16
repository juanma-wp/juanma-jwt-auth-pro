# JWT Auth Pro Plugin Restructuring Plan

## Executive Summary

Based on best practices from experienced WordPress plugin developers (Birgit Pauli-Haack, Jon Surrell, Jonathan Bossenger), this plan restructures the JWT Auth Pro plugin repository to:

1. **Separate plugin code from development tooling**
2. **Mirror WordPress.org SVN structure**
3. **Streamline the release process**
4. **Maintain clear boundaries between distributed and non-distributed code**

## Current Structure Issues

The current structure has everything at the root level:
- Plugin PHP files mixed with development configuration
- No clear separation between distributable and development files
- Potential for accidentally shipping development files to WordPress.org
- **Directory name dependency**: Currently, the plugin expects the directory to be named exactly `juanma-jwt-auth-pro` to match the main file

## Proposed New Structure

```
juanma-jwt-auth-pro/
├── plugin/                         # ← DISTRIBUTABLE PLUGIN CODE
│   └── juanma-jwt-auth-pro/        # ← Named directory (solves mounting issue)
│       ├── juanma-jwt-auth-pro.php # Main plugin file
│       ├── includes/               # Plugin PHP classes
│       │   ├── class-auth-jwt.php
│       │   ├── class-openapi-spec.php
│       │   ├── class-jwt-auth-pro-admin-settings.php
│       │   ├── class-jwt-cookie-config.php
│       │   └── helpers.php
│       ├── languages/              # Translation files
│       ├── uninstall.php
│       └── readme.txt              # WordPress.org readme
│
├── assets/                         # WordPress.org assets (separate from plugin)
│   ├── banner-772x250.png
│   ├── banner-1544x500.png
│   ├── icon-128x128.png
│   ├── icon-256x256.png
│   └── screenshot-*.png
│
├── svn-checkout/                   # LOCAL SVN (gitignored)
│   ├── trunk/
│   ├── tags/
│   └── assets/
│
├── scripts/                        # Deployment & build scripts
│   ├── deploy-to-svn.sh
│   ├── prepare-release.sh
│   └── sync-to-trunk.sh
│
├── tests/                          # Test suites (not distributed)
│   ├── unit/
│   ├── integration/
│   ├── behat/
│   └── bootstrap-*.php
│
├── docs/                           # Documentation (not distributed)
│   ├── DEVELOPMENT.md
│   ├── advanced-usage.md
│   ├── cookie-configuration.md
│   └── ...
│
├── .github/                        # GitHub workflows
│   └── workflows/
│
├── composer.json                   # Dev dependencies
├── package.json                    # Build tools
├── phpunit.xml                     # Test config
├── phpstan.neon                    # Static analysis
├── .wp-env.json                    # Local dev environment
├── .gitignore                      # Ignore svn-checkout/ and build artifacts
├── .distignore                     # What NOT to include in distributions
├── CHANGELOG.md                    # For GitHub
└── README.md                       # For GitHub (developer-focused)
```

## Why `plugin/juanma-jwt-auth-pro/` Structure?

This nested structure solves a critical WordPress issue that Jon Surrell highlighted:

> "There's some naming expectation where the plugin entry PHP file aligns with the directory name, this lets me ensure a match when I mount it locally and my checked out repo directory name doesn't matter."

### The Problem
WordPress expects:
- Plugin directory: `juanma-jwt-auth-pro`
- Main plugin file: `juanma-jwt-auth-pro.php`

If someone clones your repo as:
- `jwt-auth-main` (from GitHub default branch download)
- `my-jwt-plugin` (custom local naming)
- `juanma-jwt-auth-pro-develop` (branch naming)

The plugin might break or behave unexpectedly.

### The Solution
By having `plugin/juanma-jwt-auth-pro/`, you guarantee:
1. **The plugin directory name is ALWAYS correct** regardless of repo folder name
2. **Local mounting just works**: `ln -s /any/repo/name/plugin/juanma-jwt-auth-pro /wp-content/plugins/`
3. **WordPress.org deployment is clean**: `rsync plugin/juanma-jwt-auth-pro/ svn/trunk/`
4. **No activation issues** when users install from different sources

## Key Benefits of This Structure

### 1. Clear Separation of Concerns
- **`plugin/juanma-jwt-auth-pro/`** contains ONLY what ships to users
- Root level contains development tools and configs
- No risk of shipping `node_modules`, `.git`, or test files

### 2. WordPress.org Compatibility
- Directory name (`juanma-jwt-auth-pro`) always matches main file
- `assets/` folder mirrors WordPress.org structure
- Easy rsync from `plugin/juanma-jwt-auth-pro/` to `svn/trunk/`

### 3. Simplified Release Process
```bash
# Simple deployment becomes:
rsync -av --delete plugin/juanma-jwt-auth-pro/ svn-checkout/trunk/
rsync -av --delete assets/ svn-checkout/assets/
cd svn-checkout
svn add --force trunk/ assets/
svn commit -m "Update to version X.Y.Z"
svn cp trunk tags/X.Y.Z
svn commit -m "Tag version X.Y.Z"
```

### 4. Development Flexibility
```bash
# Works regardless of how you name your local repo:
git clone git@github.com:you/repo.git my-custom-name
cd my-custom-name
# Plugin still mounts correctly:
ln -s $(pwd)/plugin/juanma-jwt-auth-pro /path/to/wp/plugins/
```

## Migration Steps

### Phase 1: Create New Structure (Non-Breaking)
1. Create `plugin/juanma-jwt-auth-pro/` directory structure
2. Copy (don't move yet) plugin files to new location:
   ```bash
   mkdir -p plugin/juanma-jwt-auth-pro
   cp juanma-jwt-auth-pro.php plugin/juanma-jwt-auth-pro/
   cp -r includes plugin/juanma-jwt-auth-pro/
   cp -r languages plugin/juanma-jwt-auth-pro/
   cp uninstall.php plugin/juanma-jwt-auth-pro/
   ```
3. Keep originals at root temporarily for backward compatibility

### Phase 2: Setup Development Workflow
1. Update `.wp-env.json`:
   ```json
   {
     "plugins": [
       "./plugin/juanma-jwt-auth-pro"
     ]
   }
   ```
2. Create `scripts/` directory with deployment scripts
3. Add `svn-checkout/` to `.gitignore`
4. Update build processes to output to `plugin/juanma-jwt-auth-pro/`

### Phase 3: Implement SVN Deployment
1. Checkout WordPress.org SVN repo to `svn-checkout/`
2. Create deployment script based on Birgit's approach
3. Test deployment to trunk (without committing)
4. Document the release process

### Phase 4: Clean Up (Breaking Change)
1. Remove root-level plugin files
2. Update all documentation
3. Update CI/CD pipelines
4. Tag new version

## Deployment Script Example

```bash
#!/bin/bash
# scripts/deploy-to-svn.sh

PLUGIN_DIR="plugin/juanma-jwt-auth-pro"
SVN_DIR="svn-checkout"
ASSETS_DIR="assets"

# Ensure plugin directory exists and has correct name
if [ ! -d "$PLUGIN_DIR" ]; then
    echo "Error: Plugin directory not found at $PLUGIN_DIR"
    exit 1
fi

# Sync plugin files to trunk (contents, not the directory itself)
rsync -av --delete \
  --exclude='.DS_Store' \
  --exclude='*.map' \
  "$PLUGIN_DIR/" "$SVN_DIR/trunk/"

# Sync assets
rsync -av --delete \
  --exclude='.DS_Store' \
  "$ASSETS_DIR/" "$SVN_DIR/assets/"

cd "$SVN_DIR"

# Add new files, remove deleted files
svn status | grep '^?' | awk '{print $2}' | xargs -r svn add
svn status | grep '^!' | awk '{print $2}' | xargs -r svn delete

# Show what will be committed
svn status

echo "Ready to commit? (y/n)"
read -r response
if [[ "$response" == "y" ]]; then
  svn commit -m "Update plugin to latest version"
fi
```

## Testing Strategy

### Local Testing with New Structure
```bash
# Method 1: Symlink (preserves directory name)
ln -s /path/to/repo/plugin/juanma-jwt-auth-pro /path/to/wordpress/wp-content/plugins/

# Method 2: wp-env (automatic)
# .wp-env.json
{
  "plugins": [
    "./plugin/juanma-jwt-auth-pro"
  ]
}

# Method 3: Direct mount in Docker/Studio
# The plugin directory name is always correct!
```

### Pre-Release Checklist
- [ ] All tests pass
- [ ] Plugin activates correctly from `plugin/juanma-jwt-auth-pro/`
- [ ] No development files in `plugin/juanma-jwt-auth-pro/`
- [ ] Directory name matches main file name exactly
- [ ] `readme.txt` validates on WordPress.org validator
- [ ] Assets display correctly
- [ ] Version numbers synchronized across files

## Alternative Considerations

### Option A: Flat Plugin Directory
```
plugin/
├── juanma-jwt-auth-pro.php
├── includes/
└── ...
```
**Pros**: Simpler structure
**Cons**: Requires repo to be named correctly, causes activation issues

### Option B: Nested Named Directory (Recommended)
```
plugin/
└── juanma-jwt-auth-pro/
    ├── juanma-jwt-auth-pro.php
    └── ...
```
**Pros**: Always works, regardless of repo name
**Cons**: One extra directory level

### GitHub Actions Deployment
While manual deployment with scripts provides control and transparency, we could later implement GitHub Actions for:
- Automated testing on PR
- Building release artifacts
- Deploying to WordPress.org on tag creation

## Composer Dependency Handling

Since the plugin depends on `wp-rest-auth-toolkit`:

### For Development
```json
{
  "require-dev": {
    "juanma-wp/wp-rest-auth-toolkit": "dev-main"
  }
}
```

### For Distribution
Two options:
1. **Scoped inclusion**: Use PHP-Scoper to include toolkit with namespaced to avoid conflicts
2. **Separate requirement**: Document as a dependency, let users manage

## Next Steps

1. **Review and approve this plan**
2. **Create feature branch** for restructuring
3. **Implement Phase 1** (non-breaking structure)
4. **Test plugin activation** from new location
5. **Verify directory naming** works correctly
6. **Document the new workflow**
7. **Execute migration**

## Real-World Testing Commands

```bash
# Test 1: Clone with different name
git clone <repo> test-different-name
cd test-different-name
ln -s $(pwd)/plugin/juanma-jwt-auth-pro ~/Local\ Sites/test/app/public/wp-content/plugins/
# Should activate without issues

# Test 2: Download from GitHub as ZIP
# Download main branch as ZIP (becomes juanma-jwt-auth-pro-main/)
# Extract and symlink plugin/juanma-jwt-auth-pro
# Should still work!

# Test 3: Deploy to SVN
./scripts/sync-to-trunk.sh
# Check that trunk/ contains correct structure
```

## Notes from Community Best Practices

### From Birgit Pauli-Haack
- Simple scripts reduce human error
- Smoke testing before SVN commit is crucial
- Keep it "pedestrian" when you don't release often

### From Jon Surrell
- **Subdirectory prevents naming issues** ← KEY INSIGHT
- Clear separation makes it obvious what ships
- Local SVN checkout should be gitignored
- The named subdirectory ensures plugin always works

### From Jonathan Bossenger
- Bash aliases speed up repetitive tasks
- `rsync` with `--exclude` is reliable
- Manual process gives you control

## Timeline

- **Week 1**: Review and refine plan, test directory structure locally
- **Week 2**: Implement new structure in feature branch
- **Week 3**: Testing activation from various mounting scenarios
- **Week 4**: Migration and first release with new structure

## Success Criteria

1. ✅ Plugin activates regardless of repository folder name
2. ✅ Clear separation between dev and distribution files
3. ✅ Simple, scriptable deployment to WordPress.org
4. ✅ No breaking changes for existing users
5. ✅ Improved developer experience

---

*This plan combines proven practices from experienced WordPress developers with the specific needs of the JWT Auth Pro plugin. The nested directory structure specifically addresses the WordPress directory naming requirement that Jon Surrell highlighted as a key benefit.*