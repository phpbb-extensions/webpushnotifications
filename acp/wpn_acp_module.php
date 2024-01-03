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

use phpbb\config\config;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;

class wpn_acp_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	/** @var config $config */
	protected $config;

	/** @var language $lang */
	protected $lang;

	/** @var log $log */
	protected $log;

	/** @var request $request */
	protected $request;

	/** @var template $template */
	protected $template;

	/** @var user $user */
	protected $user;

	/** @var array $errors */
	protected $errors = [];

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

		$this->config = $phpbb_container->get('config');
		$this->lang = $phpbb_container->get('language');
		$this->log = $phpbb_container->get('log');
		$this->request = $phpbb_container->get('request');
		$this->template = $phpbb_container->get('template');
		$this->user = $phpbb_container->get('user');

		$form_key = 'phpbb/webpushnotifications';
		add_form_key($form_key);

		if ($mode === 'webpush')
		{
			// Load a template from adm/style for our ACP page
			$this->tpl_name = 'wpn_acp_settings';

			$this->lang->add_lang('webpushnotifications_module_acp', 'phpbb/webpushnotifications');

			// Set the page title for our ACP page
			$this->page_title = $this->lang->lang('ACP_WEBPUSH_SETTINGS');

			if ($this->request->is_set_post('submit'))
			{
				if (!check_form_key($form_key))
				{
					$language = $phpbb_container->get('language');
					trigger_error($language->lang('FORM_INVALID'), E_USER_WARNING);
				}

				$this->save_settings();
			}

			$this->display_settings();
		}
	}

	/**
	 * Add settings template vars to the form
	 */
	public function display_settings()
	{
		$this->template->assign_vars([
			'S_WEBPUSH_ENABLE'		=> $this->config['wpn_webpush_enable'],
			'WEBPUSH_VAPID_PUBLIC'	=> $this->config['wpn_webpush_vapid_public'],
			'WEBPUSH_VAPID_PRIVATE'	=> $this->config['wpn_webpush_vapid_private'],
			'U_ACTION'				=> $this->u_action,
		]);
	}

	/**
	 * Save settings data to the database
	 *
	 * @return void
	 */
	public function save_settings()
	{
		$config_array = $this->request->variable('config', ['' => ''], true);
		$display_settings = [
			'wpn_webpush_enable' => ['validate' => 'bool'],
			'wpn_webpush_vapid_public' => ['validate' => 'string:25:255', 'lang' => 'WEBPUSH_VAPID_PUBLIC'],
			'wpn_webpush_vapid_private'=> ['validate' => 'string:25:255', 'lang' => 'WEBPUSH_VAPID_PRIVATE'],
		];

		if ($config_array['wpn_webpush_enable'])
		{
			// Validate config values
			validate_config_vars($display_settings, $config_array, $this->errors);
		}

		if (count($this->errors))
		{
			$this->template->assign_vars([
				'S_ERROR'			=> (bool) count($this->errors),
				'ERROR_MSG'			=> implode('<br>', $this->errors),
			]);

			return;
		}

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_WEBPUSH');

		$this->config->set('wpn_webpush_enable', $config_array['wpn_webpush_enable']);
		$this->config->set('wpn_webpush_vapid_public', $config_array['wpn_webpush_vapid_public']);
		$this->config->set('wpn_webpush_vapid_private', $config_array['wpn_webpush_vapid_private']);

		trigger_error($this->lang->lang('CONFIG_UPDATED') . adm_back_link($this->u_action), E_USER_NOTICE);
	}
}
