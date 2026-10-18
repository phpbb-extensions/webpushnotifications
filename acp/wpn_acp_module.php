<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\acp;

use phpbb\webpushnotifications\controller\acp_controller;

class wpn_acp_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Main ACP module
	 *
	 * @param int $id
	 * @param string $mode
	 * @throws \Exception
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var acp_controller $controller */
		$controller = $phpbb_container->get('phpbb.wpn.acp.controller');
		$controller->set_page_url($this->u_action);

		if ($mode === 'webpush')
		{
			$this->tpl_name = 'wpn_acp_settings';
			$this->page_title = 'ACP_WEBPUSH_EXT_SETTINGS';
			$controller->handle_webpush();
		}
		else if ($mode === 'pwa')
		{
			$this->tpl_name = 'wpn_acp_pwa';
			$this->page_title = 'ACP_WEBPUSH_PWA_SETTINGS';
			$controller->handle_pwa();
		}
	}
}
