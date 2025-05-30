<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\controller;

use phpbb\config\config;
use phpbb\path_helper;
use phpbb\user;
use phpbb\webpushnotifications\ext;
use Symfony\Component\HttpFoundation\JsonResponse;

class manifest
{
	/** @var config */
	protected $config;

	/** @var path_helper */
	protected $path_helper;

	/** @var user */
	protected $user;

	/**
	 * Constructor for webpush controller
	 *
	 * @param config $config
	 * @param path_helper $path_helper
	 * @param user $user
	 */
	public function __construct(config $config, path_helper $path_helper, user $user)
	{
		$this->config = $config;
		$this->path_helper = $path_helper;
		$this->user = $user;
	}

	/**
	 * Handle creation of a manifest json file for progressive web-app support
	 *
	 * @return JsonResponse
	 */
	public function handle(): JsonResponse
	{
		$board_path = $this->config['force_server_vars'] ? $this->config['script_path'] : $this->path_helper->get_web_root_path();
		$board_url = generate_board_url();

		// Emoji fixer-uppers
		$sitename = ext::decode_entities($this->config['sitename'], ENT_QUOTES);
		$pwa_short_name = ext::decode_entities($this->config['pwa_short_name'], ENT_QUOTES);

		$manifest = [
			'name'			=> $sitename,
			'short_name'	=> $pwa_short_name ?: utf8_substr($sitename, 0, 12),
			'display'		=> 'standalone',
			'orientation'	=> 'portrait',
			'start_url'		=> $board_path,
			'scope'			=> $board_path,
		];

		if (!empty($this->config['pwa_icon_small']) && !empty($this->config['pwa_icon_large']))
		{
			$manifest['icons'] = [
				[
					'src' => $board_url . '/' . ext::PWA_ICON_DIR . '/' . $this->config['pwa_icon_small'],
					'sizes' => '192x192',
					'type' => 'image/png'
				],
				[
					'src' => $board_url . '/' . ext::PWA_ICON_DIR . '/' . $this->config['pwa_icon_large'],
					'sizes' => '512x512',
					'type' => 'image/png'
				]
			];
		}

		$response = new JsonResponse($manifest);
		$response->setPublic();
		$response->setMaxAge(3600);
		$response->headers->addCacheControlDirective('must-revalidate', true);

		if (!empty($this->user->data['is_bot']))
		{
			// Let reverse proxies know we detected a bot.
			$response->headers->set('X-PHPBB-IS-BOT', 'yes');
		}

		return $response;
	}
}
