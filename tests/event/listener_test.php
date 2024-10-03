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

require_once __DIR__ . '/../../../../../includes/functions_acp.php';

class listener_test extends \phpbb_database_test_case
{
	/** @var \phpbb\webpushnotifications\event\listener */
	protected $listener;

	/* @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\controller\helper */
	protected $controller_helper;

	/* @var \phpbb\webpushnotifications\form\form_helper */
	protected $form_helper;

	/** @var \FastImageSize\FastImageSize|\PHPUnit\Framework\MockObject\MockObject  */
	protected $imagesize;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\template\template */
	protected $template;

	/** @var \phpbb\webpushnotifications\notification\method\webpush */
	protected $notification_method_webpush;

	/* @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\notification\manager */
	protected $phpbb_notifications;

	/** @var string */
	protected $root_path;

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
		$this->user->data['is_bot'] = false;
		$this->user->data['user_type'] = USER_NORMAL;
		$user_loader = new \phpbb\user_loader($db, $phpbb_root_path, $phpEx, 'phpbb_users');

		$this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$this->controller_helper->method('route')
			->willReturnCallback(function ($route, array $params = []) {
				return $route . '#' . serialize($params);
			});

		$this->config = new \phpbb\config\config([
			'load_notifications' => true,
			'allow_board_notifications' => true,
			'wpn_webpush_enable' => true,
		]);

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

		$this->imagesize = $this->getMockBuilder('\FastImageSize\FastImageSize')
			->getMock();

		$this->notification_method_webpush = new \phpbb\webpushnotifications\notification\method\webpush(
			$this->config,
			$db,
			new \phpbb\log\dummy(),
			$user_loader,
			$this->user,
			$path_helper,
			$phpbb_root_path,
			$phpEx,
			'phpbb_wpn_notification_push',
			'phpbb_wpn_push_subscriptions'
		);

		$this->root_path = $phpbb_root_path;
	}

	protected function set_listener()
	{
		$this->listener = new \phpbb\webpushnotifications\event\listener(
			$this->config,
			$this->controller_helper,
			$this->imagesize,
			$this->form_helper,
			$this->language,
			$this->template,
			$this->user,
			$this->notifications,
			$this->root_path
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
			'core.page_header_after',
			'core.ucp_display_module_before',
			'core.acp_main_notice',
			'core.acp_board_config_edit_add',
			'core.validate_config_variable',
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
	public function test_load_template_data($user_id, $method_data, $subscriptions, $expected)
	{
		$this->config['wpn_webpush_dropdown_subscribe'] = true;
		$this->user->data['user_id'] = $user_id;

		$this->set_listener();

		$method_data['method'] = $this->notification_method_webpush;

		$this->notifications->expects(self::once())
			->method('get_subscription_methods')
			->willReturn([$method_data['id'] => $method_data]);

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
			]);

		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.page_header_after', [$this->listener, 'load_template_data']);
		$dispatcher->trigger_event('core.page_header_after');
	}

	public function test_pwa_manifest()
	{
		$this->config['pwa_icon_small'] = 'icon-192x192.png';
		$this->config['pwa_short_name'] = 'Test';

		$this->set_listener();

		$this->template->expects(self::once())
			->method('assign_vars')
			->with([
				'U_MANIFEST_URL'	=> $this->controller_helper->route('phpbb_webpushnotifications_manifest_controller'),
				'U_TOUCH_ICON'		=> 'icon-192x192.png',
				'SHORT_SITE_NAME'	=> 'Test',
			]);

		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.acp_main_notice', [$this->listener, 'pwa_manifest']);
		$dispatcher->trigger_event('core.acp_main_notice');
	}

	public function acp_pwa_options_data()
	{
		return [
			[ // expected config and mode
				'settings',
				['vars' => ['legend4' => []]],
				['legend_pwa_settings', 'pwa_short_name', 'pwa_icon_small', 'pwa_icon_large', 'legend4'],
			],
			[ // unexpected mode
				'foobar',
				['vars' => ['legend4' => []]],
				['legend4'],
			],
			[ // unexpected config
				'post',
				['vars' => ['foobar' => []]],
				['foobar'],
			],
			[ // unexpected config and mode
				'foobar',
				['vars' => ['foobar' => []]],
				['foobar'],
			],
		];
	}

	/**
	 * @dataProvider acp_pwa_options_data
	 */
	public function test_acp_pwa_options($mode, $display_vars, $expected_keys)
	{
		$this->set_listener();

		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.acp_board_config_edit_add', [$this->listener, 'acp_pwa_options']);

		$event_data = ['display_vars', 'mode'];
		$event_data_after = $dispatcher->trigger_event('core.acp_board_config_edit_add', compact($event_data));

		foreach ($event_data as $expected)
		{
			self::assertArrayHasKey($expected, $event_data_after);
		}
		extract($event_data_after, EXTR_OVERWRITE);

		$keys = array_keys($display_vars['vars']);

		self::assertEquals($expected_keys, $keys);

	}

	public function validate_pwa_options_data()
	{
		return [
			[
				'pwa_options:icons',
				['pwa_icon_small' => '192.png', 'pwa_icon_large' => '512.png'],
				[],
			],
			[
				'pwa_options:icons',
				['pwa_icon_small' => '1.png', 'pwa_icon_large' => '512.png'],
				['PWA_ICON_SIZE_INVALID'],
			],
			[
				'pwa_options:icons',
				['pwa_icon_small' => '1.png', 'pwa_icon_large' => '12.png'],
				['PWA_ICON_SIZE_INVALID'],
			],
			[
				'pwa_options:icons',
				['pwa_icon_small' => '192.jpg', 'pwa_icon_large' => '512.gif'],
				['PWA_ICON_MIME_INVALID'],
			],
			[
				'pwa_options:icons',
				['pwa_icon_small' => '', 'pwa_icon_large' => ''],
				[],
			],
			[
				'pwa_options:string',
				['pwa_short_name' => 'foo'],
				[],
			],
			[
				'pwa_options:string',
				['pwa_short_name' => ''],
				[],
			],
			[
				'pwa_options:string',
				['pwa_short_name' => 'foo❤️'],
				['PWA_SHORT_NAME_INVALID'],
			],
			[
				'pwa_options:string',
				['pwa_short_name' => str_repeat('a', 50)],
				['PWA_SHORT_NAME_INVALID'],
			],
		];
	}

	/**
	 * @dataProvider validate_pwa_options_data
	 */
	public function test_validate_pwa_options($validate, $cfg_array, $expected_error)
	{
		$this->config['icons_path'] = 'images/icons';
		$config_name = key($cfg_array);
		$config_definition = ['validate' => $validate];

		$pwa_icon_small = isset($cfg_array['pwa_icon_small']) ? $cfg_array['pwa_icon_small'] : '';
		$pwa_icon_large = isset($cfg_array['pwa_icon_large']) ? $cfg_array['pwa_icon_large'] : '';

		[$small_image_name, $small_image_ext] = $pwa_icon_small ? explode('.', $pwa_icon_small, 2) : ['', ''];
		[$large_image_name, $large_image_ext] = $pwa_icon_large ? explode('.', $pwa_icon_large, 2) : ['', ''];

		$error = [];

		$this->set_listener();

		$this->imagesize->expects($pwa_icon_small && $pwa_icon_large ? self::once() : self::never())
			->method('getImageSize')
			->willReturnMap([
				[$this->root_path . $this->config['icons_path'] . '/', '', false],
				[$this->root_path . $this->config['icons_path'] . '/' . $pwa_icon_small, '', ['width' => (int) $small_image_name, 'height' => (int) $small_image_name, 'type' => $small_image_ext === 'png' ? IMAGETYPE_PNG : IMAGETYPE_UNKNOWN]],
				[$this->root_path . $this->config['icons_path'] . '/' . $pwa_icon_large, '', ['width' => (int) $large_image_name, 'height' => (int) $large_image_name, 'type' => $large_image_ext === 'png' ? IMAGETYPE_PNG : IMAGETYPE_UNKNOWN]],
			]);

		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.validate_config_variable', [$this->listener, 'validate_pwa_options']);

		$event_data = ['cfg_array', 'config_name', 'config_definition', 'error'];
		$event_data_after = $dispatcher->trigger_event('core.validate_config_variable', compact($event_data));

		foreach ($event_data as $expected)
		{
			self::assertArrayHasKey($expected, $event_data_after);
		}
		extract($event_data_after, EXTR_OVERWRITE);

		self::assertEquals($expected_error, $error);
	}
}
