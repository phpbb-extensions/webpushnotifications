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

## License

[GNU General Public License v2](license.txt)
