#!/bin/bash

# Build script for Gravity Forms Listmonk Connector
# Creates a production-ready zip file for distribution

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PLUGIN_SLUG="eightam-gravity-listmonk"
VERSION=$(grep "Version:" listmonk-gravityforms.php | awk '{print $3}')
BUILD_DIR="build"
DIST_DIR="dist"
PLUGIN_DIR="${BUILD_DIR}/${PLUGIN_SLUG}"

echo -e "${GREEN}Building ${PLUGIN_SLUG} v${VERSION}${NC}"

# Clean previous builds
echo -e "${YELLOW}Cleaning previous builds...${NC}"
rm -rf "${BUILD_DIR}"
rm -rf "${DIST_DIR}"

# Create build directories
mkdir -p "${PLUGIN_DIR}"
mkdir -p "${DIST_DIR}"

# Copy plugin files
echo -e "${YELLOW}Copying plugin files...${NC}"

# Main plugin file
cp listmonk-gravityforms.php "${PLUGIN_DIR}/"

# Uninstall file
cp uninstall.php "${PLUGIN_DIR}/"

# Composer file (optional, for dependency management)
cp composer.json "${PLUGIN_DIR}/"

# Includes directory
cp -r includes "${PLUGIN_DIR}/"

# Assets directory
cp -r assets "${PLUGIN_DIR}/"

# Languages directory
cp -r languages "${PLUGIN_DIR}/"

# Documentation files
echo -e "${YELLOW}Copying documentation...${NC}"
cp README.md "${PLUGIN_DIR}/"
cp CHANGELOG.md "${PLUGIN_DIR}/"

# Optional: Copy additional docs (comment out if you don't want these in distribution)
# cp PRODUCTION_READY.md "${PLUGIN_DIR}/"
# cp DEPLOYMENT_CHECKLIST.md "${PLUGIN_DIR}/"
# cp DEBUG.md "${PLUGIN_DIR}/"
# cp TASKS.md "${PLUGIN_DIR}/"

# Remove development files from build
echo -e "${YELLOW}Removing development files...${NC}"
find "${PLUGIN_DIR}" -name ".DS_Store" -delete
find "${PLUGIN_DIR}" -name "*.log" -delete
find "${PLUGIN_DIR}" -name "Thumbs.db" -delete

# Create zip file
echo -e "${YELLOW}Creating zip archive...${NC}"
cd "${BUILD_DIR}"
zip -r "../${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip" "${PLUGIN_SLUG}" -q
cd ..

# Create checksum
echo -e "${YELLOW}Generating checksum...${NC}"
cd "${DIST_DIR}"
shasum -a 256 "${PLUGIN_SLUG}-${VERSION}.zip" > "${PLUGIN_SLUG}-${VERSION}.zip.sha256"
cd ..

# Display results
echo -e "${GREEN}✓ Build complete!${NC}"
echo ""
echo "Package: ${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip"
echo "Size: $(du -h "${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip" | cut -f1)"
echo "Checksum: ${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip.sha256"
echo ""
echo -e "${GREEN}Contents:${NC}"
unzip -l "${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip" | head -20
echo ""
echo -e "${YELLOW}Note: Review the package before distribution${NC}"
