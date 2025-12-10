<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\migrations;

use phpbb\db\migration\migration;

class add_popup_prompt extends migration
{
	public function effectively_installed(): bool
	{
		return $this->config->offsetExists('wpn_webpush_popup_prompt');
	}

	public static function depends_on()
	{
		return ['\phpbb\webpushnotifications\migrations\add_acp_configs'];
	}

	public function update_schema(): array
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'users' => [
					'user_wpn_popup_declined' => ['BOOL', 0],
				],
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'users' => [
					'user_wpn_popup_declined',
				],
			],
		];
	}

	public function update_data(): array
	{
		return [
			['config.add', ['wpn_webpush_popup_prompt', false]],
		];
	}

	public function revert_data(): array
	{
		return [
			['config.remove', ['wpn_webpush_popup_prompt']],
		];
	}
}
