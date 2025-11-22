# Release Process

Quick reference for releasing a new version of the Gravity Forms Listmonk Connector.

## Pre-Release Checklist

- [ ] All features tested and working
- [ ] Debug logging removed or disabled
- [ ] Documentation updated
- [ ] CHANGELOG.md updated with new version
- [ ] Version numbers updated in all files (see below)
- [ ] German translations updated
- [ ] Git commits up to date

## Update Version Numbers

Update version in these files:

### 1. Main Plugin File
**File**: `listmonk-gravityforms.php`

```php
* Version: 1.0.0
```

```php
define( 'EAGF_LISTMONK_VERSION', '1.0.0' );
```

### 2. Composer
**File**: `composer.json`

```json
"version": "1.0.0",
```

### 3. Package.json
**File**: `package.json`

```json
"version": "1.0.0",
```

### 4. Translation Files
**File**: `languages/eightam-gf-listmonk-de_DE.po`

```
"Project-Id-Version: Gravity Forms Listmonk Connector 1.0.0\n"
```

### 5. Changelog
**File**: `CHANGELOG.md`

Add new version section at the top.

## Build Release

```bash
# Run the build script
./build.sh

# Verify the output
ls -lh dist/
```

Expected output:
```
dist/eightam-gravity-listmonk-1.0.0.zip
dist/eightam-gravity-listmonk-1.0.0.zip.sha256
```

## Test the Package

```bash
# Extract to test location
unzip dist/eightam-gravity-listmonk-1.0.0.zip -d /tmp/test-plugin

# Verify contents
ls -la /tmp/test-plugin/eightam-gravity-listmonk/

# Check file count (should have all necessary files)
find /tmp/test-plugin/eightam-gravity-listmonk -type f | wc -l
```

## Install and Test

1. **Test in clean WordPress install**:
   ```bash
   # Copy to test site
   cp -r /tmp/test-plugin/eightam-gravity-listmonk /path/to/test-site/wp-content/plugins/
   ```

2. **Activate and test**:
   - Activate plugin
   - Configure credentials
   - Create a feed
   - Submit test form
   - Verify subscriber in Listmonk
   - Test with existing subscriber (merge)
   - Test retry mechanism
   - Check German translation

3. **Verify no errors**:
   - Check PHP error log
   - Check browser console
   - Check Gravity Forms logs

## Git Tag and Push

```bash
# Commit all changes
git add .
git commit -m "Release version 1.0.0"

# Create tag
git tag -a v1.0.0 -m "Version 1.0.0"

# Push commits and tags
git push origin main
git push origin v1.0.0
```

## Distribution

### Option 1: Direct Distribution
Send the zip file to clients:
```
dist/eightam-gravity-listmonk-1.0.0.zip
```

### Option 2: GitHub Release
1. Go to GitHub repository
2. Click "Releases" → "Create a new release"
3. Select tag `v1.0.0`
4. Upload `dist/eightam-gravity-listmonk-1.0.0.zip`
5. Add release notes from CHANGELOG.md
6. Publish release

### Option 3: WordPress.org (if applicable)
1. SVN checkout
2. Copy files to trunk
3. Create tag
4. Commit to SVN

## Post-Release

- [ ] Update documentation site (if applicable)
- [ ] Notify clients of new version
- [ ] Monitor for issues
- [ ] Update support documentation
- [ ] Plan next version features

## Hotfix Process

For urgent fixes:

1. Create hotfix branch: `git checkout -b hotfix/1.0.1`
2. Make fixes
3. Update version to 1.0.1
4. Test thoroughly
5. Build and release
6. Merge back to main

## Version Numbering

Follow Semantic Versioning (semver.org):

- **Major** (1.0.0): Breaking changes
- **Minor** (1.1.0): New features, backwards compatible
- **Patch** (1.0.1): Bug fixes, backwards compatible

## Rollback

If issues are found after release:

1. **Immediate**: Remove download link
2. **Notify**: Alert users who downloaded
3. **Fix**: Create hotfix version
4. **Test**: Thoroughly test fix
5. **Release**: New patched version

## Support

After release, monitor:
- GitHub issues
- Support email
- WordPress.org support forum (if applicable)
- Client feedback

---

**Current Version**: 1.0.0  
**Last Release**: 2025-11-22  
**Next Planned**: TBD
