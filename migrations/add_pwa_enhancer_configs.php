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

class add_pwa_enhancer_configs extends migration
{
	public function effectively_installed(): bool
	{
		return $this->config->offsetExists('pwa_theme_colour');
	}

	public static function depends_on()
	{
		return ['\phpbb\webpushnotifications\migrations\add_acp_pwa_configs'];
	}

	public function update_data(): array
	{
		return [
			['config.add', ['pwa_theme_colour', '#000000']],
			['config.add', ['pwa_background_colour', '#ffffff']],
			['config.add', ['pwa_show_install_banner', false]],
			['module.add', ['acp', 'ACP_CLIENT_COMMUNICATION', [
				'module_basename'	=> '\phpbb\webpushnotifications\acp\wpn_acp_module',
				'module_langname'	=> 'ACP_WEBPUSH_PWA_SETTINGS',
				'module_mode'		=> 'pwa',
				'module_auth'		=> 'ext_phpbb/webpushnotifications && acl_a_board',
				'after'				=> 'ACP_WEBPUSH_EXT_SETTINGS',
			]]],
		];
	}

	public function revert_data(): array
	{
		return [
			['config.remove', ['pwa_theme_colour']],
			['config.remove', ['pwa_background_colour']],
			['config.remove', ['pwa_show_install_banner']],
			['module.remove', ['acp', 'ACP_CLIENT_COMMUNICATION', [
				'module_basename'	=> '\phpbb\webpushnotifications\acp\wpn_acp_module',
				'module_langname'	=> 'ACP_WEBPUSH_PWA_SETTINGS',
				'module_mode'		=> 'pwa',
			]]],
		];
	}
}
