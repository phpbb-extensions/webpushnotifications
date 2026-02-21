# phpBB Browser Push Notifications

This is the repository for the development of the phpBB Browser Push Notifications extension.

[![Build Status](https://github.com/phpbb-extensions/webpushnotifications/actions/workflows/tests.yml/badge.svg)](https://github.com/phpbb-extensions/webpushnotifications/actions)

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

## Browser Support

| Web Browser         | Platform | Web Push Notification Support | Background Notification Support (When Browser Closed) |
|---------------------|----------|-------------------------------|-------------------------------------------------------|
| **Google Chrome**   | macOS    | ✅ Yes                         | ❌ No (unless running in the background)               |
|                     | Windows  | ✅ Yes                         | ❌ No (unless running in the background)               |
|                     | iOS      | ❌ No                          | ❌ No                                                  |
|                     | Android  | ✅ Yes                         | ✅ Yes                                                 |
| **Safari**          | macOS    | ✅ Yes                         | ✅ Yes                                                 |
|                     | iOS      | ✅ Yes                         | ✅ Yes (site must be added to Home Screen first)       |
| **Microsoft Edge**  | macOS    | ✅ Yes                         | ❌ No (unless running in the background)               |
|                     | Windows  | ✅ Yes                         | ❌ No (unless running in the background)               |
|                     | iOS      | ❌ No                          | ❌ No                                                  |
|                     | Android  | ✅ Yes                         | ✅ Yes                                                 |
| **Mozilla Firefox** | macOS    | ✅ Yes                         | ❌ No                                                  |
|                     | Windows  | ✅ Yes                         | ❌ No                                                  |
|                     | iOS      | ❌ No                          | ❌ No                                                  |
|                     | Android  | ✅ Yes                         | ❌ No                                                  |
| **Opera**           | macOS    | ✅ Yes                         | ❌ No                                                  |
|                     | Windows  | ✅ Yes                         | ❌ No                                                  |
|                     | iOS      | ❌ No                          | ❌ No                                                  |
|                     | Android  | ✅ Yes                         | ❌ No                                                  |

*(unless running in the background)* means the browsers have background processes running (they’re not fully quit).

More info here https://caniuse.com/push-api

## Testing Push Notifications

Testing push notifications necessitates user-to-user interactions to observe the notification behavior accurately. Follow the steps outlined below to effectively test push notifications (the browser recommendations are what we have seen work in local environments):

1. **User Account Setup:**
	- Create at least two distinct board user accounts for testing purposes.
    - Using Google Chrome, visit `UCP -> Board Preferences -> Edit notification options` for _**User Account 1**_ and enable Push Notifications (and enable all web push notification types if necessary). Your browser may ask you to allow notifications, which you should accept. Leave Chrome open and running the background.

2. **Message, Quote, or Reply Interaction:**
	- Initiate a user-to-user interaction by performing one of the following actions using _**User Account 2**_ in separate browser such as Firefox, Edge or Safari:
		- **Private Message:** Send a direct message from _**User Account 2**_ to _**User Account 1**_.
		- **Quote:** Quote a post or message authored by _**User Account 1**_ using _**User Account 2**_.
		- **Reply:** Respond to a post or message authored by _**User Account 1**_ using _**User Account 2**_.

3. **Observing Push Notifications:**
	- Once the interaction is performed from _**User Account 2**_ to engage with _**User Account 1**_, you promptly should see a notification from Google Chrome for _**User Account 1**_.

4. **Caveats for Local Testing**
    - Local testing of Push Notifications only works from a `http://localhost` address or if your local test server has a secure SSL certificate, e.g.: `https://local.phpbb.board`.
    - Depending on your local server's setup, operating system, and browsers, it is still possible that testing push notifications may not work (for example, in a local environment running on macOS, only Chrome will show notifications).

## License

[GNU General Public License v2](license.txt)
