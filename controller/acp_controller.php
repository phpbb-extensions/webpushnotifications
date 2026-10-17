<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\controller;

use FastImageSize\FastImageSize;
use phpbb\cache\service as cache;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\request\request_interface;
use phpbb\symfony_request;
use phpbb\template\template;
use phpbb\user;
use phpbb\webpushnotifications\ext;

class acp_controller
{
	public const FORM_KEY = 'phpbb/webpushnotifications';

	/** @var cache */
	protected $cache;

	/** @var config $config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var language $lang */
	protected $lang;

	/** @var log_interface */
	protected $log;

	/** @var FastImageSize */
	protected $imagesize;

	/** @var request_interface */
	protected $request;

	/** @var symfony_request $symfony_request */
	protected $symfony_request;

	/** @var template $template */
	protected $template;

	/** @var user $user */
	protected $user;

	/** @var string */
	protected $root_path;

	/** @var array $errors */
	protected $errors = [];

	/** @var string */
	protected $u_action;

	/**
	 * Constructor
	 *
	 * @param cache $cache
	 * @param config $config
	 * @param driver_interface $db
	 * @param FastImageSize $imagesize
	 * @param language $lang
	 * @param log_interface $log
	 * @param request_interface $request
	 * @param symfony_request $symfony_request
	 * @param template $template
	 * @param user $user
	 * @param string $root_path
	 */
	public function __construct(
		cache $cache,
		config $config,
		driver_interface $db,
		FastImageSize $imagesize,
		language $lang,
		log_interface $log,
		request_interface $request,
		symfony_request $symfony_request,
		template $template,
		user $user,
		string $root_path
	)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->imagesize = $imagesize;
		$this->lang = $lang;
		$this->log = $log;
		$this->request = $request;
		$this->symfony_request = $symfony_request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
	}

	/**
	 * Set ACP page URL
	 *
	 * @param string $u_action
	 * @return acp_controller
	 */
	public function set_page_url(string $u_action): self
	{
		$this->u_action = $u_action;

		return $this;
	}

	/**
	 * Handle Web Push settings
	 */
	public function handle_webpush(): void
	{
		$this->prepare();

		if ($this->request->is_set_post('submit'))
		{
			$this->check_form_key();
			$this->save_settings();
		}

		$this->display_settings();
	}

	/**
	 * Handle PWA settings
	 */
	public function handle_pwa(): void
	{
		$this->prepare();

		if ($this->request->is_set_post('submit'))
		{
			$this->check_form_key();
			$this->save_pwa_settings();
		}

		$this->display_pwa_settings();
	}

	/**
	 * Prepare shared ACP settings state
	 */
	protected function prepare(): void
	{
		$this->lang->add_lang('webpushnotifications_module_acp', 'phpbb/webpushnotifications');
		add_form_key(self::FORM_KEY);
	}

	/**
	 * Validate ACP form key
	 */
	protected function check_form_key(): void
	{
		if (!check_form_key(self::FORM_KEY))
		{
			trigger_error($this->lang->lang('FORM_INVALID'), E_USER_WARNING);
		}
	}

	/**
	 * Add settings template vars to the form
	 */
	protected function display_settings()
	{
		$this->template->assign_vars([
			'S_WEBPUSH_ENABLE'				=> $this->config['wpn_webpush_enable'],
			'WEBPUSH_VAPID_PUBLIC'			=> $this->config['wpn_webpush_vapid_public'],
			'S_VAPID_KEYS_CONFIGURED'		=> $this->config['wpn_webpush_vapid_public'] && $this->config['wpn_webpush_vapid_private'],
			'S_VAPID_PRIVATE_CONFIGURED'	=> (bool) $this->config['wpn_webpush_vapid_private'],
			'S_WEBPUSH_DROPDOWN_SUBSCRIBE'	=> (bool) $this->config['wpn_webpush_dropdown_subscribe'],
			'S_WEBPUSH_METHOD_ENABLED' 		=> (bool) $this->config['wpn_webpush_method_enabled'],
			'S_WEBPUSH_POPUP_PROMPT'		=> (bool) $this->config['wpn_webpush_popup_prompt'],
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
	protected function save_settings()
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

		// An empty private key keeps the stored credential. Stored private keys are never sent to the browser.
		if (($config_array['wpn_webpush_vapid_private'] ?? '') === '' && $this->config['wpn_webpush_vapid_private'])
		{
			unset($display_settings['wpn_webpush_vapid_private'], $config_array['wpn_webpush_vapid_private']);
		}

		if (!empty($config_array['wpn_webpush_enable']))
		{
			// Validate config values
			validate_config_vars($display_settings, $config_array, $this->errors);
		}

		if ($this->display_errors())
		{
			return;
		}

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_WEBPUSH');

		foreach (array_keys($display_settings) as $config_name)
		{
			if (array_key_exists($config_name, $config_array))
			{
				$this->config->set($config_name, $config_array[$config_name]);
			}
		}

		trigger_error($this->lang->lang('CONFIG_UPDATED') . adm_back_link($this->u_action), E_USER_NOTICE);
	}

	/**
	 * Add PWA settings template vars to the form
	 */
	protected function display_pwa_settings()
	{
		$this->template->assign_vars([
			'S_PWA_SHOW_INSTALL_BANNER'	=> (bool) $this->config['pwa_show_install_banner'],
			'PWA_SHORT_NAME'			=> $this->config['pwa_short_name'],
			'PWA_SHORT_NAME_PLACEHOLDER'=> utf8_substr(ext::decode_entities($this->config['sitename'], ENT_QUOTES), 0, 12),
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
	protected function save_pwa_settings()
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

			$update = [
				'pwa_bg_color'		=> $this->validate_hex_color($pwa_bg_color) ? $pwa_bg_color : $row['pwa_bg_color'],
				'pwa_theme_color'	=> $this->validate_hex_color($pwa_theme_color) ? $pwa_theme_color : $row['pwa_theme_color'],
			];

			if ($update['pwa_bg_color'] !== $row['pwa_bg_color'] || $update['pwa_theme_color'] !== $row['pwa_theme_color'])
			{
				$updates[$style_id] = $update;
			}
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
	protected function display_errors()
	{
		$has_errors = (bool) count($this->errors);

		$this->template->assign_vars([
			'S_ERROR'	=> $has_errors,
			'ERROR_MSG'	=> $has_errors ? implode('<br>', $this->errors) : '',
		]);

		return $has_errors;
	}
}
