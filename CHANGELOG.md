# Changelog

### 1.0.0-RC5 - 2024-05-31

- New: Added an enable push subscriptions toggle switch to the footer of the Notifications drop down.
- New: New and existing users will have push notification preferences for PMs and being quoted in posts set by default. (Note this will not apply to existing users of boards that have installed a previous version of this extension.)
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
