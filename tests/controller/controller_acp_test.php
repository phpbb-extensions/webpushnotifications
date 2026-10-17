<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\tests\controller;

use phpbb\webpushnotifications\controller\acp_controller;

require_once __DIR__ . '/../../../../../includes/functions_acp.php';

class controller_acp_test extends \phpbb_test_case
{
	/** @var bool */
	public static $valid_form = true;

	/** @var \phpbb\cache\service|\PHPUnit\Framework\MockObject\MockObject */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

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

	/** @var \phpbb\user|\PHPUnit\Framework\MockObject\MockObject */
	protected $user;

	/** @var string */
	protected $root_path;

	/** @var array */
	protected $style_updates;

	protected function setUp(): void
	{
		global $phpbb_dispatcher, $phpbb_root_path, $template, $user, $language;

		self::$valid_form = true;

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		if (!defined('IN_ADMIN'))
		{
			define('IN_ADMIN', true);
		}

		$this->cache = $this->createMock('\phpbb\cache\service');
		$this->config = new \phpbb\config\config($this->default_config());
		$this->db = $this->createMock('\phpbb\db\driver\driver_interface');
		$this->imagesize = $this->createMock('\FastImageSize\FastImageSize');
		$this->lang = $this->createMock('\phpbb\language\language');
		$this->log = $this->createMock('\phpbb\log\log_interface');
		$this->request = $this->createMock('\phpbb\request\request');
		$this->symfony_request = $this->createMock('\phpbb\symfony_request');
		$this->template = $this->createMock('\phpbb\template\template');
		$this->user = $this->createMock('\phpbb\user');
		$this->root_path = $phpbb_root_path;
		$this->style_updates = [];

		$this->user->data = ['user_id' => 42];
		$this->user->ip = '127.0.0.1';
		$this->user->lang = new \phpbb_mock_lang();

		$this->lang->method('lang')
			->willReturnCallback(function() {
				return implode(':', func_get_args());
			});

		$this->db->method('sql_query')
			->willReturn('result');
		$this->db->method('sql_fetchrowset')
			->with('result')
			->willReturn($this->style_rows());
		$this->db->method('sql_build_array')
			->willReturnCallback(function($query, $row) {
				$this->style_updates[] = $row;
				return implode(', ', array_map(static function($name, $value) {
					return $name . " = '" . $value . "'";
				}, array_keys($row), $row));
			});

		$template = $this->template;
		$user = $this->user;
		$language = $this->lang;
	}

	public function mode_display_data(): array
	{
		return [
			'webpush secure server' => [
				'webpush',
				'adm.php?i=test&mode=webpush',
				true,
				'example.com',
				$this->webpush_template_vars('adm.php?i=test&mode=webpush'),
				false,
				'',
			],
			'webpush localhost without https' => [
				'webpush',
				'adm.php?i=test&mode=webpush',
				false,
				'localhost',
				$this->webpush_template_vars('adm.php?i=test&mode=webpush'),
				false,
				'',
			],
			'webpush insecure remote server' => [
				'webpush',
				'adm.php?i=test&mode=webpush',
				false,
				'example.com',
				$this->webpush_template_vars('adm.php?i=test&mode=webpush'),
				true,
				'WEBPUSH_INSECURE_SERVER_ERROR',
			],
			'pwa settings' => [
				'pwa',
				'adm.php?i=test&mode=pwa',
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
	public function test_handle_displays_mode_settings($mode, $u_action, $is_secure, $server_name, array $settings_vars, $has_error, $error_msg): void
	{
		$this->lang->expects($this->once())
			->method('add_lang')
			->with('webpushnotifications_module_acp', 'phpbb/webpushnotifications');

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

		$controller = $this->create_controller($u_action);
		$controller->{'handle_' . $mode}();
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
	public function test_handle_rejects_invalid_form_key($mode): void
	{
		self::$valid_form = false;

		$this->request->method('is_set_post')
			->with('submit')
			->willReturn(true);

		$this->assertTriggerError(E_USER_WARNING, 'FORM_INVALID', function() use ($mode) {
			$this->create_controller('adm.php?i=test&mode=' . $mode)->{'handle_' . $mode}();
		});
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
			'blank existing private key is preserved' => [
				array_merge($valid, [
					'wpn_webpush_vapid_private' => '',
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
					'wpn_webpush_vapid_private' => 'existing-private-key',
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
			$controller = $this->create_controller();
			$this->assertTriggerError(E_USER_NOTICE, 'CONFIG_UPDATED', function() use ($controller) {
				$this->invoke_method($controller, 'save_settings');
			});
		}
		else
		{
			$this->invoke_method($this->create_controller(), 'save_settings');
		}

		foreach ($expected_config as $name => $value)
		{
			self::assertSame($value, $this->config[$name]);
		}
	}

	public function test_save_settings_requires_private_key_when_none_is_stored(): void
	{
		$this->config['wpn_webpush_vapid_public'] = '';
		$this->config['wpn_webpush_vapid_private'] = '';

		$this->request->method('variable')
			->with('config', ['' => ''], true)
			->willReturn([
				'wpn_webpush_enable' => 1,
				'wpn_webpush_vapid_public' => str_repeat('a', 25),
				'wpn_webpush_vapid_private' => '',
				'wpn_webpush_dropdown_subscribe' => 1,
				'wpn_webpush_method_enabled' => 0,
				'wpn_webpush_popup_prompt' => 1,
			]);

		$this->template->expects($this->once())
			->method('assign_vars')
			->with([
				'S_ERROR' => true,
				'ERROR_MSG' => 'SETTING_TOO_SHORT',
			]);
		$this->log->expects($this->never())->method('add');

		$this->invoke_method($this->create_controller(), 'save_settings');

		self::assertSame('', $this->config['wpn_webpush_vapid_private']);
	}

	public function test_save_settings_ignores_unrelated_config(): void
	{
		$this->config['allow_privmsg'] = 1;
		$this->request->method('variable')
			->with('config', ['' => ''], true)
			->willReturn([
				'wpn_webpush_enable' => 0,
				'wpn_webpush_vapid_public' => 'existing-public-key',
				'wpn_webpush_vapid_private' => '',
				'wpn_webpush_dropdown_subscribe' => 1,
				'wpn_webpush_method_enabled' => 1,
				'wpn_webpush_popup_prompt' => 1,
				'allow_privmsg' => 0,
			]);

		$controller = $this->create_controller();
		$this->assertTriggerError(E_USER_NOTICE, 'CONFIG_UPDATED', function() use ($controller) {
			$this->invoke_method($controller, 'save_settings');
		});

		self::assertSame(1, $this->config['allow_privmsg']);
	}

	public function pwa_save_data(): array
	{
		$valid = [
			'pwa_short_name' => 'Forum',
			'pwa_icon_small' => 'icon192.png',
			'pwa_icon_large' => 'icon512.png',
			'pwa_show_install_banner' => 1,
		];

		$valid_style_input = [
			'pwa_bg_color_1' => '#abcdef',
			'pwa_theme_color_1' => '#ABCDEF',
			'pwa_bg_color_2' => '',
			'pwa_theme_color_2' => '#123',
		];

		$expected_style_updates = [
			[
				'pwa_bg_color' => '#abcdef',
				'pwa_theme_color' => '#ABCDEF',
			],
			[
				'pwa_bg_color' => '',
				'pwa_theme_color' => '#123',
			],
		];

		return [
			'valid icons and style colours' => [
				$valid,
				$valid_style_input,
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
					'pwa_show_install_banner' => 1,
				],
				$expected_style_updates,
				true,
			],
			'valid empty name and icons' => [
				[],
				[],
				[],
				[
					'S_ERROR' => false,
					'ERROR_MSG' => '',
				],
				[
					'pwa_short_name' => '',
					'pwa_icon_small' => '',
					'pwa_icon_large' => '',
					'pwa_show_install_banner' => 0,
				],
				[
					[
						'pwa_bg_color' => '',
						'pwa_theme_color' => '',
					],
				],
				true,
			],
			'unchanged style colours skipped' => [
				$valid,
				[
					'pwa_bg_color_1' => '#fff000',
					'pwa_theme_color_1' => '#000fff',
					'pwa_bg_color_2' => '',
					'pwa_theme_color_2' => '',
				],
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
					'pwa_show_install_banner' => 1,
				],
				[],
				true,
			],
			'too long short name rejected after entity decoding' => [
				array_merge($valid, [
					'pwa_short_name' => '123456789012&#x1F600;',
				]),
				$valid_style_input,
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
				[],
				false,
			],
			'missing small icon rejected' => [
				array_merge($valid, [
					'pwa_icon_small' => '',
				]),
				$valid_style_input,
				[],
				[
					'S_ERROR' => true,
					'ERROR_MSG' => 'PWA_ICON_NOT_PROVIDED:PWA_ICON_SMALL',
				],
				[
					'pwa_icon_small' => 'old-small.png',
				],
				[],
				false,
			],
			'missing large icon rejected' => [
				array_merge($valid, [
					'pwa_icon_large' => '',
				]),
				$valid_style_input,
				[],
				[
					'S_ERROR' => true,
					'ERROR_MSG' => 'PWA_ICON_NOT_PROVIDED:PWA_ICON_LARGE',
				],
				[
					'pwa_icon_large' => 'old-large.png',
				],
				[],
				false,
			],
			'path traversal icon rejected' => [
				array_merge($valid, [
					'pwa_icon_small' => '../icon192.png',
				]),
				$valid_style_input,
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
				[],
				false,
			],
			'unreadable icon rejected' => [
				$valid,
				$valid_style_input,
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
				[],
				false,
			],
			'wrong icon size and MIME rejected' => [
				$valid,
				$valid_style_input,
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
				[],
				false,
			],
			'invalid style colour rejected' => [
				$valid,
				array_merge($valid_style_input, [
					'pwa_theme_color_1' => 'not-hex',
				]),
				[
					'icon192.png' => ['width' => 192, 'height' => 192, 'type' => IMAGETYPE_PNG],
					'icon512.png' => ['width' => 512, 'height' => 512, 'type' => IMAGETYPE_PNG],
				],
				[
					'S_ERROR' => true,
					'ERROR_MSG' => 'PWA_INVALID_COLOUR:not-hex',
				],
				[
					'pwa_short_name' => 'Old',
				],
				[],
				false,
			],
		];
	}

	/**
	 * @dataProvider pwa_save_data
	 */
	public function test_save_pwa_settings_validates_and_persists_pwa_config(array $input, array $style_input, array $image_info, array $error_vars, array $expected_config, array $expected_style_updates, $expect_saved): void
	{
		$this->request->method('variable')
			->willReturnCallback(function($name, $default = null, $multibyte = false) use ($input, $style_input) {
				if ($name === 'config')
				{
					return $input;
				}

				return $style_input[$name] ?? $default;
			});

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
			$controller = $this->create_controller('adm.php?i=test&mode=pwa');
			$this->assertTriggerError(E_USER_NOTICE, 'CONFIG_UPDATED', function() use ($controller) {
				$this->invoke_method($controller, 'save_pwa_settings');
			});
		}
		else
		{
			$this->invoke_method($this->create_controller('adm.php?i=test&mode=pwa'), 'save_pwa_settings');
		}

		foreach ($expected_config as $name => $value)
		{
			self::assertSame($value, $this->config[$name]);
		}

		self::assertSame($expected_style_updates, $this->style_updates);
	}

	public function hex_colour_data(): array
	{
		return [
			'empty colour allowed' => ['', true, []],
			'valid 3 digit colour allowed' => [' #fff ', true, []],
			'valid 6 digit colour allowed' => ['#ABCDEF', true, []],
			'missing hash rejected' => ['123456', false, ['PWA_INVALID_COLOUR:123456']],
			'invalid character rejected' => ['#12345g', false, ['PWA_INVALID_COLOUR:#12345g']],
		];
	}

	/**
	 * @dataProvider hex_colour_data
	 */
	public function test_validate_hex_color($colour, $expected, array $expected_errors): void
	{
		$controller = $this->create_controller();

		self::assertSame($expected, $this->invoke_method($controller, 'validate_hex_color', [$colour]));
		self::assertSame($expected_errors, $this->get_protected_property($controller, 'errors'));
	}

	public function test_display_errors_returns_false_without_errors(): void
	{
		$this->template->expects($this->once())
			->method('assign_vars')
			->with([
				'S_ERROR' => false,
				'ERROR_MSG' => '',
			]);

		self::assertFalse($this->invoke_method($this->create_controller(), 'display_errors'));
	}

	public function test_display_errors_returns_true_with_joined_errors(): void
	{
		$controller = $this->create_controller();
		$this->set_protected_property($controller, 'errors', ['first', 'second']);

		$this->template->expects($this->once())
			->method('assign_vars')
			->with([
				'S_ERROR' => true,
				'ERROR_MSG' => 'first<br>second',
			]);

		self::assertTrue($this->invoke_method($controller, 'display_errors'));
	}

	protected function create_controller($u_action = 'adm.php?i=test&mode=webpush'): acp_controller
	{
		return (new acp_controller(
			$this->cache,
			$this->config,
			$this->db,
			$this->imagesize,
			$this->lang,
			$this->log,
			$this->request,
			$this->symfony_request,
			$this->template,
			$this->user,
			$this->root_path
		))->set_page_url($u_action);
	}

	protected function set_protected_property($object, $property, $value): void
	{
		$reflection = new \ReflectionClass($object);
		$property = $reflection->getProperty($property);
		$property->setAccessible(true);
		$property->setValue($object, $value);
	}

	protected function assertTriggerError($errno, $message, callable $callback): void
	{
		$caught = null;
		set_error_handler(static function($severity, $error_message, $file, $line) {
			throw new \ErrorException($error_message, 0, $severity, $file, $line);
		}, $errno);

		try
		{
			$callback();
		}
		catch (\ErrorException $exception)
		{
			$caught = $exception;
		}
		finally
		{
			restore_error_handler();
		}

		self::assertNotNull($caught, 'Expected trigger_error() was not raised.');
		self::assertSame($errno, $caught->getSeverity());
		self::assertStringContainsString($message, $caught->getMessage());
	}

	protected function get_protected_property($object, $property)
	{
		$reflection = new \ReflectionClass($object);
		$property = $reflection->getProperty($property);
		$property->setAccessible(true);

		return $property->getValue($object);
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
			'sitename' => 'Board &amp; &#x1F600; Forum',
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
		];
	}

	protected function style_rows(): array
	{
		return [
			[
				'style_id' => 1,
				'style_name' => 'prosilver',
				'pwa_bg_color' => '#fff000',
				'pwa_theme_color' => '#000fff',
			],
			[
				'style_id' => 2,
				'style_name' => 'silverfoo',
				'pwa_bg_color' => '',
				'pwa_theme_color' => '',
			],
		];
	}

	protected function webpush_template_vars($u_action): array
	{
		return [
			'S_WEBPUSH_ENABLE' => 1,
			'WEBPUSH_VAPID_PUBLIC' => 'existing-public-key',
			'S_VAPID_KEYS_CONFIGURED' => true,
			'S_VAPID_PRIVATE_CONFIGURED' => true,
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
			'PWA_SHORT_NAME_PLACEHOLDER' => 'Board & 😀 Fo',
			'PWA_ICON_SMALL' => 'old-small.png',
			'PWA_ICON_LARGE' => 'old-large.png',
			'STYLES' => $this->style_rows(),
			'U_ACTION' => $u_action,
		];
	}
}

namespace phpbb\webpushnotifications\controller;

function add_form_key()
{
}

function check_form_key()
{
	return \phpbb\webpushnotifications\tests\controller\controller_acp_test::$valid_form;
}
