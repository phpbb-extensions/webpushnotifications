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

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\controller\helper as controller_helper;
use phpbb\webpushnotifications\form\form_helper;
use phpbb\language\language;
use phpbb\notification\manager;
use phpbb\template\template;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return [
			'core.ucp_display_module_before'	=> 'load_language',
			'core.acp_main_notice'				=> 'compatibility_notice',
			'core.page_header_after'			=> [['load_template_data'], ['service_worker_url']],
		];
	}

	/* @var controller_helper */
	protected $controller_helper;

	/* @var form_helper */
	protected $form_helper;

	/* @var language */
	protected $language;

	/* @var template */
	protected $template;

	/* @var manager */
	protected $phpbb_notifications;

	/**
	 * Constructor
	 *
	 * @param controller_helper $controller_helper Controller helper object
	 * @param form_helper $form_helper Form helper object
	 * @param language $language Language object
	 * @param template $template Template object
	 * @param manager $phpbb_notifications Notifications manager object
	 */
	public function __construct(controller_helper $controller_helper, form_helper $form_helper, language $language, template $template, manager $phpbb_notifications)
	{
		$this->controller_helper = $controller_helper;
		$this->form_helper = $form_helper;
		$this->language = $language;
		$this->template = $template;
		$this->phpbb_notifications = $phpbb_notifications;
	}

	/**
	 * Load template data
	 */
	public function load_template_data()
	{
		$methods = $this->phpbb_notifications->get_subscription_methods();
		if (array_key_exists('notification.method.phpbb.wpn.webpush', $methods))
		{
			if (!$this->language->is_set('NOTIFICATION_METHOD_PHPBB_WPN_WEBPUSH'))
			{
				$this->language->add_lang('webpushnotifications_module_ucp', 'phpbb/webpushnotifications');
			}
			$template_ary = $methods['notification.method.phpbb.wpn.webpush']['method']->get_ucp_template_data($this->controller_helper, $this->form_helper);
			$this->template->assign_vars($template_ary);
		}
	}

	/**
	 * Load language file
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
	 * Generate service worker URL globally for update
	 */
	public function service_worker_url()
	{
		if (!$this->template->retrieve_var('U_WEBPUSH_WORKER_URL'))
		{
			$this->template->assign_var('U_WEBPUSH_WORKER_URL', $this->controller_helper->route('phpbb_webpushnotifications_ucp_push_worker_controller'));
		}
	}
}
