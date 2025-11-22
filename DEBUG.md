# Debugging the Listmonk Plugin

## Enable WordPress Debug Logging

1. **Check if debug is enabled** in `wp-config.php`:
   ```php
   define( 'WP_DEBUG', true );
   define( 'WP_DEBUG_LOG', true );
   define( 'WP_DEBUG_DISPLAY', false );
   ```

2. **Debug log location**:
   - Local by Flywheel: `/Users/nick/Local Sites/vibe/app/public/wp-content/debug.log`
   - Standard WordPress: `wp-content/debug.log`

## View Debug Logs

### Option 1: Terminal (Real-time)
```bash
tail -f "/Users/nick/Local Sites/vibe/app/public/wp-content/debug.log"
```

### Option 2: View Last 50 Lines
```bash
tail -n 50 "/Users/nick/Local Sites/vibe/app/public/wp-content/debug.log"
```

### Option 3: Search for Listmonk Entries
```bash
grep "Listmonk" "/Users/nick/Local Sites/vibe/app/public/wp-content/debug.log"
```

## What to Look For

When you submit a form, you should see these log entries:

1. **Create Response** (when subscriber exists):
   ```
   Listmonk create response: Array
   (
       [success] => 
       [message] => E-Mail existiert bereits. (HTTP 409)
       [code] => 409
       ...
   )
   ```

2. **Lookup Attempt**:
   ```
   Listmonk: Subscriber exists (409), attempting lookup for: email@example.com
   ```

3. **Lookup Response**:
   ```
   Listmonk lookup response: Array
   (
       [success] => 1
       [data] => Array
       (
           [data] => Array
           (
               [results] => Array
               (
                   [0] => Array
                   (
                       [id] => 123
                       [email] => email@example.com
                       ...
                   )
               )
           )
       )
   )
   ```

4. **Found Subscriber**:
   ```
   Listmonk: Found existing subscriber ID: 123
   ```

5. **Merged Payload**:
   ```
   Listmonk merged payload: Array
   (
       [email] => email@example.com
       [lists] => Array(1, 2, 3)
       [attribs] => stdClass Object
       ...
   )
   ```

## Common Issues

### Issue 1: Lookup Returns Empty Results
**Log shows**: `Listmonk: Lookup failed or no results found`

**Possible causes**:
- Email query syntax is wrong
- Listmonk API query parameter format is incorrect
- Email contains special characters that need escaping

**Check**: Look at the lookup response structure

### Issue 2: Wrong Data Path
**Log shows**: Lookup succeeds but "no results found"

**Possible causes**:
- Response structure is different than expected
- Data is at `data.results` instead of `data.data.results`

**Check**: Print the full `$existing['data']` structure

### Issue 3: Update Fails
**Log shows**: Found subscriber but update fails

**Possible causes**:
- Merged payload has invalid format
- Required fields missing in merged payload
- API permissions issue

**Check**: Look at the merged payload structure

## Quick Debug Steps

1. **Submit the form** with an existing email
2. **Check the error message** in Gravity Forms entry notes
3. **Open debug log**:
   ```bash
   tail -f "/Users/nick/Local Sites/vibe/app/public/wp-content/debug.log"
   ```
4. **Submit form again** and watch the log in real-time
5. **Look for**:
   - "Listmonk create response" - Should show 409 error
   - "Listmonk lookup response" - Should show subscriber data
   - "Listmonk: Found existing subscriber ID" - Should show ID number
   - "Listmonk merged payload" - Should show merged data

## Disable Debug Logging

Once debugging is complete, remove the `error_log()` calls or set:
```php
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
```

## Alternative: Use Gravity Forms Logging

Gravity Forms has built-in logging. Enable it:
1. Go to **Forms → Settings → Logging**
2. Enable logging
3. Check logs at **Forms → System Status → Logs**
