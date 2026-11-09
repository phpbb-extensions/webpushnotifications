<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\migrations;

use phpbb\db\migration\migration;

class add_pwa_metadata_toggle extends migration
{
	public function effectively_installed(): bool
	{
		return $this->config->offsetExists('pwa_metadata_enabled');
	}

	public static function depends_on()
	{
		return ['\phpbb\webpushnotifications\migrations\add_pwa_enhancer_schema'];
	}

	public function update_data(): array
	{
		return [
			['config.add', ['pwa_metadata_enabled', 1]],
		];
	}

	public function revert_data(): array
	{
		return [
			['config.remove', ['pwa_metadata_enabled']],
		];
	}
}
