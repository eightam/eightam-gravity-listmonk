# Gravity Forms Listmonk Integration

Minimal Gravity Forms add-on that pushes entries to Listmonk using its REST API.

## Completed Tasks

- [x] Capture Listmonk API reference and project requirements
- [x] Scaffold Gravity Forms add-on plugin structure
- [x] Implement global settings (URL, username, API key, preconfirm default)
- [x] Build feed UI for field mappings, list presets, and dynamic selections
- [x] Create repeatable key/value UI for Listmonk attributes (fallback to JSON textarea)
- [x] Implement API client to fetch lists and create/update subscribers
- [x] Handle entry processing, list assignment, logging, and retry button
- [x] Document usage and update README/admin help text
- [x] Localization pass for admin strings (including German de_DE)
- [x] Update README for plugin usage and setup
- [x] German translation (de_DE) for admin interface
- [x] Production readiness improvements:
  - [x] Admin notice when credentials missing but feeds exist
  - [x] Uninstall cleanup (settings and transients)
  - [x] Simplified cache key for list caching
  - [x] Inline help links to Listmonk documentation
  - [x] Explicit capability mapping

## In Progress Tasks

- [ ] Test form submission and verify Listmonk integration

## Future Tasks

- [ ] Automated tests or mocks for API client
- [ ] WP-CLI command for bulk re-syncing entries

## Implementation Plan

1. **Plugin Bootstrap**
   - Create plugin header, autoloader (if needed), and register with Gravity Forms Add-On Framework.
   - Ensure activation checks for Gravity Forms version.
2. **Settings Page**
   - Add global settings section for Listmonk base URL, username, API key, and default preconfirm toggle.
   - Store securely via GF settings API; validate URL/auth fields.
3. **Feed Configuration**
   - Provide mappings for name, email, and optional additional fields.
   - Implement repeatable key/value rows (JS-enhanced) with JSON textarea fallback for custom attribs.
   - Allow list selection via:
     - Static presets (checkboxes populated from API lists at save time).
     - Dynamic selections referencing form fields (supports Listmonk list IDs via GF merge tags or choices).
   - Include per-feed preconfirm override.
4. **List Retrieval**
   - On settings/feed load, call `/api/lists` with stored credentials (cache results to reduce API calls).
   - Handle pagination by requesting high per_page value.
5. **Submission Handling**
   - During `process_feed`, assemble payload: email, name, status (enabled/preconfirm), lists array, attribs JSON.
   - Call `/api/subscribers` with POST (or PUT for updates) and capture response/errors.
   - Log results into Gravity Forms entry notes (success/failure) with timestamps and request IDs.
6. **Retry Support**
   - Add custom entry detail button for admins to re-run feed (leveraging `GFAPI::send_feed_to_entry`).
7. **Error Handling & UX**
   - Surface API failures via entry notes and admin notices; never block entry creation.
   - Provide inline help text linking to Listmonk docs.
8. **Documentation**
   - Update README/admin help and include instructions for obtaining API credentials.

## Relevant Files

- ✅ listmonk api.md — Collected API examples for reference
- ⏳ TASKS.md — Project plan and status tracker
- ⏳ listmonk-gravityforms.php — Main plugin bootstrap (to be created)
- ⏳ includes/class-listmonk-addon.php — Gravity Forms add-on implementation
- ⏳ assets/js/admin.js — Repeatable fields UI logic
- ⏳ assets/css/admin.css — Admin styling for feeds/settings

## Coding Standards & Guidelines

- Adhere to WordPress PHP coding standards (spacing, naming, escaping).
- Leverage Gravity Forms Add-On Framework APIs (settings fields, feed processing, logging).
- Escape and sanitize all input/output via GF/WordPress helpers.
- Use dependency injection or small classes for API client logic; avoid global state.
- Keep functions focused; prefer early returns and guard clauses.
- Add inline docs (`@since`, param/return tags) for public methods.
- Cache external API responses when possible to reduce load.
- Provide translatable strings via `__()` / `_e()`.
