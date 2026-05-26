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

use phpbb\config\config;
use phpbb\controller\helper as controller_helper;
use phpbb\language\language;
use phpbb\notification\manager;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use phpbb\webpushnotifications\ext;
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

	/* @var language */
	protected $language;

	/** @var request_interface */
	protected $request;

	/* @var template */
	protected $template;

	/** @var user */
	protected $user;

	/* @var manager */
	protected $phpbb_notifications;

	/**
	 * Constructor
	 *
	 * @param config $config
	 * @param controller_helper $controller_helper Controller helper object
	 * @param form_helper $form_helper Form helper object
	 * @param language $language Language object
	 * @param request_interface $request
	 * @param template $template Template object
	 * @param user $user
	 * @param manager $phpbb_notifications Notifications manager object
	 */
	public function __construct(config $config, controller_helper $controller_helper, form_helper $form_helper, language $language, request_interface $request, template $template, user $user, manager $phpbb_notifications)
	{
		$this->config = $config;
		$this->controller_helper = $controller_helper;
		$this->form_helper = $form_helper;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_notifications = $phpbb_notifications;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'						=> 'load_language_on_setup',
			'core.page_header_after'				=> [['load_template_data'], ['pwa_manifest']],
			'core.ucp_display_module_before'		=> 'load_language',
			'core.acp_main_notice'					=> 'compatibility_notice',
			'core.help_manager_add_block_after'		=> 'wpn_faq',
		];
	}

	/**
	 * Load common language file during user setup
	 *
	 * @param	\phpbb\event\data	$event	The event object
	 * @return	void
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'phpbb/webpushnotifications',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
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
			'U_TOUCH_ICON'		=> $this->config['pwa_icon_small'] ? ext::PWA_ICON_DIR . '/' . $this->config['pwa_icon_small'] : null,
			'SHORT_SITE_NAME'	=> $this->config['pwa_short_name'] ?: $this->trim_shortname($this->config['sitename']),
			'PWA_THEME_COLOR'	=> !empty($this->user->style['pwa_theme_color']) ? $this->user->style['pwa_theme_color'] : ext::PWA_THEME_COLOR,
			'PWA_BG_COLOR'		=> !empty($this->user->style['pwa_bg_color']) ? $this->user->style['pwa_bg_color'] : ext::PWA_BG_COLOR,
			'S_PWA_SHOW_BANNER'	=> !empty($this->config['pwa_show_install_banner']) && $this->is_mobile_phone(),
		]);
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
	 * Trim short name from a string to 12 characters
	 *
	 * @param string $name
	 * @return string 12 max characters string
	 */
	protected function trim_shortname($name)
	{
		$decoded = ext::decode_entities($name, ENT_COMPAT);
		$trimmed = utf8_substr($decoded, 0, 12);
		return utf8_htmlspecialchars($trimmed);
	}

	/**
	 * Lightweight phone detection for install-banner display
	 *
	 * @return bool
	 */
	protected function is_mobile_phone()
	{
		$user_agent = (string) $this->request->server('HTTP_USER_AGENT', '');
		if ($user_agent === '' || preg_match('/ipad|tablet|kindle|silk|playbook/i', $user_agent))
		{
			return false;
		}

		return (bool) preg_match('/mobile|iphone|ipod|android.*mobile|windows phone|blackberry|opera mini/i', $user_agent);
	}
}
