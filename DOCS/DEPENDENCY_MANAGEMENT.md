# Dependency Management Guide

## Overview

This project uses a **dual composer.json setup** to separate development and production dependencies:

- **Root `composer.json`**: Contains only development dependencies (PHPUnit, PHPCS, PHPStan, etc.)
- **Plugin `composer.json`**: Contains only production dependencies (wp-rest-auth-toolkit)

This separation ensures:
1. Clean production builds with minimal dependencies
2. Development tools stay at the root level
3. Easier dependency management and smaller plugin size

## Directory Structure

```
juanma-jwt-auth-pro/
├── composer.json           # Development dependencies only
├── vendor/                 # Dev tools (PHPUnit, PHPCS, etc.)
└── plugin/
    └── juanma-jwt-auth-pro/
        ├── composer.json   # Production dependencies only
        └── vendor/         # Plugin runtime dependencies
```

## Installation

### Initial Setup (Development)

```bash
# Install dev dependencies at root
composer install

# This automatically installs plugin dependencies via post-install-cmd
```

### Manual Plugin Dependencies

```bash
# Install production dependencies in plugin
composer run plugin:install

# Or with development dependencies for testing
composer run plugin:install-dev

# Update plugin dependencies
composer run plugin:update
```

## Available Composer Scripts

### Root Level Scripts

```bash
# Code quality
composer run lint          # Run PHPCS
composer run lint-fix      # Fix PHPCS issues
composer run phpstan       # Run PHPStan analysis

# Testing
composer run behat         # Run Behat tests

# Plugin dependency management
composer run plugin:install      # Install production deps (no-dev)
composer run plugin:update       # Update production deps
composer run plugin:install-dev  # Install with dev deps
```

## Deployment

The deployment script automatically handles the dual setup:

```bash
./scripts/deploy-to-svn.sh
```

This script:
1. Installs production-only dependencies in the plugin directory
2. Excludes `composer.json` and `composer.lock` from the SVN sync
3. Ensures only necessary files are deployed to WordPress.org

## Important Notes

### For Development

1. **Always run `composer install` at the root first** - this installs dev tools and triggers plugin dependency installation
2. The root `vendor/` directory contains only development tools
3. The plugin's `vendor/` directory contains only runtime dependencies

### For Production/Deployment

1. The deployment script runs `composer install --no-dev` in the plugin directory
2. Only production dependencies are included in releases
3. Development dependencies never ship to end users

### Dependency Updates

When updating dependencies:

```bash
# Update dev dependencies
composer update

# Update plugin production dependencies
composer run plugin:update

# Or manually
cd plugin/juanma-jwt-auth-pro
composer update
```

## Troubleshooting

### Plugin vendor directory is missing

```bash
cd plugin/juanma-jwt-auth-pro
composer install
```

### Tests can't find dependencies

Ensure both root and plugin vendor directories exist:

```bash
composer install                # Root dev dependencies
composer run plugin:install-dev # Plugin dependencies
```

### PHPStan can't find WordPress stubs

The WordPress stubs are in root vendor, ensure you run:

```bash
composer install
```

## Benefits of This Setup

1. **Smaller Plugin Size**: Only necessary production dependencies are included
2. **Cleaner Releases**: No development tools in distributed plugin
3. **Better Security**: Reduced attack surface with minimal production dependencies
4. **Easier Maintenance**: Clear separation between dev and prod environments
5. **Faster Installs**: Users don't download unnecessary dev dependencies

## Migration from Single composer.json

If you're migrating from a single composer.json setup:

1. Move production dependencies to `plugin/juanma-jwt-auth-pro/composer.json`
2. Keep dev dependencies in root `composer.json`
3. Remove autoload configuration from root (it's in plugin composer.json)
4. Run `composer install` at root, then `composer run plugin:install`
5. Update your deployment scripts to handle dual setup

---

*This dual composer setup follows WordPress plugin development best practices, ensuring clean separation between development and production environments.*