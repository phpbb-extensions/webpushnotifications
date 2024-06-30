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

	/** @var \phpbb\webpushnotifications\notification\method\webpush */
	protected $notification_method_webpush;

	/* @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\notification\manager */
	protected $phpbb_notifications;

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
		$this->user = $user;
		$this->user->data['user_form_salt'] = '';
		$user_loader = new \phpbb\user_loader($db, $phpbb_root_path, $phpEx, 'phpbb_users');

		$this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$this->controller_helper->method('route')
			->willReturnCallback(function ($route, array $params = []) {
				return $route . '#' . serialize($params);
			});

		$this->config = new \phpbb\config\config([]);

		$this->form_helper = new \phpbb\webpushnotifications\form\form_helper(
			$this->config,
			$request,
			$this->user
		);

		$path_helper = $this->getMockBuilder('\phpbb\path_helper')
			->disableOriginalConstructor()
			->setMethods(array())
			->getMock();

		$this->notifications = $this->getMockBuilder('\phpbb\notification\manager')
			->disableOriginalConstructor()
			->getMock();

		$this->notification_method_webpush = new \phpbb\webpushnotifications\notification\method\webpush(
			$this->config,
			$db,
			$this->language,
			new \phpbb\log\dummy(),
			$user_loader,
			$this->user,
			$path_helper,
			$phpbb_root_path,
			$phpEx,
			'phpbb_wpn_notification_push',
			'phpbb_wpn_push_subscriptions'
		);
	}

	protected function set_listener()
	{
		$this->listener = new \phpbb\webpushnotifications\event\listener(
			$this->controller_helper,
			$this->form_helper,
			$this->language,
			$this->template,
			$this->notifications
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
			'core.ucp_display_module_before',
			'core.acp_main_notice',
			'core.page_header_after',
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
			[	// webpush method with a valid webpush subscription
				2,
				'method_data' => [
					'id' => 'notification.method.phpbb.wpn.webpush',
				],
				[['endpoint' => 'https://web.push.test.localhost/foo2', 'expirationTime' => 0]],
				true,
			],
			[	// wrong method, but with a valid webpush subscription, expect no code execution
				2,
				'method_data' => [
					'id' => 'notification.method.email',
				],
				[],
				false,
			],
			[	// webpush method with another valid webpush subscription
				3,
				'method_data' => [
					'id' => 'notification.method.phpbb.wpn.webpush',
				],
				[['endpoint' => 'https://web.push.test.localhost/foo3', 'expirationTime' => 1]],
				true,
			],
			[	// webpush method with an invalid webpush subscription, expect code execution but no subscription data
				5,
				'method_data' => [
					'id' => 'notification.method.phpbb.wpn.webpush',
				],
				[],
				true,
			],
			[	// wrong method with an invalid webpush subscription, expect no code execution
				5,
				'method_data' => [
					'id' => 'notification.method.email',
				],
				[],
				false,
			],
		];
	}

	/**
	 * @dataProvider get_ucp_template_data_data
	 */
	public function test_get_ucp_template_data($user_id, $method_data, $subscriptions, $expected)
	{
		$this->config['wpn_webpush_dropdown_subscribe'] = true;
		$this->user->data['user_id'] = $user_id;
		$this->template->expects($expected ? self::once() : self::never())
			->method('assign_vars')
			->with([
				'NOTIFICATIONS_WEBPUSH_ENABLE'	=> true,
				'U_WEBPUSH_SUBSCRIBE'			=> $this->controller_helper->route('phpbb_webpushnotifications_ucp_push_subscribe_controller'),
				'U_WEBPUSH_UNSUBSCRIBE'			=> $this->controller_helper->route('phpbb_webpushnotifications_ucp_push_unsubscribe_controller'),
				'VAPID_PUBLIC_KEY'				=> $this->config['wpn_webpush_vapid_public'],
				'U_WEBPUSH_WORKER_URL'			=> $this->controller_helper->route('phpbb_webpushnotifications_ucp_push_worker_controller'),
				'SUBSCRIPTIONS'					=> $subscriptions,
				'WEBPUSH_FORM_TOKENS'			=> $this->form_helper->get_form_tokens(\phpbb\webpushnotifications\ucp\controller\webpush::FORM_TOKEN_UCP),
				'U_MANIFEST_URL'				=> $this->controller_helper->route('phpbb_webpushnotifications_manifest_controller'),
				'U_TOUCH_ICON'					=> '',
			]
		);

		$this->set_listener();

		$method_data['method'] = $this->notification_method_webpush;

		$this->notifications->expects(self::once())
			->method('get_subscription_methods')
			->willReturn([$method_data['id'] => $method_data]);

		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.page_header_after', [$this->listener, 'load_template_data']);
		$dispatcher->trigger_event('core.page_header_after');
	}
}
