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
use phpbb\webpushnotifications\controller\acp_controller;

require_once __DIR__ . '/../../../../../includes/functions_module.php';

class acp_module_test extends \phpbb_test_case
{
	/** @var acp_controller|\PHPUnit\Framework\MockObject\MockObject */
	protected $controller;

	/** @var \phpbb_mock_extension_manager */
	protected $extension_manager;

	/** @var \phpbb\module\module_manager */
	protected $module_manager;

	protected function setUp(): void
	{
		global $phpbb_container, $phpbb_dispatcher, $phpbb_extension_manager, $phpbb_root_path, $phpEx;

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

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

		$this->controller = $this->createMock(acp_controller::class);

		$phpbb_container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$phpbb_container->expects(self::any())
			->method('get')
			->with('phpbb.wpn.acp.controller')
			->willReturn($this->controller);
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
						'cat' => ['ACP_CLIENT_COMMUNICATION'],
					],
					'pwa'	=> [
						'title' => 'ACP_WEBPUSH_PWA_SETTINGS',
						'auth' => 'ext_phpbb/webpushnotifications && acl_a_board',
						'cat' => ['ACP_CLIENT_COMMUNICATION'],
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

	public function mode_data(): array
	{
		return [
			'webpush' => ['webpush', 'wpn_acp_settings', 'ACP_WEBPUSH_EXT_SETTINGS', 'handle_webpush'],
			'pwa' => ['pwa', 'wpn_acp_pwa', 'ACP_WEBPUSH_PWA_SETTINGS', 'handle_pwa'],
		];
	}

	/**
	 * @dataProvider mode_data
	 */
	public function test_main_delegates_mode($mode, $tpl_name, $page_title, $handler): void
	{
		$u_action = 'adm.php?i=test&mode=' . $mode;

		$this->controller->expects(self::once())
			->method('set_page_url')
			->with($u_action)
			->willReturnSelf();
		$this->controller->expects(self::once())
			->method($handler);

		$module = new wpn_acp_module();
		$module->u_action = $u_action;
		$module->main('', $mode);

		self::assertSame($tpl_name, $module->tpl_name);
		self::assertSame($page_title, $module->page_title);
	}
}
