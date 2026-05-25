<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\tests\acp;

use phpbb\webpushnotifications\acp\wpn_acp_module;

require_once __DIR__ . '/../../../../../includes/functions_module.php';
require_once __DIR__ . '/../../../../../includes/functions_acp.php';

class acp_module_test extends \phpbb_test_case
{
	/** @var bool */
	public static $valid_form = true;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb_mock_extension_manager */
	protected $extension_manager;

	/** @var \phpbb\module\module_manager */
	protected $module_manager;

	/** @var \FastImageSize\FastImageSize|\PHPUnit\Framework\MockObject\MockObject */
	protected $imagesize;

	/** @var \phpbb\language\language|\PHPUnit\Framework\MockObject\MockObject */
	protected $lang;

	/** @var \phpbb\log\log_interface|\PHPUnit\Framework\MockObject\MockObject */
	protected $log;

	/** @var \phpbb\request\request|\PHPUnit\Framework\MockObject\MockObject */
	protected $request;

	/** @var \phpbb\symfony_request|\PHPUnit\Framework\MockObject\MockObject */
	protected $symfony_request;

	/** @var \phpbb\template\template|\PHPUnit\Framework\MockObject\MockObject */
	protected $template;

	/** @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject */
	protected $container;

	/** @var \phpbb\user|\PHPUnit\Framework\MockObject\MockObject */
	protected $user;

	/** @var string */
	protected $root_path;

	protected function setUp(): void
	{
		global $phpbb_dispatcher, $phpbb_extension_manager, $phpbb_root_path, $phpEx, $phpbb_container, $template, $user, $language;

		self::$valid_form = true;

		$this->extension_manager = new \phpbb_mock_extension_manager(
			$phpbb_root_path,
			[
				'phpbb/webpushnotifications' => [
					'ext_name' => 'phpbb/webpushnotifications',
					'ext_active' => '1',
					'ext_path' => 'ext/phpbb/webpushnotifications/',
				],
			]
		);
		$phpbb_extension_manager = $this->extension_manager;

		$this->module_manager = new \phpbb\module\module_manager(
			new \phpbb\cache\driver\dummy(),
			$this->createMock('\phpbb\db\driver\driver_interface'),
			$this->extension_manager,
			MODULES_TABLE,
			$phpbb_root_path,
			$phpEx
		);

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		if (!defined('IN_ADMIN'))
		{
			define('IN_ADMIN', true);
		}

		$this->config = new \phpbb\config\config($this->default_config());
		$this->imagesize = $this->createMock('\FastImageSize\FastImageSize');
		$this->lang = $this->createMock('\phpbb\language\language');
		$this->log = $this->createMock('\phpbb\log\log_interface');
		$this->request = $this->createMock('\phpbb\request\request');
		$this->symfony_request = $this->createMock('\phpbb\symfony_request');
		$this->template = $this->createMock('\phpbb\template\template');
		$this->user = $this->createMock('\phpbb\user');
		$this->root_path = $phpbb_root_path;

		$this->user->data = ['user_id' => 42];
		$this->user->ip = '127.0.0.1';
		$this->user->lang = new \phpbb_mock_lang();

		$this->lang->method('lang')
			->willReturnCallback(function() {
				return implode(':', func_get_args());
			});

		$this->container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$this->container->method('get')
			->willReturnCallback(function($service) {
				return [
					'config' => $this->config,
					'upload_imagesize' => $this->imagesize,
					'language' => $this->lang,
					'log' => $this->log,
					'request' => $this->request,
					'symfony_request' => $this->symfony_request,
					'template' => $this->template,
					'user' => $this->user,
				][$service];
			});
		$this->container->method('getParameter')
			->with('core.root_path')
			->willReturn($phpbb_root_path);

		$phpbb_container = $this->container;
		$template = $this->template;
		$user = $this->user;
		$language = $this->lang;
	}

	public function test_module_info(): void
	{
		self::assertEquals([
			'\\phpbb\\webpushnotifications\\acp\\wpn_acp_module' => [
				'filename'	=> '\\phpbb\\webpushnotifications\\acp\\wpn_acp_module',
				'title'		=> 'ACP_WEBPUSH_EXT_SETTINGS',
				'modes'		=> [
					'webpush'	=> [
						'title' => 'ACP_WEBPUSH_EXT_SETTINGS',
						'auth' => 'ext_phpbb/webpushnotifications && acl_a_server',
						'cat' => ['ACP_CLIENT_COMMUNICATION']
					],
					'pwa'	=> [
						'title' => 'ACP_WEBPUSH_PWA_SETTINGS',
						'auth' => 'ext_phpbb/webpushnotifications && acl_a_board',
						'cat' => ['ACP_CLIENT_COMMUNICATION']
					],
				],
			],
		], $this->module_manager->get_module_infos('acp', 'wpn_acp_module'));
	}

	public function module_auth_test_data(): array
	{
		return [
			'missing extension' => ['ext_foo/bar', false],
			'active extension' => ['ext_phpbb/webpushnotifications', true],
		];
	}

	/**
	 * @dataProvider module_auth_test_data
	 */
	public function test_module_auth($module_auth, $expected): void
	{
		self::assertEquals($expected, \p_master::module_auth($module_auth, 0));
	}

	public function mode_display_data(): array
	{
		return [
			'webpush secure server' => [
				'webpush',
				'adm.php?i=test&mode=webpush',
				'wpn_acp_settings',
				'ACP_WEBPUSH_EXT_SETTINGS',
				true,
				'example.com',
				$this->webpush_template_vars('adm.php?i=test&mode=webpush'),
				false,
				'',
			],
			'webpush localhost without https' => [
				'webpush',
				'adm.php?i=test&mode=webpush',
				'wpn_acp_settings',
				'ACP_WEBPUSH_EXT_SETTINGS',
				false,
				'localhost',
				$this->webpush_template_vars('adm.php?i=test&mode=webpush'),
				false,
				'',
			],
			'webpush insecure remote server' => [
				'webpush',
				'adm.php?i=test&mode=webpush',
				'wpn_acp_settings',
				'ACP_WEBPUSH_EXT_SETTINGS',
				false,
				'example.com',
				$this->webpush_template_vars('adm.php?i=test&mode=webpush'),
				true,
				'WEBPUSH_INSECURE_SERVER_ERROR',
			],
			'pwa settings' => [
				'pwa',
				'adm.php?i=test&mode=pwa',
				'wpn_acp_pwa',
				'ACP_WEBPUSH_PWA_SETTINGS',
				true,
				'example.com',
				$this->pwa_template_vars('adm.php?i=test&mode=pwa'),
				false,
				'',
			],
		];
	}

	/**
	 * @dataProvider mode_display_data
	 */
	public function test_main_displays_mode_settings($mode, $u_action, $tpl_name, $page_title, $is_secure, $server_name, array $settings_vars, $has_error, $error_msg): void
	{
		$this->request->expects($this->once())
			->method('is_set_post')
			->with('submit')
			->willReturn(false);

		$this->symfony_request->method('isSecure')
			->willReturn($is_secure);

		$this->request->method('server')
			->with('SERVER_NAME')
			->willReturn($server_name);

		$this->template->expects(self::exactly(2))
			->method('assign_vars')
			->withConsecutive(
				[$settings_vars],
				[[
					'S_ERROR' => $has_error,
					'ERROR_MSG' => $error_msg,
				]]
			);

		$module = $this->create_module($u_action);
		$module->main('', $mode);

		self::assertSame($tpl_name, $module->tpl_name);
		self::assertSame($page_title, $module->page_title);
	}

	public function invalid_form_key_data(): array
	{
		return [
			'webpush' => ['webpush'],
			'pwa' => ['pwa'],
		];
	}

	/**
	 * @dataProvider invalid_form_key_data
	 */
	public function test_main_rejects_invalid_form_key($mode): void
	{
		self::$valid_form = false;

		$this->request->method('is_set_post')
			->with('submit')
			->willReturn(true);

		$this->setExpectedTriggerError(E_USER_WARNING, 'FORM_INVALID');

		$this->create_module('adm.php?i=test&mode=' . $mode)->main('', $mode);
	}

	public function webpush_save_data(): array
	{
		$valid = [
			'wpn_webpush_enable' => 1,
			'wpn_webpush_vapid_public' => str_repeat('a', 25),
			'wpn_webpush_vapid_private' => str_repeat('b', 25),
			'wpn_webpush_dropdown_subscribe' => 1,
			'wpn_webpush_method_enabled' => 0,
			'wpn_webpush_popup_prompt' => 1,
		];

		return [
			'valid enabled settings' => [
				$valid,
				[
					'S_ERROR' => false,
					'ERROR_MSG' => '',
				],
				[
					'wpn_webpush_vapid_public' => str_repeat('a', 25),
					'wpn_webpush_vapid_private' => str_repeat('b', 25),
					'wpn_webpush_method_enabled' => 0,
				],
				true,
			],
			'masked existing private key is preserved' => [
				array_merge($valid, [
					'wpn_webpush_vapid_private' => wpn_acp_module::MASKED_PRIVATE_KEY,
				]),
				[
					'S_ERROR' => false,
					'ERROR_MSG' => '',
				],
				[
					'wpn_webpush_vapid_public' => str_repeat('a', 25),
					'wpn_webpush_vapid_private' => 'existing-private-key',
				],
				true,
			],
			'invalid enabled VAPID keys are rejected' => [
				array_merge($valid, [
					'wpn_webpush_vapid_public' => 'short',
					'wpn_webpush_vapid_private' => 'short',
				]),
				[
					'S_ERROR' => true,
					'ERROR_MSG' => 'SETTING_TOO_SHORT<br>SETTING_TOO_SHORT',
				],
				[
					'wpn_webpush_vapid_public' => 'existing-public-key',
					'wpn_webpush_vapid_private' => 'existing-private-key',
				],
				false,
			],
			'disabled webpush skips VAPID validation' => [
				array_merge($valid, [
					'wpn_webpush_enable' => 0,
					'wpn_webpush_vapid_public' => '',
					'wpn_webpush_vapid_private' => '',
				]),
				[
					'S_ERROR' => false,
					'ERROR_MSG' => '',
				],
				[
					'wpn_webpush_enable' => 0,
					'wpn_webpush_vapid_public' => '',
					'wpn_webpush_vapid_private' => '',
				],
				true,
			],
		];
	}

	/**
	 * @dataProvider webpush_save_data
	 */
	public function test_save_settings_validates_and_persists_webpush_config(array $input, array $error_vars, array $expected_config, $expect_saved): void
	{
		$this->config['wpn_webpush_vapid_public'] = 'existing-public-key';
		$this->config['wpn_webpush_vapid_private'] = 'existing-private-key';

		$this->request->method('variable')
			->with('config', ['' => ''], true)
			->willReturn($input);

		$this->template->expects($this->once())
			->method('assign_vars')
			->with($error_vars);

		$this->log->expects($expect_saved ? $this->once() : $this->never())
			->method('add')
			->with('admin', 42, '127.0.0.1', 'LOG_CONFIG_WEBPUSH');

		if ($expect_saved)
		{
			$this->setExpectedTriggerError(E_USER_NOTICE, 'CONFIG_UPDATED');
		}

		$this->create_module()->save_settings();

		foreach ($expected_config as $name => $value)
		{
			self::assertSame($value, $this->config[$name]);
		}
	}

	public function pwa_save_data(): array
	{
		$valid = [
			'pwa_short_name' => 'Forum',
			'pwa_icon_small' => 'icon192.png',
			'pwa_icon_large' => 'icon512.png',
			'pwa_theme_colour' => '#ABCDEF',
			'pwa_background_colour' => ' bad ',
			'pwa_show_install_banner' => 1,
		];

		return [
			'valid icons and normalised colours' => [
				$valid,
				[
					'icon192.png' => ['width' => 192, 'height' => 192, 'type' => IMAGETYPE_PNG],
					'icon512.png' => ['width' => 512, 'height' => 512, 'type' => IMAGETYPE_PNG],
				],
				[
					'S_ERROR' => false,
					'ERROR_MSG' => '',
				],
				[
					'pwa_short_name' => 'Forum',
					'pwa_icon_small' => 'icon192.png',
					'pwa_icon_large' => 'icon512.png',
					'pwa_theme_colour' => '#abcdef',
					'pwa_background_colour' => '#ffffff',
					'pwa_show_install_banner' => 1,
				],
				true,
			],
			'valid empty name and icons' => [
				[
					'pwa_theme_colour' => '#000000',
					'pwa_background_colour' => '#ffffff',
				],
				[],
				[
					'S_ERROR' => false,
					'ERROR_MSG' => '',
				],
				[
					'pwa_short_name' => '',
					'pwa_icon_small' => '',
					'pwa_icon_large' => '',
					'pwa_theme_colour' => '#000000',
					'pwa_background_colour' => '#ffffff',
					'pwa_show_install_banner' => 0,
				],
				true,
			],
			'too long short name rejected after entity decoding' => [
				array_merge($valid, [
					'pwa_short_name' => '123456789012&#x1F600;',
				]),
				[
					'icon192.png' => ['width' => 192, 'height' => 192, 'type' => IMAGETYPE_PNG],
					'icon512.png' => ['width' => 512, 'height' => 512, 'type' => IMAGETYPE_PNG],
				],
				[
					'S_ERROR' => true,
					'ERROR_MSG' => 'PWA_SHORT_NAME_INVALID',
				],
				[
					'pwa_short_name' => 'Old',
				],
				false,
			],
			'missing small icon rejected' => [
				array_merge($valid, [
					'pwa_icon_small' => '',
				]),
				[],
				[
					'S_ERROR' => true,
					'ERROR_MSG' => 'PWA_ICON_NOT_PROVIDED:PWA_ICON_SMALL',
				],
				[
					'pwa_icon_small' => 'old-small.png',
				],
				false,
			],
			'missing large icon rejected' => [
				array_merge($valid, [
					'pwa_icon_large' => '',
				]),
				[],
				[
					'S_ERROR' => true,
					'ERROR_MSG' => 'PWA_ICON_NOT_PROVIDED:PWA_ICON_LARGE',
				],
				[
					'pwa_icon_large' => 'old-large.png',
				],
				false,
			],
			'path traversal icon rejected' => [
				array_merge($valid, [
					'pwa_icon_small' => '../icon192.png',
				]),
				[
					'icon512.png' => ['width' => 512, 'height' => 512, 'type' => IMAGETYPE_PNG],
				],
				[
					'S_ERROR' => true,
					'ERROR_MSG' => 'PWA_ICON_INVALID:../icon192.png',
				],
				[
					'pwa_icon_small' => 'old-small.png',
				],
				false,
			],
			'unreadable icon rejected' => [
				$valid,
				[
					'icon192.png' => false,
					'icon512.png' => ['width' => 512, 'height' => 512, 'type' => IMAGETYPE_PNG],
				],
				[
					'S_ERROR' => true,
					'ERROR_MSG' => 'PWA_ICON_INVALID:icon192.png',
				],
				[
					'pwa_icon_small' => 'old-small.png',
				],
				false,
			],
			'wrong icon size and MIME rejected' => [
				$valid,
				[
					'icon192.png' => ['width' => 191, 'height' => 192, 'type' => IMAGETYPE_JPEG],
					'icon512.png' => ['width' => 512, 'height' => 511, 'type' => IMAGETYPE_PNG],
				],
				[
					'S_ERROR' => true,
					'ERROR_MSG' => 'PWA_ICON_SIZE_INVALID:icon192.png<br>PWA_ICON_MIME_INVALID:icon192.png<br>PWA_ICON_SIZE_INVALID:icon512.png',
				],
				[
					'pwa_icon_small' => 'old-small.png',
					'pwa_icon_large' => 'old-large.png',
				],
				false,
			],
		];
	}

	/**
	 * @dataProvider pwa_save_data
	 */
	public function test_save_pwa_settings_validates_and_persists_pwa_config(array $input, array $image_info, array $error_vars, array $expected_config, $expect_saved): void
	{
		$this->request->method('variable')
			->with('config', ['' => ''], true)
			->willReturn($input);

		$this->imagesize->method('getImageSize')
			->willReturnCallback(function($path) use ($image_info) {
				return $image_info[basename($path)];
			});

		$this->template->expects($this->once())
			->method('assign_vars')
			->with($error_vars);

		$this->log->expects($expect_saved ? $this->once() : $this->never())
			->method('add')
			->with('admin', 42, '127.0.0.1', 'LOG_CONFIG_WEBPUSH');

		if ($expect_saved)
		{
			$this->setExpectedTriggerError(E_USER_NOTICE, 'CONFIG_UPDATED');
		}

		$this->create_module('adm.php?i=test&mode=pwa')->save_pwa_settings();

		foreach ($expected_config as $name => $value)
		{
			self::assertSame($value, $this->config[$name]);
		}
	}

	public function colour_data(): array
	{
		return [
			'valid colour lowercased' => [' #ABCDEF ', '#000000', '#abcdef'],
			'valid digits kept' => ['#123456', '#000000', '#123456'],
			'missing hash rejected' => ['123456', '#000000', '#000000'],
			'short hex rejected' => ['#fff', '#000000', '#000000'],
			'invalid character rejected' => ['#12345g', '#ffffff', '#ffffff'],
			'non string rejected' => [123456, '#000000', '#000000'],
		];
	}

	/**
	 * @dataProvider colour_data
	 */
	public function test_normalise_colour($colour, $default, $expected): void
	{
		self::assertSame($expected, $this->invoke_method($this->create_module(), 'normalise_colour', [$colour, $default]));
	}

	public function test_display_errors_returns_false_without_errors(): void
	{
		$this->template->expects($this->once())
			->method('assign_vars')
			->with([
				'S_ERROR' => false,
				'ERROR_MSG' => '',
			]);

		self::assertFalse($this->create_module()->display_errors());
	}

	public function test_display_errors_returns_true_with_joined_errors(): void
	{
		$module = $this->create_module();
		$this->set_protected_property($module, 'errors', ['first', 'second']);

		$this->template->expects($this->once())
			->method('assign_vars')
			->with([
				'S_ERROR' => true,
				'ERROR_MSG' => 'first<br>second',
			]);

		self::assertTrue($module->display_errors());
	}

	protected function create_module($u_action = 'adm.php?i=test&mode=webpush'): wpn_acp_module
	{
		$module = new wpn_acp_module();
		$module->u_action = $u_action;

		foreach ([
			'config' => $this->config,
			'imagesize' => $this->imagesize,
			'lang' => $this->lang,
			'log' => $this->log,
			'request' => $this->request,
			'symfony_request' => $this->symfony_request,
			'template' => $this->template,
			'user' => $this->user,
			'root_path' => $this->root_path,
		] as $property => $value)
		{
			$this->set_protected_property($module, $property, $value);
		}

		return $module;
	}

	protected function set_protected_property($object, $property, $value): void
	{
		$reflection = new \ReflectionClass($object);
		$property = $reflection->getProperty($property);
		$property->setAccessible(true);
		$property->setValue($object, $value);
	}

	protected function invoke_method($object, $method_name, array $parameters = [])
	{
		$reflection = new \ReflectionClass($object);
		$method = $reflection->getMethod($method_name);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}

	protected function default_config(): array
	{
		return [
			'wpn_webpush_enable' => 1,
			'wpn_webpush_vapid_public' => 'existing-public-key',
			'wpn_webpush_vapid_private' => 'existing-private-key',
			'wpn_webpush_dropdown_subscribe' => 1,
			'wpn_webpush_method_enabled' => 1,
			'wpn_webpush_popup_prompt' => 1,
			'pwa_show_install_banner' => 1,
			'pwa_short_name' => 'Old',
			'pwa_icon_small' => 'old-small.png',
			'pwa_icon_large' => 'old-large.png',
			'pwa_theme_colour' => '#000000',
			'pwa_background_colour' => '#ffffff',
		];
	}

	protected function webpush_template_vars($u_action): array
	{
		return [
			'S_WEBPUSH_ENABLE' => 1,
			'WEBPUSH_VAPID_PUBLIC' => 'existing-public-key',
			'WEBPUSH_VAPID_PRIVATE' => wpn_acp_module::MASKED_PRIVATE_KEY,
			'S_WEBPUSH_DROPDOWN_SUBSCRIBE' => 1,
			'S_WEBPUSH_METHOD_ENABLED' => 1,
			'S_WEBPUSH_POPUP_PROMPT' => 1,
			'U_ACTION' => $u_action,
		];
	}

	protected function pwa_template_vars($u_action): array
	{
		return [
			'S_PWA_SHOW_INSTALL_BANNER' => true,
			'PWA_SHORT_NAME' => 'Old',
			'PWA_ICON_SMALL' => 'old-small.png',
			'PWA_ICON_LARGE' => 'old-large.png',
			'PWA_THEME_COLOUR' => '#000000',
			'PWA_BACKGROUND_COLOUR' => '#ffffff',
			'U_ACTION' => $u_action,
		];
	}
}

namespace phpbb\webpushnotifications\acp;

function add_form_key()
{
}

function check_form_key()
{
	return \phpbb\webpushnotifications\tests\acp\acp_module_test::$valid_form;
}
