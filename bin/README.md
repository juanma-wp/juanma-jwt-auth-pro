# Deployment Scripts

This directory contains scripts for managing the plugin's deployment workflow to WordPress.org.

## Scripts Overview

### [clean-toolkit.sh](clean-toolkit.sh)
Cleans up the `vendor/wp-rest-auth/auth-toolkit` directory by removing either symlinks or directories.

**Usage:**
```bash
./scripts/clean-toolkit.sh
```

**Purpose:**
- Removes development symlinks before production deployment
- Ensures clean state for composer production dependencies

---

### [deploy-to-svn.sh](deploy-to-svn.sh)
Main deployment script that syncs plugin files to WordPress.org SVN trunk and commits changes.

**Usage:**
```bash
./scripts/deploy-to-svn.sh
```

**What it does:**
1. Validates plugin directory exists at `plugin/juanma-jwt-auth-pro`
2. Checks out SVN repository if not present
3. Installs production dependencies (`composer install --no-dev`)
4. Syncs plugin files to `svn-checkout/trunk/`
5. Syncs assets to `svn-checkout/assets/`
6. Processes SVN changes (adds new files, removes deleted files)
7. Shows diff summary for review
8. Prompts for commit confirmation and message
9. Commits to WordPress.org SVN trunk

**Exclusions:**
- `.DS_Store` files
- `*.map` files
- `*.log` files
- `languages/README.md`
- `composer.lock`

---

### [sync-to-trunk.sh](sync-to-trunk.sh)
Quick sync script for testing - syncs files to SVN trunk WITHOUT committing.

**Usage:**
```bash
./scripts/sync-to-trunk.sh
```

**What it does:**
1. Validates plugin and SVN directories exist
2. Syncs plugin files to `svn-checkout/trunk/`
3. Syncs assets to `svn-checkout/assets/`
4. Shows SVN status
5. **Does NOT commit** - useful for local testing

**Use case:**
- Testing deployment before actual commit
- Reviewing file changes locally
- Validating rsync excludes

---

### [create-release.sh](create-release.sh)
Creates a tagged release on WordPress.org from the SVN trunk.

**Usage:**
```bash
./scripts/create-release.sh
```

**What it does:**
1. Validates SVN checkout exists (requires prior deployment)
2. Extracts version from `plugin/juanma-jwt-auth-pro/juanma-jwt-auth-pro.php`
3. Checks if tag already exists
4. Creates tag by copying trunk to `tags/X.X.X`
5. Shows tag changes for review
6. Prompts for commit confirmation
7. Commits tag to WordPress.org
8. Shows plugin URL on success

**Prerequisites:**
- SVN trunk must be up-to-date (run `deploy-to-svn.sh` first)

---

## Typical Deployment Workflow

### Full Release to WordPress.org

```bash
# 1. Ensure toolkit is clean (optional, if using symlinks)
./scripts/clean-toolkit.sh

# 2. Deploy to trunk
./scripts/deploy-to-svn.sh
# - Reviews changes
# - Enters commit message
# - Confirms deployment

# 3. Create tagged release
./scripts/create-release.sh
# - Confirms version number
# - Reviews tag changes
# - Confirms tag creation

# Result: Version X.X.X live on WordPress.org
```

### Testing Deployment (Dry Run)

```bash
# Sync files without committing
./scripts/sync-to-trunk.sh

# Review changes in svn-checkout/
cd svn-checkout
svn status
svn diff

# If satisfied, deploy for real
./scripts/deploy-to-svn.sh
```

---

## Configuration

All scripts use these directory paths:
- **Plugin source:** `plugin/juanma-jwt-auth-pro`
- **SVN checkout:** `svn-checkout`
- **Assets:** `assets`
- **Plugin slug:** `juanma-jwt-auth-pro`

Scripts must be run from the repository root directory.

---

## Requirements

- `svn` command-line tool
- `rsync` command-line tool
- `composer` (for dependency management)
- WordPress.org SVN credentials (for commit operations)

---

## Notes

- All scripts include interactive prompts before making changes to WordPress.org
- Color-coded output for better visibility (green=success, yellow=warning, red=error)
- Set `-e` flag to exit on errors
- Scripts validate directory existence before proceeding
