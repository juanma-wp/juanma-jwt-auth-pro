#!/bin/bash

# JWT Auth Pro - Deploy to WordPress.org SVN Repository
# Based on best practices from Birgit Pauli-Haack

set -e

# Configuration
PLUGIN_DIR="plugin/juanma-jwt-auth-pro"
SVN_DIR="svn-checkout"
ASSETS_DIR="assets"
PLUGIN_SLUG="juanma-jwt-auth-pro"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}JWT Auth Pro - WordPress.org Deployment Script${NC}"
echo "================================================"

# Check if we're in the right directory
if [ ! -d "$PLUGIN_DIR" ]; then
    echo -e "${RED}Error: Plugin directory not found at $PLUGIN_DIR${NC}"
    echo "Please run this script from the repository root"
    exit 1
fi

# Check if SVN checkout exists
if [ ! -d "$SVN_DIR" ]; then
    echo -e "${YELLOW}SVN checkout not found. Would you like to checkout the repository? (y/n)${NC}"
    read -r response
    if [[ "$response" == "y" ]]; then
        echo "Checking out WordPress.org SVN repository..."
        svn checkout "https://plugins.svn.wordpress.org/$PLUGIN_SLUG" "$SVN_DIR"
    else
        echo -e "${RED}Cannot proceed without SVN checkout${NC}"
        exit 1
    fi
fi

echo -e "\n${GREEN}Step 1: Installing production dependencies...${NC}"

# Install production dependencies in plugin directory
cd "$PLUGIN_DIR"
composer install --no-dev --optimize-autoloader --no-interaction --quiet
cd - > /dev/null

echo -e "\n${GREEN}Step 2: Syncing plugin files to SVN trunk...${NC}"

# Sync plugin files to trunk (contents, not the directory itself)
rsync -av --delete \
  --exclude='.DS_Store' \
  --exclude='*.map' \
  --exclude='*.log' \
  --exclude='languages/README.md' \
  --exclude='composer.json' \
  --exclude='composer.lock' \
  "$PLUGIN_DIR/" "$SVN_DIR/trunk/"

echo -e "\n${GREEN}Step 3: Syncing assets to SVN assets directory...${NC}"

# Sync assets if they exist
if [ -d "$ASSETS_DIR" ]; then
    rsync -av --delete \
      --exclude='.DS_Store' \
      "$ASSETS_DIR/" "$SVN_DIR/assets/"
else
    echo -e "${YELLOW}No assets directory found, skipping...${NC}"
fi

cd "$SVN_DIR"

echo -e "\n${GREEN}Step 4: Processing SVN changes...${NC}"

# Add new files
NEW_FILES=$(svn status | grep '^?' | awk '{print $2}')
if [ ! -z "$NEW_FILES" ]; then
    echo "$NEW_FILES" | xargs -r svn add
    echo "Added new files to SVN"
fi

# Remove deleted files
DELETED_FILES=$(svn status | grep '^!' | awk '{print $2}')
if [ ! -z "$DELETED_FILES" ]; then
    echo "$DELETED_FILES" | xargs -r svn delete
    echo "Removed deleted files from SVN"
fi

echo -e "\n${GREEN}Step 5: Review changes...${NC}"
svn status

echo -e "\n${YELLOW}Summary of changes to be committed:${NC}"
svn diff --summarize

echo -e "\n${YELLOW}Ready to commit these changes to WordPress.org? (y/n)${NC}"
read -r response

if [[ "$response" == "y" ]]; then
    echo -e "\n${GREEN}Committing to WordPress.org...${NC}"
    echo "Enter commit message:"
    read -r commit_message
    svn commit -m "$commit_message"
    echo -e "${GREEN}✓ Successfully deployed to WordPress.org trunk${NC}"
else
    echo -e "${YELLOW}Deployment cancelled. No changes were committed.${NC}"
fi