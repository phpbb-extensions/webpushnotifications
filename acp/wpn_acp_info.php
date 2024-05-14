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

class wpn_acp_info
{
	public function module()
	{
		return [
			'filename'	=> '\phpbb\webpushnotifications\acp\wpn_acp_module',
			'title'		=> 'ACP_WEBPUSH_EXT_SETTINGS',
			'modes'		=> [
				'webpush'	=> [
					'title' => 'ACP_WEBPUSH_EXT_SETTINGS',
					'auth' => 'ext_phpbb/webpushnotifications && acl_a_server',
					'cat' => ['ACP_CLIENT_COMMUNICATION']
				],
			],
		];
	}
}
