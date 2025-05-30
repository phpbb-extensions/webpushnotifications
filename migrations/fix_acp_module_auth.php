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

class fix_acp_module_auth extends migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . $this->table_prefix . "modules
			WHERE module_class = 'acp'
				AND module_langname = 'ACP_WEBPUSH_EXT_SETTINGS'
				AND module_auth = 'ext_phpbb/webpushnotifications && acl_a_server'";
		$result = $this->db->sql_query($sql);
		$module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id !== false;
	}

	public static function depends_on()
	{
		return ['\phpbb\webpushnotifications\migrations\add_webpush'];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'set_acp_module_auth']]],
		];
	}

	public function set_acp_module_auth()
	{
		$phpbb_modules_table = $this->table_prefix . 'modules';
		$sql = 'UPDATE ' . $phpbb_modules_table . "
			SET module_auth = '" . $this->db->sql_escape('ext_phpbb/webpushnotifications && acl_a_server') . "'
			WHERE module_langname = '" . $this->db->sql_escape('ACP_WEBPUSH_EXT_SETTINGS')  . "'";
		$this->db->sql_query($sql);
	}
}
