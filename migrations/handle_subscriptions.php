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

use phpbb\db\migration\migration;

class handle_subscriptions extends migration
{
	public function effectively_installed()
	{
		return !$this->db_tools->sql_table_exists($this->table_prefix . 'wpn_notification_push');
	}

	public function revert_data(): array
	{
		return [
			['custom', [[$this, 'update_subscriptions']]],
			['if', [
				($this->db_tools->sql_table_exists($this->table_prefix . 'notification_push')),
				['custom', [[$this, 'copy_subscription_tables']]],
			]],
			['if', [
				(isset($this->config['webpush_enable'])),
				['config.update', ['webpush_enable', $this->config['wpn_webpush_enable']]],
			]],
			['if', [
				(isset($this->config['webpush_vapid_public'])),
				['config.update', ['webpush_vapid_public', $this->config['wpn_webpush_vapid_public']]],
			]],
			['if', [
				(isset($this->config['webpush_vapid_private'])),
				['config.update', ['webpush_vapid_private', $this->config['wpn_webpush_vapid_private']]],
			]],
		];
	}

	public function update_subscriptions()
	{
		$user_notifications_table = $this->table_prefix . 'user_notifications';

		// Check if webpush notification method exists in phpBB core (as of phpBB 4.0)
		$core_webpush_exists = $this->db_tools->sql_table_exists($this->table_prefix . 'notification_push');

		/*
		 * If webpush notification method exists in phpBB core,
		 * keep all subscriptions by just renaming notification method.
		 * Otherwise remove all subscriptions
		 */
		$sql = $core_webpush_exists ?
			'UPDATE ' . $user_notifications_table . "
				SET method = '" . $this->db->sql_escape('notification.method.webpush') . "'
				WHERE method = '" . $this->db->sql_escape('notification.method.phpbb.wpn.webpush')  . "'" :

			'DELETE FROM ' . $user_notifications_table . "
				WHERE method = '" . $this->db->sql_escape('notification.method.phpbb.wpn.webpush') . "'";

		$this->db->sql_query($sql);
	}

	public function copy_subscription_tables()
	{
		$core_notification_push_table = $this->table_prefix . 'notification_push';
		$core_push_subscriptions_table = $this->table_prefix . 'push_subscriptions';

		$wpn_notification_push_table = $this->table_prefix . 'wpn_notification_push';
		$wpn_push_subscriptions_table = $this->table_prefix . 'wpn_push_subscriptions';

		/*
		 * If webpush notification method exists in phpBB core,
		 * copy all subscriptions data over the corresponding core tables.
		 */
		foreach ([
				$core_notification_push_table => $wpn_notification_push_table,
				$core_push_subscriptions_table => $wpn_push_subscriptions_table
			] as $core_table => $ext_table)
		{
			$sql = 'INSERT INTO ' . $core_table . '
					SELECT * FROM ' . $ext_table;
			$this->db->sql_query($sql);
		}
	}
}
