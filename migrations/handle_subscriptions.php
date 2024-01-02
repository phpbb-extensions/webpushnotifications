<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\migrations;

use phpbb\db\migration\container_aware_migration;

class handle_subscriptions extends container_aware_migration
{
	public function revert_data(): array
	{
		return [
			['custom', [[$this, 'update_subscriptions']]],
		];
	}

	public function update_subscriptions()
	{
		$user_notifications_table = $this->table_prefix . 'user_notifications';

		// Check if webpush notification method exists in phpBB core (as of phpBB 4.0)
		$core_webpush_exists = $this->container->has('notification.method.webpush');

		/*
		 * If webpush notification method exists in phpBB core,
		 * keep all subscriptions by just renaming notification method.
		 * Otherwise remove all subscriptions
		 */
		$sql = $core_webpush_exists ?
			'UPDATE ' . $user_notifications_table . "
				SET method = '" . $this->db->sql_escape('notification.method.webpush') . "'
				WHERE method = '" . $this->db->sql_escape('phpbb.wpn.notification.method.webpush')  . "'" :

			'DELETE FROM ' . $user_notifications_table . "
				WHERE method = '" . $this->db->sql_escape('phpbb.wpn.notification.method.webpush') . "'";

		$this->db->sql_query($sql);
	}
}
