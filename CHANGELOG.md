# Changelog

All notable changes to the Gravity Forms Listmonk Connector will be documented in this file.

## [1.0.0] - 2025-11-22

### Added
- **Smart merge logic for existing subscribers**: When a subscriber already exists in Listmonk, new data is intelligently merged instead of replaced
  - Lists are combined (existing + new) with duplicates removed
  - Attributes are merged: new values override old values, but existing attributes not in the new submission are preserved
  - Name is preserved if the new submission has an empty name field
  - Status is preserved unless explicitly changed
- Production-ready features:
  - Admin notice when credentials are missing but active feeds exist
  - Uninstall cleanup (removes all settings and transients)
  - Simplified cache key for list caching
  - Inline help links to Listmonk documentation
  - Explicit capability mapping
- Complete German (de_DE) translation
- Comprehensive documentation (README, PRODUCTION_READY, DEPLOYMENT_CHECKLIST)

### Changed
- Updated plugin version to 1.0.0
- Updated author information to "8am GmbH" with author URI
- Enhanced `upsert_subscriber()` method to merge data instead of replace
- Updated all documentation to reflect merge behavior

### Technical Details
- New `merge_subscriber_data()` method in `EAGF_Listmonk_Client` class
- Merge logic handles arrays, objects, and scalar values appropriately
- Preserves data integrity while allowing updates

## [0.1.0] - 2025-11-21

### Added
- Initial plugin structure using Gravity Forms Add-On Framework
- Global settings for Listmonk credentials (URL, username, API key)
- Per-form feed configuration
- Field mapping for email and name
- Static list presets (multi-select)
- Dynamic list assignment via form fields
- JSON attributes with merge tag support
- Preconfirm subscription options (global and per-feed)
- API client for Listmonk REST API
- List caching (10-minute transient)
- Entry notes for sync results
- Retry mechanism from entry detail screen
- Error handling that doesn't block form submissions
- Text domain and translation infrastructure
- Basic documentation

### Technical Details
- Uses Gravity Forms Feed Add-On Framework (`GFFeedAddOn`)
- HTTP basic authentication for Listmonk API
- Upsert logic (create or update by email)
- Nonce and capability protection on admin actions
