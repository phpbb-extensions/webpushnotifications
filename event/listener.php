<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\event;

use FastImageSize\FastImageSize;
use phpbb\config\config;
use phpbb\controller\helper as controller_helper;
use phpbb\language\language;
use phpbb\notification\manager;
use phpbb\template\template;
use phpbb\user;
use phpbb\webpushnotifications\form\form_helper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	/** @var config */
	protected $config;

	/* @var controller_helper */
	protected $controller_helper;

	/* @var form_helper */
	protected $form_helper;

	/** @var FastImageSize */
	protected $imagesize;

	/* @var language */
	protected $language;

	/* @var template */
	protected $template;

	/** @var user */
	protected $user;

	/* @var manager */
	protected $phpbb_notifications;

	/** @var string */
	protected $root_path;

	/**
	 * Constructor
	 *
	 * @param config $config
	 * @param controller_helper $controller_helper Controller helper object
	 * @param FastImageSize $imagesize
	 * @param form_helper $form_helper Form helper object
	 * @param language $language Language object
	 * @param template $template Template object
	 * @param user $user
	 * @param manager $phpbb_notifications Notifications manager object
	 * @param $root_path
	 */
	public function __construct(config $config, controller_helper $controller_helper, FastImageSize $imagesize, form_helper $form_helper, language $language, template $template, user $user, manager $phpbb_notifications, $root_path)
	{
		$this->config = $config;
		$this->controller_helper = $controller_helper;
		$this->imagesize = $imagesize;
		$this->form_helper = $form_helper;
		$this->language = $language;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_notifications = $phpbb_notifications;
		$this->root_path = $root_path;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.page_header_after'			=> [['load_template_data'], ['pwa_manifest']],
			'core.ucp_display_module_before'	=> 'load_language',
			'core.acp_main_notice'				=> 'compatibility_notice',
			'core.acp_board_config_edit_add'		=> 'acp_pwa_options',
			'core.validate_config_variable'		=> 'validate_pwa_options',
			'core.help_manager_add_block_after'	=> 'wpn_faq',
		];
	}

	/**
	 * Load template data
	 */
	public function load_template_data()
	{
		if (!$this->can_use_notifications())
		{
			return;
		}

		$methods = $this->phpbb_notifications->get_subscription_methods();
		$webpush_method = $methods['notification.method.phpbb.wpn.webpush'] ?? null;

		if ($webpush_method === null)
		{
			return;
		}

		if (!$this->language->is_set('NOTIFICATION_METHOD_PHPBB_WPN_WEBPUSH'))
		{
			$this->load_language();
		}

		$template_ary = $webpush_method['method']->get_ucp_template_data($this->controller_helper, $this->form_helper);
		$this->template->assign_vars($template_ary);
	}

	/**
	 * Load language file (this is required for the UCP)
	 */
	public function load_language()
	{
		$this->language->add_lang('webpushnotifications_module_ucp', 'phpbb/webpushnotifications');
	}

	/**
	 * Check if extension is compatible (it will not be compatible with phpBB 4)
	 */
	public function compatibility_notice()
	{
		$this->template->assign_var('S_WPN_COMPATIBILITY_NOTICE', phpbb_version_compare(PHPBB_VERSION, '4.0.0-dev', '>='));
	}

	/**
	 * Assign template data for web manifest support
	 *
	 * @return void
	 */
	public function pwa_manifest()
	{
		$this->template->assign_vars([
			'U_MANIFEST_URL'	=> $this->controller_helper->route('phpbb_webpushnotifications_manifest_controller'),
			'U_TOUCH_ICON'		=> $this->config['pwa_icon_small'],
			'SHORT_SITE_NAME'	=> $this->config['pwa_short_name'] ?: $this->get_shortname($this->config['sitename']),
		]);
	}

	/**
	 * Progressive web app options for the ACP
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function acp_pwa_options($event)
	{
		if ($event['mode'] === 'settings' && array_key_exists('legend4', $event['display_vars']['vars']))
		{
			$this->language->add_lang('webpushnotifications_common_acp', 'phpbb/webpushnotifications');

			$my_config_vars = [
				'legend_pwa_settings'=> 'PWA_SETTINGS',
				'pwa_short_name'	=> ['lang' => 'PWA_SHORT_NAME', 'validate' => 'pwa_options:string', 'type' => 'custom', 'function' => [$this, 'pwa_short_sitename'], 'explain' => true],
				'pwa_icon_small'	=> ['lang' => 'PWA_ICON_SMALL', 'validate' => 'pwa_options:icons', 'type' => 'custom', 'function' => [$this, 'pwa_icon_name'], 'explain' => true],
				'pwa_icon_large'	=> ['lang' => 'PWA_ICON_LARGE', 'validate' => 'pwa_options:icons', 'type' => 'custom', 'function' => [$this, 'pwa_icon_name'], 'explain' => true],
			];

			$event->update_subarray('display_vars', 'vars', phpbb_insert_config_array($event['display_vars']['vars'], $my_config_vars, ['before' => 'legend4']));
		}
	}

	/**
	 * Return HTML for PWA icon name settings
	 *
	 * @param string $value Value of config
	 * @param string $key Name of config
	 * @return string
	 */
	public function pwa_icon_name($value, $key)
	{
		return $this->config['icons_path'] . '/<input id="' . $key . '" type="text" size="40" maxlength="255" name="config[' . $key . ']" value="' . $value . '">';
	}

	/**
	 * Return HTML for PWA short site name setting
	 *
	 * @param string $value Value of config
	 * @param string $key Name of config
	 * @return string
	 */
	public function pwa_short_sitename($value, $key)
	{
		$placeholder = $this->get_shortname($this->config['sitename']);

		return '<input id="' . $key . '" type="text" size="40" maxlength="12" name="config[' . $key . ']" value="' . $value . '" placeholder="' . $placeholder . '">';
	}

	/**
	 * Validate PWA options
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function validate_pwa_options($event)
	{
		$type	= 0;
		$mode	= 1;

		$validator = explode(':', $event['config_definition']['validate']);

		if ($validator[$type] !== 'pwa_options')
		{
			return;
		}

		switch ($validator[$mode])
		{
			case 'string':
				// Ignore validation if icon fields are empty
				if (empty($event['cfg_array']['pwa_short_name']))
				{
					return;
				}

				$short_name = $event['cfg_array']['pwa_short_name'];

				// Do not allow multibyte characters or emoji
				if (strlen($short_name) !== mb_strlen($short_name, 'UTF-8'))
				{
					$this->add_error($event, 'PWA_SHORT_NAME_INVALID');
					return;
				}

				// Do not allow strings longer than 12 characters
				if (strlen($short_name) > 12)
				{
					$this->add_error($event, 'PWA_SHORT_NAME_INVALID');
					return;
				}
			break;

			case 'icons':
				// Ignore validation if icon fields are empty
				if (empty($event['cfg_array']['pwa_icon_small']) && empty($event['cfg_array']['pwa_icon_large']))
				{
					return;
				}

				$value = $event['cfg_array'][$event['config_name']];

				// Don't allow empty values, if one icon is set, both must be set.
				if (empty($value))
				{
					$this->add_error($event, 'PWA_IMAGE_NOT_PROVIDED', $this->language->lang(strtoupper($event['config_name'])));
					return;
				}

				// Check if image is valid
				$image = $this->root_path . $this->config['icons_path'] . '/' . $value;
				$image_info = $this->imagesize->getImageSize($image);
				if ($image_info !== false)
				{
					if (($event['config_name'] === 'pwa_icon_small' && $image_info['width'] !== 192 && $image_info['height'] !== 192) ||
						($event['config_name'] === 'pwa_icon_large' && $image_info['width'] !== 512 && $image_info['height'] !== 512))
					{
						$this->add_error($event, 'PWA_ICON_SIZE_INVALID', $value);
					}

					if ($image_info['type'] !== IMAGETYPE_PNG)
					{
						$this->add_error($event, 'PWA_ICON_MIME_INVALID', $value);
					}
				}
				else
				{
					$this->add_error($event, 'PWA_IMAGE_INVALID', $value);
				}
			break;
		}
	}

	/**
	 * Add Web Push info to the phpBB FAQ
	 *
	 * @param \phpbb\event\data $event The event object
	 * @return void
	 */
	public function wpn_faq($event)
	{
		if ($event['block_name'] === 'HELP_FAQ_BLOCK_BOOKMARKS')
		{
			$this->language->add_lang('webpushnotifications_faq', 'phpbb/webpushnotifications');

			$this->template->assign_block_vars('faq_block', [
				'BLOCK_TITLE'	=> $this->language->lang('HELP_FAQ_WPN'),
				'SWITCH_COLUMN'	=> false,
			]);

			$questions = [
				'HELP_FAQ_WPN_WHAT_QUESTION'    => 'HELP_FAQ_WPN_WHAT_ANSWER',
				'HELP_FAQ_WPN_HOW_QUESTION'     => 'HELP_FAQ_WPN_HOW_ANSWER',
				'HELP_FAQ_WPN_SESSION_QUESTION' => 'HELP_FAQ_WPN_SESSION_ANSWER',
				'HELP_FAQ_WPN_SUBBING_QUESTION' => 'HELP_FAQ_WPN_SUBBING_ANSWER',
				'HELP_FAQ_WPN_GENERAL_QUESTION' => 'HELP_FAQ_WPN_GENERAL_ANSWER',
			];

			$faq_rows = [];
			foreach ($questions as $question => $answer)
			{
				$faq_rows[] = [
					'FAQ_QUESTION' => $this->language->lang($question),
					'FAQ_ANSWER'   => $this->language->lang($answer),
				];
			}

			$this->template->assign_block_vars_array('faq_block.faq_row', $faq_rows);
		}
	}

	/**
	 * Add errors to the error array
	 *
	 * @param \phpbb\event\data $event
	 * @param string $error_key
	 * @param string $param
	 * @return void
	 */
	protected function add_error($event, $error_key, $param = null)
	{
		$error = $event['error'];
		$error[] = $this->language->lang($error_key, $param);
		$event['error'] = $error;
	}

	/**
	 * Can notifications be used by the user?
	 *
	 * @return bool
	 */
	protected function can_use_notifications()
	{
		return $this->config['wpn_webpush_enable']
			&& ANONYMOUS !== $this->user->id()
			&& USER_IGNORE !== (int) $this->user->data['user_type'];
	}

	/**
	 * Get short name from a string (strip out multibyte characters and trim to 12 characters)
	 *
	 * @param string $name
	 * @return string 12 max characters string
	 */
	protected function get_shortname($name)
	{
		return utf8_substr(preg_replace('/[^\x20-\x7E]/', '', $name), 0, 12);
	}
}
