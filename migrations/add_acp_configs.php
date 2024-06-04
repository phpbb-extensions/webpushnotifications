<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\migrations;

use phpbb\db\migration\migration;

class add_acp_configs extends migration
{
	public function effectively_installed(): bool
	{
		return $this->config->offsetExists('wpn_webpush_method_enabled');
	}

	public static function depends_on()
	{
		return ['\phpbb\webpushnotifications\migrations\add_webpush'];
	}

	public function update_data(): array
	{
		return [
			['config.add', ['wpn_webpush_method_enabled', false]],
			['config.add', ['wpn_webpush_dropdown_subscribe', false]],
		];
	}

	public function revert_data(): array
	{
		return [
			['config.remove', ['wpn_webpush_method_enabled']],
			['config.remove', ['wpn_webpush_dropdown_subscribe']],
		];
	}
}
