#!/bin/bash

# JWT Auth Pro - Quick Sync to SVN Trunk (for testing)
# This script syncs files without committing - useful for local testing

set -e

# Configuration
PLUGIN_DIR="plugin/juanma-jwt-auth-pro"
SVN_DIR="svn-checkout"
ASSETS_DIR="assets"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}JWT Auth Pro - Sync to SVN Trunk (Test Mode)${NC}"
echo "============================================="

# Check if we're in the right directory
if [ ! -d "$PLUGIN_DIR" ]; then
    echo -e "${RED}Error: Plugin directory not found at $PLUGIN_DIR${NC}"
    echo "Please run this script from the repository root"
    exit 1
fi

# Check if SVN checkout exists
if [ ! -d "$SVN_DIR" ]; then
    echo -e "${RED}Error: SVN checkout not found at $SVN_DIR${NC}"
    echo "Please run: svn checkout https://plugins.svn.wordpress.org/juanma-jwt-auth-pro svn-checkout"
    exit 1
fi

echo -e "\n${GREEN}Syncing plugin files to SVN trunk...${NC}"

# Build rsync exclude arguments from .distignore
RSYNC_EXCLUDES=""
if [ -f ".distignore" ]; then
    while IFS= read -r line || [ -n "$line" ]; do
        # Skip empty lines and comments
        [[ -z "$line" || "$line" =~ ^#.*$ ]] && continue
        # Remove leading slash if present (rsync doesn't need it)
        pattern="${line#/}"
        RSYNC_EXCLUDES="$RSYNC_EXCLUDES --exclude=$pattern"
    done < .distignore
fi

# Sync plugin files to trunk
rsync -av --delete \
  $RSYNC_EXCLUDES \
  "$PLUGIN_DIR/" "$SVN_DIR/trunk/"

# Sync assets if they exist
if [ -d "$ASSETS_DIR" ]; then
    echo -e "\n${GREEN}Syncing assets...${NC}"
    rsync -av --delete \
      --exclude='.DS_Store' \
      "$ASSETS_DIR/" "$SVN_DIR/assets/"
fi

echo -e "\n${GREEN}✓ Files synced to $SVN_DIR${NC}"
echo -e "${YELLOW}Note: Changes are NOT committed to WordPress.org${NC}"
echo "To commit, run: ./scripts/deploy-to-svn.sh"

# Show what changed
cd "$SVN_DIR"
echo -e "\n${GREEN}SVN Status:${NC}"
svn status