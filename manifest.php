<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

use Symfony\Component\HttpFoundation\JsonResponse;
use phpbb\webpushnotifications\ext;

/**
* @ignore
**/
const IN_PHPBB = true;
$phpbb_root_path = ((defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './') . '../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$board_url = generate_board_url();

// Emoji fixer-uppers
$sitename = ext::decode_entities($config['sitename'], ENT_QUOTES);
$pwa_short_name = ext::decode_entities($config['pwa_short_name'], ENT_QUOTES);

$manifest = [
	'name'			=> $sitename,
	'short_name'	=> $pwa_short_name ?: utf8_substr($sitename, 0, 12),
	'display'		=> 'standalone',
	'orientation'	=> 'portrait',
	'start_url'		=> $board_url,
	'scope'			=> $board_url . '/',
];

if (!empty($config['pwa_icon_small']) && !empty($config['pwa_icon_large']))
{
	$manifest['icons'] = [
		[
			'src' => $board_url . '/' . ext::PWA_ICON_DIR . '/' . $config['pwa_icon_small'],
			'sizes' => '192x192',
			'type' => 'image/png'
		],
		[
			'src' => $board_url . '/' . ext::PWA_ICON_DIR . '/' . $config['pwa_icon_large'],
			'sizes' => '512x512',
			'type' => 'image/png'
		]
	];
}

$response = new JsonResponse($manifest);
$response->send();
