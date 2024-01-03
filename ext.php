<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications;

/**
 * phpBB Browser Push Notifications Extension base
 */
class ext extends \phpbb\extension\base
{
	/**
	 * {@inheritdoc}
	 *
	 * Requires phpBB 3.3.12 due to new template and core events.
	 * Should not be installed in phpBB 4.0.0-a1 because it already has push notifications.
	 * Requires PHP 7.3 due to 3rd party libraries included.
	 */
	public function is_enableable()
	{
		return
			phpbb_version_compare(PHPBB_VERSION, '3.3.12-dev', '>=') &&
			phpbb_version_compare(PHPBB_VERSION, '4.0.0-a1', '<') &&
			phpbb_version_compare(PHP_VERSION_ID, '70300', '>=');
	}
}
