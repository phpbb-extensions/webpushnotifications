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

class add_pwa_enhancer_schema extends migration
{
	public static function depends_on()
	{
		return ['\phpbb\webpushnotifications\migrations\add_pwa_enhancer_configs'];
	}

	public function effectively_installed(): bool
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'styles', 'pwa_bg_color');
	}

	public function update_schema(): array
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'styles'	=> [
					'pwa_bg_color'		=> ['VCHAR:8', ''],
					'pwa_theme_color'	=> ['VCHAR:8', ''],
				],
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'styles'	=> [
					'pwa_bg_color',
					'pwa_theme_color',
				],
			],
		];
	}
}
