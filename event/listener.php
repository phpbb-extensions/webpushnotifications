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
use phpbb\webpushnotifications\notification\method\webpush;
use phpbb\language\language;
use phpbb\template\template;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return [
			'core.page_header'					=> 'load_template_data',
			'core.ucp_display_module_before'	=> 'load_language',
			'core.acp_main_notice'				=> 'compatibility_notice',
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

	/* @var webpush */
	protected $webpush;

	/**
	 * Constructor
	 *
	 * @param controller_helper $controller_helper Controller helper object
	 * @param form_helper $form_helper Form helper object
	 * @param language $language Language object
	 * @param template $template Template object
	 * @param webpush $webpush Webpush notification method object
	 */
	public function __construct(controller_helper $controller_helper, form_helper $form_helper, language $language, template $template, webpush $webpush)
	{
		$this->controller_helper = $controller_helper;
		$this->form_helper = $form_helper;
		$this->language = $language;
		$this->template = $template;
		$this->webpush = $webpush;
	}

	/**
	 * Load template data
	 *
	 * @param \phpbb\event\data $event
	 */
	public function load_template_data($event)
	{
		$template_ary = $this->webpush->get_ucp_template_data($this->controller_helper, $this->form_helper);
		$this->template->assign_vars($template_ary);
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
}
