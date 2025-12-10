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
	public function effectively_installed()
	{
		return $this->config->offsetExists('wpn_webpush_popup_prompt');
	}

	public static function depends_on()
	{
		return ['\phpbb\webpushnotifications\migrations\add_acp_configs'];
	}

	public function update_data()
	{
		return [
			['config.add', ['wpn_webpush_popup_prompt', 0]],
		];
	}
}
