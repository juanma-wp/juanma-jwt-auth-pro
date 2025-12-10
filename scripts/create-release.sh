#!/bin/bash

# JWT Auth Pro - Create Release Tag in WordPress.org SVN
# Based on workflow from Birgit Pauli-Haack

set -e

# Configuration
SVN_DIR="svn-checkout"
PLUGIN_SLUG="juanma-jwt-auth-pro"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${GREEN}JWT Auth Pro - Create Release Tag${NC}"
echo "=================================="

# Check if SVN checkout exists
if [ ! -d "$SVN_DIR" ]; then
    echo -e "${RED}Error: SVN checkout not found at $SVN_DIR${NC}"
    echo "Please run deploy-to-svn.sh first"
    exit 1
fi

# Get version from main plugin file
VERSION=$(grep "Version:" plugin/juanma-jwt-auth-pro/juanma-jwt-auth-pro.php | awk '{print $3}')

if [ -z "$VERSION" ]; then
    echo -e "${RED}Error: Could not determine plugin version${NC}"
    exit 1
fi

echo -e "${BLUE}Current plugin version: $VERSION${NC}"

cd "$SVN_DIR"

# Check if tag already exists
if [ -d "tags/$VERSION" ]; then
    echo -e "${YELLOW}Warning: Tag $VERSION already exists${NC}"
    echo "Do you want to overwrite it? (y/n)"
    read -r response
    if [[ "$response" != "y" ]]; then
        echo "Enter new version number:"
        read -r VERSION
    else
        svn delete "tags/$VERSION"
    fi
fi

echo -e "\n${GREEN}Creating tag $VERSION from trunk...${NC}"

# Create the tag
svn cp trunk "tags/$VERSION"

echo -e "\n${GREEN}Review tag changes:${NC}"
svn status

echo -e "\n${YELLOW}Ready to commit tag $VERSION to WordPress.org? (y/n)${NC}"
read -r response

if [[ "$response" == "y" ]]; then
    echo -e "\n${GREEN}Committing tag to WordPress.org...${NC}"
    svn commit -m "Tagging version $VERSION"
    echo -e "${GREEN}✓ Successfully created tag $VERSION on WordPress.org${NC}"

    echo -e "\n${GREEN}🎉 Release $VERSION is now live on WordPress.org!${NC}"
    echo -e "View at: https://wordpress.org/plugins/$PLUGIN_SLUG/"
else
    echo -e "${YELLOW}Tag creation cancelled. Changes not committed.${NC}"
    # Revert the tag creation
    svn revert -R "tags/$VERSION"
    rm -rf "tags/$VERSION"
fi