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
use phpbb\symfony_request;
use phpbb\template\template;
use phpbb\user;
use phpbb\webpushnotifications\ext;

class wpn_acp_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var config $config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var language $lang */
	protected $lang;

	/** @var log $log */
	protected $log;

	/** @var \FastImageSize\FastImageSize $imagesize */
	protected $imagesize;

	/** @var request $request */
	protected $request;

	/** @var symfony_request $symfony_request */
	private $symfony_request;

	/** @var template $template */
	protected $template;

	/** @var user $user */
	protected $user;

	/** @var string */
	protected $root_path;

	/** @var array $errors */
	protected $errors = [];

	/** @var string Hide/replace private key with asterisks */
	public const MASKED_PRIVATE_KEY = '********';

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

		$this->cache = $phpbb_container->get('cache');
		$this->config = $phpbb_container->get('config');
		$this->db = $phpbb_container->get('dbal.conn');
		$this->imagesize = $phpbb_container->get('upload_imagesize');
		$this->lang = $phpbb_container->get('language');
		$this->log = $phpbb_container->get('log');
		$this->request = $phpbb_container->get('request');
		$this->symfony_request = $phpbb_container->get('symfony_request');
		$this->template = $phpbb_container->get('template');
		$this->user = $phpbb_container->get('user');
		$this->root_path = $phpbb_container->getParameter('core.root_path');

		$form_key = 'phpbb/webpushnotifications';
		add_form_key($form_key);

		if ($mode === 'webpush')
		{
			// Load a template from adm/style for our ACP page
			$this->tpl_name = 'wpn_acp_settings';

			$this->lang->add_lang('webpushnotifications_module_acp', 'phpbb/webpushnotifications');

			// Set the page title for our ACP page
			$this->page_title = $this->lang->lang('ACP_WEBPUSH_EXT_SETTINGS');

			if ($this->request->is_set_post('submit'))
			{
				if (!check_form_key($form_key))
				{
					trigger_error($this->lang->lang('FORM_INVALID'), E_USER_WARNING);
				}

				$this->save_settings();
			}

			$this->display_settings();
		}
		else if ($mode === 'pwa')
		{
			$this->tpl_name = 'wpn_acp_pwa';

			$this->lang->add_lang('webpushnotifications_module_acp', 'phpbb/webpushnotifications');

			$this->page_title = $this->lang->lang('ACP_WEBPUSH_PWA_SETTINGS');

			if ($this->request->is_set_post('submit'))
			{
				if (!check_form_key($form_key))
				{
					trigger_error($this->lang->lang('FORM_INVALID'), E_USER_WARNING);
				}

				$this->save_pwa_settings();
			}

			$this->display_pwa_settings();
		}
	}

	/**
	 * Add settings template vars to the form
	 */
	public function display_settings()
	{
		$this->template->assign_vars([
			'S_WEBPUSH_ENABLE'				=> $this->config['wpn_webpush_enable'],
			'WEBPUSH_VAPID_PUBLIC'			=> $this->config['wpn_webpush_vapid_public'],
			'WEBPUSH_VAPID_PRIVATE'			=> $this->config['wpn_webpush_vapid_private'] ? self::MASKED_PRIVATE_KEY : '',
			'S_WEBPUSH_DROPDOWN_SUBSCRIBE'	=> $this->config['wpn_webpush_dropdown_subscribe'],
			'S_WEBPUSH_METHOD_ENABLED' 		=> $this->config['wpn_webpush_method_enabled'],
			'S_WEBPUSH_POPUP_PROMPT'		=> $this->config['wpn_webpush_popup_prompt'],
			'U_ACTION'						=> $this->u_action,
		]);

		if (!$this->symfony_request->isSecure() && $this->request->server('SERVER_NAME') !== 'localhost')
		{
			$this->errors[] = $this->lang->lang('WEBPUSH_INSECURE_SERVER_ERROR');
		}

		$this->display_errors();
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
			'wpn_webpush_dropdown_subscribe' => ['validate' => 'bool'],
			'wpn_webpush_method_enabled' => ['validate' => 'bool'],
			'wpn_webpush_popup_prompt' => ['validate' => 'bool'],
		];

		// Do not validate and update private key field if the content is ******** and the key was already set
		if ($config_array['wpn_webpush_vapid_private'] === self::MASKED_PRIVATE_KEY && $this->config['wpn_webpush_vapid_private'])
		{
			unset($display_settings['wpn_webpush_vapid_private'], $config_array['wpn_webpush_vapid_private']);
		}

		if ($config_array['wpn_webpush_enable'])
		{
			// Validate config values
			validate_config_vars($display_settings, $config_array, $this->errors);
		}

		if ($this->display_errors())
		{
			return;
		}

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_WEBPUSH');

		foreach ($config_array as $config_name => $config_value)
		{
			$this->config->set($config_name, $config_value);
		}

		trigger_error($this->lang->lang('CONFIG_UPDATED') . adm_back_link($this->u_action), E_USER_NOTICE);
	}

	/**
	 * Add PWA settings template vars to the form
	 */
	public function display_pwa_settings()
	{
		$this->template->assign_vars([
			'S_PWA_SHOW_INSTALL_BANNER'	=> (bool) $this->config['pwa_show_install_banner'],
			'PWA_SHORT_NAME'			=> $this->config['pwa_short_name'],
			'PWA_ICON_SMALL'			=> $this->config['pwa_icon_small'],
			'PWA_ICON_LARGE'			=> $this->config['pwa_icon_large'],
			'STYLES'					=> $this->get_styles(),
			'U_ACTION'					=> $this->u_action,
		]);

		$this->display_errors();
	}

	/**
	 * Save PWA settings data to the database
	 *
	 * @return void
	 */
	public function save_pwa_settings()
	{
		$config_array = $this->request->variable('config', ['' => ''], true);

		$config_array['pwa_short_name'] = $config_array['pwa_short_name'] ?? '';
		$config_array['pwa_icon_small'] = $config_array['pwa_icon_small'] ?? '';
		$config_array['pwa_icon_large'] = $config_array['pwa_icon_large'] ?? '';

		$this->validate_pwa_short_name($config_array['pwa_short_name']);
		$this->validate_pwa_icons($config_array['pwa_icon_small'], $config_array['pwa_icon_large']);

		$styles = $this->get_styles();
		$updates = [];
		foreach ($styles as $row)
		{
			$style_id			= $row['style_id'];
			$pwa_bg_color		= $this->request->variable('pwa_bg_color_' . $style_id, '');
			$pwa_theme_color	= $this->request->variable('pwa_theme_color_' . $style_id, '');

			$updates[$style_id] = [
				'pwa_bg_color'		=> $this->validate_hex_color($pwa_bg_color) ? $pwa_bg_color : $row['pwa_bg_color'],
				'pwa_theme_color'	=> $this->validate_hex_color($pwa_theme_color) ? $pwa_theme_color : $row['pwa_theme_color'],
			];
		}

		if ($this->display_errors())
		{
			return;
		}

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_WEBPUSH');

		// Ensure 4-byte emoji can be stored correctly
		$config_array['pwa_short_name'] = utf8_encode_ucr($config_array['pwa_short_name']);

		foreach ([
			'pwa_short_name',
			'pwa_icon_small',
			'pwa_icon_large',
			'pwa_show_install_banner',
		] as $config_name)
		{
			$this->config->set($config_name, $config_array[$config_name] ?? 0);
		}

		$this->set_styles($updates);

		trigger_error($this->lang->lang('CONFIG_UPDATED') . adm_back_link($this->u_action), E_USER_NOTICE);
	}

	/**
	 * Validate PWA short site name
	 */
	protected function validate_pwa_short_name(string $short_name): void
	{
		if ($short_name === '')
		{
			return;
		}

		$short_name = ext::decode_entities($short_name, ENT_QUOTES);
		if (utf8_strlen($short_name) > 12)
		{
			$this->errors[] = $this->lang->lang('PWA_SHORT_NAME_INVALID');
		}
	}

	/**
	 * Validate PWA icon filenames and dimensions
	 */
	protected function validate_pwa_icons(string $small_icon, string $large_icon): void
	{
		if ($small_icon === '' && $large_icon === '')
		{
			return;
		}

		if ($small_icon === '')
		{
			$this->errors[] = $this->lang->lang('PWA_ICON_NOT_PROVIDED', $this->lang->lang('PWA_ICON_SMALL'));
			return;
		}

		if ($large_icon === '')
		{
			$this->errors[] = $this->lang->lang('PWA_ICON_NOT_PROVIDED', $this->lang->lang('PWA_ICON_LARGE'));
			return;
		}

		$this->validate_pwa_icon($small_icon, 192);
		$this->validate_pwa_icon($large_icon, 512);
	}

	/**
	 * Validate one PWA icon file
	 */
	protected function validate_pwa_icon(string $filename, int $size): void
	{
		if (basename($filename) !== $filename)
		{
			$this->errors[] = $this->lang->lang('PWA_ICON_INVALID', $filename);
			return;
		}

		$image = $this->root_path . ext::PWA_ICON_DIR . '/' . $filename;
		$image_info = $this->imagesize->getImageSize($image);
		if ($image_info === false)
		{
			$this->errors[] = $this->lang->lang('PWA_ICON_INVALID', $filename);
			return;
		}

		if ($image_info['width'] !== $size || $image_info['height'] !== $size)
		{
			$this->errors[] = $this->lang->lang('PWA_ICON_SIZE_INVALID', $filename);
		}

		if ($image_info['type'] !== IMAGETYPE_PNG)
		{
			$this->errors[] = $this->lang->lang('PWA_ICON_MIME_INVALID', $filename);
		}
	}

	/**
	 * Validate HTML color hex codes
	 */
	protected function validate_hex_color(string $code): bool
	{
		$code = trim($code);

		if ($code === '')
		{
			return true;
		}

		$test = (bool) preg_match('/^#([0-9A-F]{3}){1,2}$/i', $code);

		if ($test === false)
		{
			$this->errors[] = $this->lang->lang('PWA_INVALID_COLOUR', $code);
		}

		return $test;
	}

	/**
	 * Get style data from the styles table
	 *
	 * @return array Style data
	 */
	protected function get_styles(): array
	{
		$sql = 'SELECT style_id, style_name, pwa_bg_color, pwa_theme_color
			FROM ' . STYLES_TABLE . '
			WHERE style_active = 1
			ORDER BY style_name';
		$result = $this->db->sql_query($sql);

		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Set style data in the styles table
	 *
	 * @param array $rows Array of style table data to update; style_id is key
	 * @return void
	 */
	protected function set_styles(array $rows): void
	{
		if (!empty($rows))
		{
			$this->db->sql_transaction('begin');

			foreach ($rows as $style_id => $row)
			{
				$sql = 'UPDATE ' . STYLES_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $row) . '
					WHERE style_id = ' . (int) $style_id;
				$this->db->sql_query($sql);
			}

			$this->db->sql_transaction('commit');

			$this->cache->destroy('sql', STYLES_TABLE);
		}
	}

	/**
	 * Display any errors
	 *
	 * @return bool
	 */
	public function display_errors()
	{
		$has_errors = (bool) count($this->errors);

		$this->template->assign_vars([
			'S_ERROR'	=> $has_errors,
			'ERROR_MSG'	=> $has_errors ? implode('<br>', $this->errors) : '',
		]);

		return $has_errors;
	}
}
