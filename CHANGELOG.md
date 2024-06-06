# Changelog

### 1.0.0 - 2024-06-05

- First official release.

### 1.0.0-RC8 - 2024-06-05

- Fixed (for real this time) an issue where push notifications failed on Android devices using Firefox. 
- Tweaked padding in the notification dropdown subscribe button to align with header elements.

### 1.0.0-RC7 - 2024-06-03

- New: Added an ACP option to show or hide the push subscriptions toggle switch in the footer of the Notifications drop down.
- New: Added an ACP option to make all user-based web push notification settings enabled or disabled by default. With this option enabled, users need only subscribe to start getting notifications. With this option disabled, users who subscribe will still need to visit the UCP and enable the notifications they want to receive.
- Rewrote the fix for the issue with the Push Service Worker not updating in a user's browser after an extension update.
- Fixed some internal code issues detected by the EPV.

### 1.0.0-RC6 - 2024-06-01

- New: Added an enable push subscriptions toggle switch to the footer of the Notifications drop down.
- Fixed an issue where the Subscribe button was appearing on some UCP pages unexpectedly.
- Fixed an issue where push notifications failed on Android devices using Firefox as their browser.
- Requires phpBB 3.3.12 or newer.

### 1.0.0-RC4 - 2024-05-26

- Fixed an issue with the Push Service Worker not updating in a user's browser after an extension update.
- Made sure all extension template files are properly namespaced.

### 1.0.0-RC3 - 2024-05-16

- Fixed an issue with user avatars not being displayed in the push notifications.
- Added Russian translation.

### 1.0.0-RC2 - 2024-05-14

- NOTE: If upgrading from RC1 to RC2, you must fully uninstall (disable and purge) RC1 and install RC2 as a fresh installation.
- Fixed an issue when uninstalling the extension that resulted in migration failures due to running migration files in the wrong order.
- Fixed an issue related to push URL paths so subscription data will be maintained after upgrading a forum to phpBB 4.

### 1.0.0-RC1 - 2024-05-12

- First public release
