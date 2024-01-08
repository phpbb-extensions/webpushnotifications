<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	'ACP_WEBPUSH_SETTINGS_EXPLAIN'	=> 'Here you can enable and control the use of Web Push for board notifications. Web Push is a simple protocol for the delivery of real-time events to user agents, more commonly known as push messages. It is supported by most modern browsers on desktop and mobile devices.',
	'WEBPUSH_ENABLE'				=> 'Enable Web Push',
	'WEBPUSH_ENABLE_EXPLAIN'		=> 'Allow receiving notifications via Web Push. It is required to enter or generate valid VAPID identification keys to be able to use Web Push.',
	'WEBPUSH_ENABLE_FOR_ALL_USERS'	=> 'Enable Web Push for all users',
	'WEBPUSH_ENABLE_FOR_ALL_USERS_EXPLAIN'	=> 'This switch allows receiving notifications via Web Push to be enabled for all users having board notifications enabled. If enabled, users will be subscribed to receive notifications via Web Push for the same notification types they have board notifications enabled.',
	'WEBPUSH_ENABLED_FOR_ALL_USERS'	=> 'Users were subscribed to receive notifications via Web Push successfully.',
	'WEBPUSH_GENERATE_VAPID_KEYS'	=> 'Generate Identification keys',
	'WEBPUSH_VAPID_PUBLIC'			=> 'Server identification public key',
	'WEBPUSH_VAPID_PUBLIC_EXPLAIN'	=> 'The Voluntary Application Server Identification (VAPID) public key will be shared to authenticate push messages sent by your site.<br><em><strong>Warning:</strong> Changing the VAPID public key will automatically invalidate all Webpush subscriptions.</em>',
	'WEBPUSH_VAPID_PRIVATE'			=> 'Server identification private key',
	'WEBPUSH_VAPID_PRIVATE_EXPLAIN'	=> 'The Voluntary Application Server Identification (VAPID) private key will be used to create authenticated push messages sent by your site. The VAPID private key <strong>must</strong> be a valid public-private key pair with the VAPID public key.<br><em><strong>Warning:</strong> Changing the VAPID private key will automatically invalidate all Webpush subscriptions.</em>',
]);
