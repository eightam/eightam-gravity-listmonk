# Gravity Forms Listmonk Connector

Minimal Gravity Forms feed add-on that pushes form submissions into [Listmonk](https://listmonk.app/) using its REST API.

This plugin is intended for site owners who already use Gravity Forms and Listmonk and want a simple, reliable way to add or update subscribers when a form is submitted.

## Features

- **Listmonk API integration** using basic authentication
- **Per-form feeds** via the Gravity Forms Add-On framework
- **Field mapping** for email and name
- **Static list presets** (multi-select from Listmonk lists)
- **Dynamic list assignment** based on submitted field values (IDs via text/select/checkbox/JSON)
- **Subscriber attributes** through a JSON field with merge-tag support
- **Retry support** from the Gravity Forms entry detail screen
- **Translatable** (text domain `eightam-gf-listmonk`, German `de_DE` included)

## Requirements

- WordPress 5.8+
- PHP 7.4+
- Gravity Forms 2.5+
- A running Listmonk instance with API access over HTTPS

## Installation

1. Install and activate **Gravity Forms**.
2. Copy this plugin into `wp-content/plugins/eightam-gravity-listmonk` or install it as a normal WordPress plugin.
3. Activate **Gravity Forms Listmonk Connector** in **Plugins → Installed Plugins**.

If Gravity Forms is not active, the add-on will not register and no feeds will run.

## Configuration

### 1. Global Listmonk settings

1. Go to **Forms → Settings → Listmonk**.
2. Enter your **Base URL**, e.g. `https://listmonk.example.com/`.
3. Enter the **API Username** and **API Key / Token**.
4. Optionally enable **Preconfirm Subscriptions by Default** if you want to skip double opt-in globally.

These settings are used for all feeds unless overridden per feed.

### 2. Creating a feed for a form

1. Open **Forms → Forms**, edit a form, and go to **Settings → Listmonk**.
2. Click **Add New** to create a feed.
3. Configure:
   - **Feed Name**: Any label to identify this feed.
   - **Field Mapping**:
     - **Email Address** (required)
     - **Name** (optional)
   - **Attributes JSON** (optional): a JSON object of custom attributes.
     - Example: `{ "city": "Berlin", "source": "website" }`
     - Merge tags are supported and are replaced before sending to Listmonk.

### 3. List assignment

You can assign subscribers to lists in two ways (they can be combined):

- **List Presets (Optional)**
  - Multi-select of Listmonk lists fetched from the API.
  - Selected lists are always applied to the subscriber.

- **Dynamic List Field**
  - Choose a form field whose submitted value(s) resolve to Listmonk list IDs.
  - Supported patterns:
    - Comma-separated list IDs, e.g. `1,2,3`
    - JSON array of IDs, e.g. `[1, 2, 3]`
    - Checkbox/select fields whose choice values are list IDs

### 4. Preconfirm mode

Per feed you can control whether subscriptions are preconfirmed:

- **Inherit global setting** (default)
- **Always preconfirm this feed**
- **Require confirmation (double opt-in)**

The resulting boolean is sent as `preconfirm_subscriptions` to Listmonk.

## How it works

On successful form submission, the feed:

1. Resolves the mapped email and name.
2. Resolves list IDs from presets and the dynamic field.
3. Builds an attributes payload from the JSON field after merge-tag replacement.
4. Calls the Listmonk API to **create** a subscriber.
5. On HTTP 409 (already exists), looks up the subscriber by email and **merges** the new data with existing data:
   - **Lists**: Combines existing and new lists (no duplicates)
   - **Attributes**: New values override old values, but existing attributes not in the new submission are preserved
   - **Name**: Keeps existing name if new name is empty
   - **Status**: Preserves existing status unless explicitly changed
6. Writes a note to the Gravity Forms entry with success or error details.

## Retrying a sync

On the **Entry** detail screen for a form with a Listmonk feed:

1. Open the entry in the admin.
2. Use the **Listmonk Actions** meta box.
3. Click **Retry Feed**.

All active Listmonk feeds for that form will be re-run for the selected entry.

Permissions: only users with the `gravityforms_listmonk` capability can see and use this box.

## Translations

- Text domain: `eightam-gf-listmonk`
- Domain path: `/languages`
- Included: `de_DE` translation (`languages/eightam-gf-listmonk-de_DE.po`)

To adjust translations or generate a `.mo` file, open the `.po` file in a tool like Poedit and save/compile it. When deployed via WordPress.org, translations are typically managed via GlotPress instead.
