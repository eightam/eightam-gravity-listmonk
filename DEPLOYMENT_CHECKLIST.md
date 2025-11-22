# Deployment Checklist

Use this checklist before deploying the Gravity Forms Listmonk Connector to production.

## Pre-Deployment

### Code Review
- [x] All PHP files follow WordPress coding standards
- [x] All user input is sanitized and validated
- [x] All output is properly escaped
- [x] No direct file access (ABSPATH checks in place)
- [x] Error handling prevents form submission blocking
- [x] Logging doesn't expose sensitive data in production

### Security
- [x] Nonce verification on all admin actions
- [x] Capability checks on all sensitive operations
- [x] HTTP basic auth over HTTPS only
- [x] No hardcoded credentials
- [x] Uninstall cleanup removes all data

### Functionality
- [x] Plugin bootstrap loads correctly
- [x] Text domain loads translations
- [x] Settings page accessible
- [x] Feed configuration UI works
- [x] List caching implemented
- [x] Retry mechanism functional
- [x] Admin notices display correctly

### Documentation
- [x] README.md complete with installation and usage
- [x] TASKS.md tracks development progress
- [x] PRODUCTION_READY.md documents improvements
- [x] Inline help links to Listmonk docs
- [x] Code comments explain complex logic

### Translations
- [x] Text domain declared in plugin header
- [x] Domain path set to /languages
- [x] All strings wrapped in translation functions
- [x] German (de_DE) translation complete
- [ ] Compile .po to .mo file (optional, can be done post-deployment)

## Testing Checklist

### Basic Functionality
- [ ] Install plugin successfully
- [ ] Activate plugin without errors
- [ ] Access settings page
- [ ] Save credentials
- [ ] Lists load from Listmonk API
- [ ] Create feed on a form
- [ ] Submit form and verify subscriber created
- [ ] Check entry notes for success message

### Advanced Features
- [ ] Test with existing subscriber (update flow)
- [ ] Test static list presets
- [ ] Test dynamic list field (text input)
- [ ] Test dynamic list field (checkbox)
- [ ] Test attributes JSON with merge tags
- [ ] Test all preconfirm modes (inherit/yes/no)
- [ ] Test retry button on entry detail
- [ ] Verify admin notice when credentials missing

### Error Handling
- [ ] Test with invalid credentials
- [ ] Test with unreachable Listmonk server
- [ ] Test with invalid JSON in attributes
- [ ] Test with missing email field
- [ ] Test with no lists selected
- [ ] Verify form submission still succeeds on API failure

### Multisite (if applicable)
- [ ] Test on multisite network
- [ ] Verify settings per-site
- [ ] Test uninstall cleanup on multisite

### Translations
- [ ] Switch WordPress to German (de_DE)
- [ ] Verify all admin strings translated
- [ ] Verify help links still work

### Performance
- [ ] Verify list cache works (check transients)
- [ ] Confirm cache expires after 10 minutes
- [ ] Test with large number of lists (100+)
- [ ] Monitor form submission time

## Deployment Steps

1. **Backup**
   - [ ] Backup WordPress database
   - [ ] Backup wp-content/plugins directory

2. **Upload**
   - [ ] Upload plugin to wp-content/plugins/
   - [ ] Verify file permissions (644 for files, 755 for directories)
   - [ ] Ensure uninstall.php is executable

3. **Activate**
   - [ ] Activate plugin in WordPress admin
   - [ ] Check for PHP errors in debug log
   - [ ] Verify no conflicts with other plugins

4. **Configure**
   - [ ] Navigate to Forms → Settings → Listmonk
   - [ ] Enter Listmonk base URL (HTTPS)
   - [ ] Enter API username
   - [ ] Enter API key/token
   - [ ] Test connection by creating a feed

5. **Create Feeds**
   - [ ] Edit a form
   - [ ] Go to Settings → Listmonk
   - [ ] Create a feed
   - [ ] Map email field (required)
   - [ ] Map name field (optional)
   - [ ] Select lists
   - [ ] Configure preconfirm mode
   - [ ] Save feed

6. **Test**
   - [ ] Submit test form
   - [ ] Check entry notes
   - [ ] Verify subscriber in Listmonk
   - [ ] Test retry if needed

## Post-Deployment

### Monitoring
- [ ] Monitor WordPress error logs
- [ ] Monitor Listmonk API logs
- [ ] Check Gravity Forms entry notes regularly
- [ ] Review admin notices

### Documentation
- [ ] Document credentials location for team
- [ ] Document any custom configurations
- [ ] Create internal support guide

### Maintenance
- [ ] Schedule regular testing
- [ ] Plan for Gravity Forms updates
- [ ] Plan for WordPress updates
- [ ] Monitor Listmonk API changes

## Rollback Plan

If issues occur:

1. **Immediate**
   - Deactivate plugin via WordPress admin
   - Or rename plugin directory via FTP/SSH

2. **Data**
   - Feeds remain in database (safe to reactivate)
   - Settings remain in database (safe to reactivate)
   - No data loss on deactivation

3. **Cleanup** (if permanently removing)
   - Delete plugin via WordPress admin
   - Uninstall.php will clean up automatically
   - Or manually delete from wp-content/plugins/

## Support Contacts

- **Plugin Developer:** 8am.ch
- **Gravity Forms:** https://www.gravityforms.com/support/
- **Listmonk:** https://listmonk.app/docs/

## Version Information

- **Plugin Version:** 0.1.0
- **WordPress Version Required:** 5.8+
- **PHP Version Required:** 7.4+
- **Gravity Forms Version Required:** 2.5+
- **Tested Up To:** WordPress 6.5

---

**Deployment Date:** _________________

**Deployed By:** _________________

**Production URL:** _________________

**Listmonk URL:** _________________
