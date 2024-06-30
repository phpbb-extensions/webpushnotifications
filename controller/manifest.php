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
use phpbb\exception\http_exception;
use phpbb\language\language;
use phpbb\path_helper;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class manifest
{
	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var path_helper */
	protected $path_helper;

	/** @var user */
	protected $user;

	/**
	 * Constructor for webpush controller
	 *
	 * @param config $config
	 * @param path_helper $path_helper
	 * @param language $language
	 * @param user $user
	 */
	public function __construct(config $config, language $language, path_helper $path_helper, user $user)
	{
		$this->config = $config;
		$this->path_helper = $path_helper;
		$this->language = $language;
		$this->user = $user;
	}

	/**
	 * Handle creation of a manifest json file for progressive web-app support
	 *
	 * @return JsonResponse
	 */
	public function handle(): JsonResponse
	{
		if ($this->user->data['is_bot'] || $this->user->data['user_type'] == USER_INACTIVE)
		{
			throw new http_exception(Response::HTTP_FORBIDDEN, 'Forbidden');
		}

		$root_path = $this->path_helper->get_web_root_path();

		$manifest = [
			'name'			=> $this->config['sitename'],
			'short_name'	=> substr($this->config['sitename'], 0, 12), // TODO need an ACP option for short name
			'display'		=> 'standalone',
			'orientation'	=> 'portrait',
			'dir'			=> $this->language->lang('DIRECTION'),
			'start_url'		=> $root_path,
			'scope'			=> $root_path
		];

		// TODO add support for icons
		if (isset($this->config['wpn_app_icon_small'], $this->config['wpn_app_icon_large']))
		{
			$manifest['icons'] = [
				[
					'src' => $root_path . 'images/icons/' . $this->config['wpn_app_icon_small'],
					'sizes' => '192x192',
					'type' => 'image/png'
				],
				[
					'src' => $root_path . 'images/icons/' . $this->config['wpn_app_icon_large'],
					'sizes' => '512x512',
					'type' => 'image/png'
				]
			];
		}

		return new JsonResponse($manifest);
	}
}
