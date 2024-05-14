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

class add_webpush extends migration
{
	public function effectively_installed(): bool
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'wpn_notification_push');
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v33x\v3312rc1'];
	}

	public function update_schema(): array
	{
		return [
			'add_tables'	=> [
				$this->table_prefix . 'wpn_notification_push' => [
					'COLUMNS'	=> [
						'notification_type_id'	=> ['USINT', 0],
						'item_id'				=> ['ULINT', 0],
						'item_parent_id'		=> ['ULINT', 0],
						'user_id'				=> ['ULINT', 0],
						'push_data'				=> ['MTEXT', ''],
						'notification_time'		=> ['TIMESTAMP', 0]
					],
					'PRIMARY_KEY' => ['notification_type_id', 'item_id', 'item_parent_id', 'user_id'],
				],
				$this->table_prefix . 'wpn_push_subscriptions' => [
					'COLUMNS'	=> [
						'subscription_id'	=> ['ULINT', null, 'auto_increment'],
						'user_id'			=> ['ULINT', 0],
						'endpoint'			=> ['TEXT', ''],
						'expiration_time'	=> ['TIMESTAMP', 0],
						'p256dh'			=> ['VCHAR', ''],
						'auth'				=> ['VCHAR', ''],
					],
					'PRIMARY_KEY' => ['subscription_id', 'user_id'],
				]
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'wpn_notification_push',
				$this->table_prefix . 'wpn_push_subscriptions',
			],
		];
	}

	public function update_data(): array
	{
		return [
			['config.add', ['wpn_webpush_enable', false]],
			['config.add', ['wpn_webpush_vapid_public', '']],
			['config.add', ['wpn_webpush_vapid_private', '']],
			['module.add', ['acp', 'ACP_CLIENT_COMMUNICATION', [
				'module_basename'	=> '\phpbb\webpushnotifications\acp\wpn_acp_module',
				'module_langname'	=> 'ACP_WEBPUSH_EXT_SETTINGS',
				'module_mode'		=> 'webpush',
				'after'				=> 'ACP_JABBER_SETTINGS',
			]]],
		];
	}

	public function revert_data(): array
	{
		return [
			['config.remove', ['wpn_webpush_enable']],
			['config.remove', ['wpn_webpush_vapid_public']],
			['config.remove', ['wpn_webpush_vapid_private']],
			['module.remove', ['acp', 'ACP_BOARD_CONFIGURATION', 'ACP_WEBPUSH_EXT_SETTINGS']]
		];
	}
}
