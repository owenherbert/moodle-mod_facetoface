# Testing Instructions for Private Calendar Invitation Flag Configuration

## Overview
This feature adds a site-level configuration option to enable/disable private calendar invitation flags in the Face-to-Face activity module. When enabled, calendar events in iCalendar attachments will be marked as `CLASS:PRIVATE`. When disabled, they will be marked as `CLASS:PUBLIC`.

## Prerequisites
- Moodle 4.3 or higher
- mod_facetoface plugin installed
- Administrator access to Moodle site
- Email configuration setup for testing email notifications

## Automated Testing

### Running PHPUnit Tests
From your Moodle installation directory:

```bash
# Initialize PHPUnit if not already done
php admin/tool/phpunit/cli/init.php

# Run all Face-to-Face tests
vendor/bin/phpunit --testsuite mod_facetoface_testsuite

# Run only the iCalendar privacy test
vendor/bin/phpunit mod/facetoface/tests/icalendar_privacy_test.php
```

### Expected Test Results
All three test methods should pass:
- `test_icalendar_private_flag_enabled()` - Verifies CLASS:PRIVATE when setting is ON
- `test_icalendar_private_flag_disabled()` - Verifies CLASS:PUBLIC when setting is OFF
- `test_icalendar_private_flag_default()` - Verifies default behavior (PUBLIC) when not set

## Manual Testing

### Step 1: Access the Setting
1. Log in as a site administrator
2. Navigate to: **Site administration** → **Plugins** → **Activity modules** → **Face-to-face**
3. Scroll down to the **iCalendar Attachments** section
4. Locate the new setting: **Private calendar invitations**

### Step 2: Test with Private Flag Enabled (Default)
1. Ensure the **Private calendar invitations** checkbox is **checked** (default state)
2. Click **Save changes**
3. Create a test course (or use an existing one)
4. Add a **Face-to-Face** activity to the course
5. Create a new session with:
   - Session date: Tomorrow or any future date
   - Set appropriate times
   - Save the session
6. Enrol a test user in the course
7. As the test user, sign up for the Face-to-Face session
8. Select notification type: **iCalendar** or **Email and iCalendar**
9. Complete the booking

**Verification:**
- Check the email received by the test user
- Download/save the iCalendar attachment (invite.ics)
- Open the .ics file in a text editor
- **Expected result:** Find the line `CLASS:PRIVATE` in the VEVENT section

Example iCalendar content:
```
BEGIN:VEVENT
UID:...
DTSTAMP:...
DTSTART:...
DTEND:...
SEQUENCE:0
SUMMARY:Test Face-to-Face
LOCATION:...
DESCRIPTION:...
CLASS:PRIVATE
TRANSP:OPAQUE
...
END:VEVENT
```

### Step 3: Test with Private Flag Disabled
1. Return to: **Site administration** → **Plugins** → **Activity modules** → **Face-to-face**
2. **Uncheck** the **Private calendar invitations** checkbox
3. Click **Save changes**
4. Cancel the previous booking (if still active)
5. Sign up for the same session again (or create a new session)
6. Follow the same booking process as in Step 2

**Verification:**
- Check the new email received
- Download/save the new iCalendar attachment
- Open the .ics file in a text editor
- **Expected result:** Find the line `CLASS:PUBLIC` instead of `CLASS:PRIVATE`

Example iCalendar content:
```
BEGIN:VEVENT
...
CLASS:PUBLIC
TRANSP:OPAQUE
...
END:VEVENT
```

### Step 4: Test Calendar Application Behavior

#### With CLASS:PRIVATE (Privacy Enabled):
1. Import the .ics file into your calendar application (Outlook, Google Calendar, Apple Calendar, etc.)
2. **Expected behavior:**
   - Event details (title, description, location) should only be visible to you
   - Other users viewing your calendar should see the time blocked but not the event details
   - The event should be marked as "Private" or show a lock icon (behavior varies by calendar app)

#### With CLASS:PUBLIC (Privacy Disabled):
1. Import the .ics file into your calendar application
2. **Expected behavior:**
   - Event details are fully visible
   - Other users with access to your calendar can see all event information
   - The event should be marked as "Public" or have no privacy restrictions

### Step 5: Test Cancellation Emails
1. Test cancellation with both settings (enabled and disabled)
2. Cancel a booking
3. Verify the cancellation iCalendar attachment also respects the privacy setting

## Edge Cases to Test

### Multiple Sessions
- Book multiple Face-to-Face sessions
- Verify all iCalendar attachments respect the current setting

### Multi-day Events
- Create a session spanning multiple days
- Verify the iCalendar attachment is generated correctly with the privacy setting

### Session Updates
- Book a session
- Modify the session details
- Verify updated notifications respect the privacy setting

### Notification Types
Test with different notification types:
- iCalendar only (MDL_F2F_ICAL)
- Email and iCalendar (MDL_F2F_BOTH)
- Verify plain text emails don't receive iCalendar attachments

## Rollback Testing
1. Disable the setting
2. Re-enable it
3. Verify the setting persists correctly across saves
4. Check that existing bookings are not affected by changing the setting
5. Only new notifications should reflect the updated setting

## Code Quality Checks

### PHP Syntax Check
```bash
php -l mod/facetoface/lib.php
php -l mod/facetoface/settings.php
php -l mod/facetoface/lang/en/facetoface.php
php -l mod/facetoface/tests/icalendar_privacy_test.php
```

All should return: "No syntax errors detected"

### Moodle Code Checker (if available)
```bash
# From Moodle root directory
php local/codechecker/cli/run.php --path=mod/facetoface
```

## Known Limitations
- The setting only affects newly generated iCalendar attachments
- Existing calendar events will retain their original privacy setting
- The default setting is "enabled" (CLASS:PRIVATE) for privacy by default
- If the setting has never been configured, it will default to PUBLIC (when get_config returns false)

## Troubleshooting

### Issue: No iCalendar attachment received
**Solution:** 
- Verify notification type is set to include iCalendar
- Check email configuration is working
- Ensure session has valid dates

### Issue: Setting doesn't seem to work
**Solution:**
- Clear Moodle caches: **Site administration** → **Development** → **Purge all caches**
- Verify you're testing with a NEW booking after changing the setting
- Check the generated .ics file directly, not just the calendar app import

### Issue: Tests fail
**Solution:**
- Ensure PHPUnit is properly initialized
- Check Moodle version compatibility (requires 4.3+)
- Verify all plugin files are properly installed

## Success Criteria
- ✅ Setting appears in Face-to-Face plugin settings under iCalendar section
- ✅ Default state is enabled (checkbox checked)
- ✅ When enabled, iCalendar attachments contain `CLASS:PRIVATE`
- ✅ When disabled, iCalendar attachments contain `CLASS:PUBLIC`
- ✅ PHPUnit tests pass
- ✅ No PHP syntax errors
- ✅ No console errors in Moodle
- ✅ Setting persists after page refresh
- ✅ Calendar applications correctly interpret the CLASS property

## Additional Notes
- This setting follows Moodle plugin development conventions
- Language strings are properly defined in lang/en/facetoface.php
- The implementation is minimal and non-breaking
- Backward compatible with existing functionality
- The feature respects the principle of "privacy by default" when setting is enabled
