# Build Instructions

This document explains how to build a distribution package of the Gravity Forms Listmonk Connector plugin.

## Quick Start

### Option 1: Using the Build Script (Recommended)

```bash
# Make the script executable (first time only)
chmod +x build.sh

# Run the build
./build.sh
```

This will create:
- `dist/eightam-gravity-listmonk-1.0.0.zip` - The plugin package
- `dist/eightam-gravity-listmonk-1.0.0.zip.sha256` - Checksum file

### Option 2: Using npm

```bash
# Run the build
npm run build

# Clean build artifacts
npm run clean
```

### Option 3: Manual Build

```bash
# Create directories
mkdir -p build/eightam-gravity-listmonk
mkdir -p dist

# Copy files
cp listmonk-gravityforms.php build/eightam-gravity-listmonk/
cp uninstall.php build/eightam-gravity-listmonk/
cp composer.json build/eightam-gravity-listmonk/
cp README.md build/eightam-gravity-listmonk/
cp CHANGELOG.md build/eightam-gravity-listmonk/
cp -r includes build/eightam-gravity-listmonk/
cp -r assets build/eightam-gravity-listmonk/
cp -r languages build/eightam-gravity-listmonk/

# Create zip
cd build
zip -r ../dist/eightam-gravity-listmonk-1.0.0.zip eightam-gravity-listmonk
cd ..
```

## What Gets Included

The build script includes these files and directories:

### Required Files
- `listmonk-gravityforms.php` - Main plugin file
- `uninstall.php` - Cleanup on uninstall
- `composer.json` - Package metadata
- `README.md` - Plugin documentation
- `CHANGELOG.md` - Version history

### Required Directories
- `includes/` - PHP classes
  - `class-listmonk-addon.php`
  - `class-listmonk-client.php`
- `assets/` - Static assets
  - `css/admin.css`
  - `js/admin.js`
  - `img/listmonk-icon.svg`
- `languages/` - Translation files
  - `eightam-gf-listmonk-de_DE.po`

## What Gets Excluded

Files excluded from distribution (see `.distignore`):

### Development Files
- `.git/` - Git repository
- `.gitignore` - Git ignore rules
- `build/` - Build directory
- `dist/` - Distribution directory
- `node_modules/` - npm packages

### Documentation (Optional)
- `TASKS.md` - Development tasks
- `DEBUG.md` - Debug instructions
- `PRODUCTION_READY.md` - Production notes
- `DEPLOYMENT_CHECKLIST.md` - Deployment guide
- `listmonk api.md` - API reference

### Build Files
- `build.sh` - Build script
- `package.json` - npm configuration
- `BUILD.md` - This file

### System Files
- `.DS_Store` - macOS metadata
- `Thumbs.db` - Windows thumbnails
- `*.log` - Log files

## Build Output

After running the build, you'll get:

```
dist/
├── eightam-gravity-listmonk-1.0.0.zip
└── eightam-gravity-listmonk-1.0.0.zip.sha256
```

### Verify the Package

```bash
# List contents
unzip -l dist/eightam-gravity-listmonk-1.0.0.zip

# Verify checksum
cd dist
shasum -a 256 -c eightam-gravity-listmonk-1.0.0.zip.sha256
cd ..

# Extract and test
unzip dist/eightam-gravity-listmonk-1.0.0.zip -d test-install
```

## Pre-Build Checklist

Before building, ensure:

- [ ] Version number updated in `listmonk-gravityforms.php`
- [ ] Version number updated in `CHANGELOG.md`
- [ ] Version number updated in `composer.json`
- [ ] Version number updated in `package.json`
- [ ] Version number updated in `languages/*.po` files
- [ ] All changes committed to git
- [ ] Tests passing (if applicable)
- [ ] Documentation updated

## Post-Build Steps

After building:

1. **Test the package**:
   ```bash
   # Extract to test directory
   unzip dist/eightam-gravity-listmonk-1.0.0.zip -d /tmp/test-plugin
   
   # Install in test WordPress site
   cp -r /tmp/test-plugin/eightam-gravity-listmonk /path/to/wordpress/wp-content/plugins/
   ```

2. **Verify in WordPress**:
   - Activate the plugin
   - Check for errors
   - Test basic functionality
   - Verify translations load

3. **Create GitHub release** (if applicable):
   ```bash
   git tag v1.0.0
   git push origin v1.0.0
   ```

4. **Upload to distribution**:
   - WordPress.org plugin repository
   - Private update server
   - Client delivery

## Troubleshooting

### Build script not executable
```bash
chmod +x build.sh
```

### Permission denied errors
```bash
sudo chown -R $(whoami) build dist
```

### Zip command not found
```bash
# macOS
brew install zip

# Ubuntu/Debian
sudo apt-get install zip
```

### Wrong version in output
Update version in `listmonk-gravityforms.php`:
```php
* Version: 1.0.0
```

And in the constant:
```php
define( 'EAGF_LISTMONK_VERSION', '1.0.0' );
```

## Clean Up

Remove build artifacts:

```bash
# Using npm
npm run clean

# Manual
rm -rf build dist
```

## Continuous Integration

For automated builds, add to your CI/CD pipeline:

```yaml
# Example GitHub Actions
- name: Build plugin
  run: |
    chmod +x build.sh
    ./build.sh

- name: Upload artifact
  uses: actions/upload-artifact@v2
  with:
    name: plugin-package
    path: dist/*.zip
```

## Version Management

To bump version:

1. Update `listmonk-gravityforms.php` header
2. Update `EAGF_LISTMONK_VERSION` constant
3. Update `composer.json`
4. Update `package.json`
5. Update translation files
6. Add entry to `CHANGELOG.md`
7. Commit changes
8. Run build script
9. Tag release

## Support

For build issues, check:
- File permissions
- Disk space
- Required commands installed (zip, shasum)
- Path to plugin directory

---

**Last Updated**: 2025-11-22  
**Plugin Version**: 1.0.0
