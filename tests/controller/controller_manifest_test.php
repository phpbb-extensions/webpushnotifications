<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\tests\controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class controller_manifest_test extends \phpbb_test_case
{
	protected $config;
	protected $user;
	protected $manifest;

	protected function setUp(): void
	{
		global $config, $user, $phpbb_root_path, $phpEx;

		parent::setUp();

		$config = $this->config = new \phpbb\config\config([
			'force_server_vars'	=> false,
			'script_path'		=> '',
			'sitename'			=> '',
			'pwa_short_name'	=> '',
			'pwa_icon_small'	=> '',
			'pwa_icon_large'	=> '',
		]);

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$language = new \phpbb\language\language($lang_loader);
		$user = $this->user = new \phpbb\user($language, '\phpbb\datetime');

		$this->manifest = new \phpbb\webpushnotifications\controller\manifest($this->config, $this->user);
	}

	public function manifest_data()
	{
		return [
			'using board url root path' => [
				[
					'force_server_vars'	=> false,
					'script_path'		=> '',
					'sitename'			=> 'yourdomain.com',
					'pwa_short_name'	=> 'yourdomain',
				],
				[
					'name'		=> 'yourdomain.com',
					'short_name'	=> 'yourdomain',
					'display'		=> 'standalone',
					'orientation'	=> 'portrait',
					'start_url'		=> '/',
					'scope'			=> '/',
				],
			],
			'using script path' => [
				[
					'force_server_vars'	=> true,
					'script_path'		=> '/foo/',
					'sitename'			=> 'yourdomain.com',
					'pwa_short_name'	=> 'yourdomain',
				],
				[
					'name'		=> 'yourdomain.com',
					'short_name'	=> 'yourdomain',
					'display'		=> 'standalone',
					'orientation'	=> 'portrait',
					'start_url'		=> '/foo/',
					'scope'			=> '/foo/',
				],
			],
			'with shortname' => [
				[
					'sitename'			=> 'testdomain.com',
					'pwa_short_name'	=> 'testdomain',
				],
				[
					'name'		=> 'testdomain.com',
					'short_name'	=> 'testdomain',
					'display'		=> 'standalone',
					'orientation'	=> 'portrait',
					'start_url'		=> '/',
					'scope'			=> '/',
				],
			],
			'without shortname' => [
				[
					'sitename'			=> 'testdomain.com',
					'pwa_short_name'	=> '',
				],
				[
					'name'		=> 'testdomain.com',
					'short_name'	=> 'testdomain.c',
					'display'		=> 'standalone',
					'orientation'	=> 'portrait',
					'start_url'		=> '/',
					'scope'			=> '/',
				],
			],
			'with icons' => [
				[
					'sitename'			=> '',
					'pwa_short_name'	=> '',
					'pwa_icon_small'	=> 'foo_sm.png',
					'pwa_icon_large'	=> 'foo_lg.png',
				],
				[
					'name'		=> '',
					'short_name'	=> '',
					'display'		=> 'standalone',
					'orientation'	=> 'portrait',
					'start_url'		=> '/',
					'scope'			=> '/',
					'icons'			=> [
						[
							'src'	=> 'http://images/site_icons/foo_sm.png',
							'sizes'	=> '192x192',
							'type'	=> 'image/png',
						],
						[
							'src'	=> 'http://images/site_icons/foo_lg.png',
							'sizes'	=> '512x512',
							'type'	=> 'image/png',
						],
					],
				],
			],
		];
	}

	/**
	 * @dataProvider manifest_data
	 */
	public function test_manifest($configs, $expected)
	{
		foreach ($configs as $key => $value)
		{
			$this->config->set($key, $value);
		}

		$response = $this->manifest->handle();

		$this->assertInstanceOf(JsonResponse::class, $response);

		$this->assertEquals($expected, json_decode($response->getContent(), true));
	}

	public function manifest_with_bot_data()
	{
		return [
			'is a bot' => [true, 'yes'],
			'not a bot' => [false, null],
		];
	}

	/**
	 * @dataProvider manifest_with_bot_data
	 */
	public function test_manifest_with_bot($is_bot, $expected)
	{
		$this->user->data['is_bot'] = $is_bot;

		$response = $this->manifest->handle();

		$this->assertInstanceOf(JsonResponse::class, $response);

		$this->assertEquals($expected, $response->headers->get('X-PHPBB-IS-BOT'));
	}
}
