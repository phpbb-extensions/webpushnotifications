<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\tests\event;

use phpbb\webpushnotifications\notification\method\webpush;
use phpbb\webpushnotifications\ucp\controller\webpush as ucp_webpush;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class listener_test extends \phpbb_database_test_case
{
	/** @var \phpbb\webpushnotifications\event\listener */
	protected $listener;

	/* @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\controller\helper */
	protected $controller_helper;

	/* @var \phpbb\webpushnotifications\form\form_helper */
	protected $form_helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\template\template */
	protected $template;

	/** @var webpush */
	protected $notification_method_webpush;

	protected static function setup_extensions()
	{
		return ['phpbb/webpushnotifications'];
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/webpush.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx, $user;

		$db = $this->new_dbal();

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang_loader->set_extension_manager(new \phpbb_mock_extension_manager($phpbb_root_path));
		$this->language = new \phpbb\language\language($lang_loader);
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();
		$request = new \phpbb\request\request();
		$request->enable_super_globals();
		$user = new \phpbb\user($this->language, '\phpbb\datetime');
		$user_loader = new \phpbb\user_loader($db, $phpbb_root_path, $phpEx, 'phpbb_users');

		$this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$this->controller_helper->method('route')
			->willReturnCallback(function ($route, array $params = array()) {
				return $route . '#' . serialize($params);
			});

		$this->config = new \phpbb\config\config([]);

		$this->form_helper = new \phpbb\webpushnotifications\form\form_helper(
			$this->config,
			$request,
			$user
		);

		$phpbb_container = $this->container = new ContainerBuilder();
		$phpbb_container->set('user_loader', $user_loader);
		$phpbb_container->set('user', $user);
		$phpbb_container->set('config', $this->config);
		$phpbb_container->set('dbal.conn', $db);
		$phpbb_container->set('log', new \phpbb\log\dummy());
		$phpbb_container->setParameter('core.root_path', $phpbb_root_path);
		$phpbb_container->setParameter('core.php_ext', $phpEx);
		$phpbb_container->setParameter('tables.phpbb.wpn.notification_push', 'phpbb_wpn_notification_push');
		$phpbb_container->setParameter('tables.phpbb.wpn.push_subscriptions', 'phpbb_wpn_push_subscriptions');

		$this->notification_method_webpush = new webpush(
			$phpbb_container->get('config'),
			$phpbb_container->get('dbal.conn'),
			$phpbb_container->get('log'),
			$phpbb_container->get('user_loader'),
			$phpbb_container->get('user'),
			$phpbb_root_path,
			$phpEx,
			$phpbb_container->getParameter('tables.phpbb.wpn.notification_push'),
			$phpbb_container->getParameter('tables.phpbb.wpn.push_subscriptions')
		);

		$phpbb_container->set('notification.method.phpbb.wpn.webpush', $this->notification_method_webpush);

		$phpbb_container->compile();
	}

	protected function set_listener()
	{
		$this->listener = new \phpbb\webpushnotifications\event\listener(
			$this->controller_helper,
			$this->form_helper,
			$this->language,
			$this->template
		);
	}

	public function test_construct()
	{
		$this->set_listener();
		self::assertInstanceOf('\Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->listener);
	}

	public function test_getSubscribedEvents()
	{
		self::assertEquals([
			'core.ucp_notifications_output_notification_types_modify_template_vars',
			'core.ucp_display_module_before',
			'core.acp_main_notice',
		], array_keys(\phpbb\webpushnotifications\event\listener::getSubscribedEvents()));
	}

	public function test_load_language()
	{
		$this->set_listener();

		self::assertFalse($this->language->is_set('NOTIFICATION_METHOD_PHPBB_WPN_WEBPUSH'));

		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.ucp_display_module_before', [$this->listener, 'load_language']);
		$dispatcher->trigger_event('core.ucp_display_module_before');

		self::assertTrue($this->language->is_set('NOTIFICATION_METHOD_PHPBB_WPN_WEBPUSH'));
	}

	public function test_compatibility_notice()
	{
		$this->set_listener();

		$this->template->expects(self::once())
			->method('assign_var')
			->with('S_WPN_COMPATIBILITY_NOTICE', false);

		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.acp_main_notice', [$this->listener, 'compatibility_notice']);
		$dispatcher->trigger_event('core.acp_main_notice');
	}

	public function get_ucp_template_data_data()
	{
		return [
			[
				'method_data' => [
					'id' => 'notification.method.phpbb.wpn.webpush',
				],
				true,
			],
			[
				'method_data' => [
					'id' => 'notification.method.phpbb.email',
				],
				false,
			],
		];
	}

	/**
	 * @dataProvider get_ucp_template_data_data
	 */
	public function test_get_ucp_template_data($method_data, $expected)
	{
		$this->template->expects($expected ? self::once() : self::never())
			->method('assign_vars')
			->with([
				'NOTIFICATIONS_WEBPUSH_ENABLE'	=> true,
				'U_WEBPUSH_SUBSCRIBE'			=> $this->controller_helper->route('phpbb_webpushnotifications_ucp_push_subscribe_controller'),
				'U_WEBPUSH_UNSUBSCRIBE'			=> $this->controller_helper->route('phpbb_webpushnotifications_ucp_push_unsubscribe_controller'),
				'VAPID_PUBLIC_KEY'				=> $this->config['wpn_webpush_vapid_public'],
				'U_WEBPUSH_WORKER_URL'			=> $this->controller_helper->route('phpbb_webpushnotifications_ucp_push_worker_controller'),
				'SUBSCRIPTIONS'					=> [['endpoint' => 'https://web.push.test.localhost/foo', 'expirationTime' => 0]],
				'WEBPUSH_FORM_TOKENS'			=> $this->form_helper->get_form_tokens(ucp_webpush::FORM_TOKEN_UCP),
			]
		);

		$this->set_listener();

		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.ucp_notifications_output_notification_types_modify_template_vars', [$this->listener, 'load_template_data']);

		$method_data['method'] = $this->notification_method_webpush;

		$event_data = ['method_data'];
		$event_data_returned = $dispatcher->trigger_event('core.ucp_notifications_output_notification_types_modify_template_vars', compact($event_data));
		extract($event_data_returned);
	}
}
