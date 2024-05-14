# phpBB Browser Push Notifications

This is the repository for the development of the phpBB Browser Push Notifications extension.

[![Build Status](https://github.com/phpbb-extensions/webpushnotifications/workflows/Tests/badge.svg)](https://github.com/phpbb-extensions/webpushnotifications/actions)

An official phpBB extension that allows board users to receive browser-based push notifications.

**Important: Only official release versions validated by the phpBB Extensions Team should be installed on a live forum. Pre-release (beta, RC) versions downloaded from this repository are only to be used for testing on offline/development forums and are not officially supported.**

## Contributing

Please fork this repository and submit a pull request to contribute to this extension.

To run this extension from the repo (and not from a pre-built package) on a local server, perform the following tasks:

- Fork phpbb-extensions/webpushnotifications to your GitHub account, then create a local clone of it:
  ```bash
  git clone https://github.com/your_github_name/webpushnotifications.git
  ```
- Install this extension's dependencies (from the root of your webpushnotifications repo):
  ```bash
  cd webpushnotifications # navigate into the root of your webpushnotifications repo
  php composer.phar install # installs extension's 3rd-party dependencies 
  ```
- Install the extension to your local phpBB forum by moving your local repo to the proper phpBB directory:
  ```bash
  cd ../ # back out one directory level
  mv webpushnotifications path_to_phpBB/ext/phpbb # move webpushnotifications to your phpBB/ext/phpbb directory
  cd path_to_phpBB # navigate to your phpBB forum's root directory
  php bin/phpbbcli.php extension:enable phpbb/webpushnotifications # install the extension
  ```

## Testing Push Notifications

Testing push notifications necessitates user-to-user interactions to observe the notification behavior accurately. Follow the steps outlined below to effectively test push notifications:

1. **User Account Setup:**
	- Create at least two distinct user accounts for testing purposes.
    - In each user account's notifications preferences, assign web push notifications and subscribe to them to receive Push Notifications.

2. **Message, Quote, or Reply Interaction:**
	- Initiate a user-to-user interaction by performing one of the following actions using "User Account 1":
		- **Private Message:** Send a direct message from "User Account 1" to "User Account 2".
		- **Quote:** Quote a post or message authored by "User Account 2" using "User Account 1".
		- **Reply:** Respond to a post or message authored by "User Account 2" using "User Account 1".

3. **Observing Push Notifications:**
	- Once the interaction is performed from "User Account 1" to engage with "User Account 2," you promptly should see a notification for "User Account 2."

4. **Caveats for Local Testing**
    - Local testing of Push Notifications only works from a `localhost` address or if your local server has a secure SSL certificate.
    - We have seen success on Windows using manually installed PHP, Apache and MySQL. However, for reasons not yet known we do not see success on Mac using MAMP.

## License

[GNU General Public License v2](license.txt)
