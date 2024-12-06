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
	'ACP_WEBPUSH_SETTINGS_EXPLAIN'	=> 'Here you can enable Web Push for board notifications. Web Push is a protocol for the real-time delivery of events to user agents, commonly referred to as push messages. It is compatible with the majority of modern browsers on both desktop and mobile devices. Users can opt to receive Web Push alerts in their browser by subscribing and enabling their preferred notifications in the UCP.<br><br>To enable push notifications on Apple mobile devices, your site needs to function as a Progressive Web Application (PWA). This requires users to add your site to their device’s home screen. For an improved user experience, you can find additional PWA settings under <strong>Board settings</strong>, where you can configure an optional app name and app icons for how your site will appear on users’ home screens.',
	'WEBPUSH_ENABLE'				=> 'Enable Web Push',
	'WEBPUSH_ENABLE_EXPLAIN'		=> 'Allow users to receive notifications in their browser or device via Web Push. To utilise Web Push, you must input or generate valid VAPID identification keys.',
	'WEBPUSH_GENERATE_VAPID_KEYS'	=> 'Generate Identification keys',
	'WEBPUSH_VAPID_PUBLIC'			=> 'Server identification public key',
	'WEBPUSH_VAPID_PUBLIC_EXPLAIN'	=> 'The Voluntary Application Server Identification (VAPID) public key is shared to authenticate push messages from your site.<br><em><strong>Caution:</strong> Modifying the VAPID public key will automatically render all Web Push subscriptions invalid.</em>',
	'WEBPUSH_VAPID_PRIVATE'			=> 'Server identification private key',
	'WEBPUSH_VAPID_PRIVATE_EXPLAIN'	=> 'The Voluntary Application Server Identification (VAPID) private key is used to generate authenticated push messages dispatched from your site. The VAPID private key <strong>must</strong> form a valid public-private key pair alongside the VAPID public key.<br><em><strong>Caution:</strong> Modifying the VAPID private key will automatically render all Web Push subscriptions invalid.</em>',
	'WEBPUSH_METHOD_ENABLED'		=> 'Enable user-based web push notifications by default',
	'WEBPUSH_METHOD_ENABLED_EXPLAIN'=> 'When this setting is enabled, users who have also enabled and allowed browser notifications will start receiving them automatically. They can visit the UCP Notification settings to disable any unwanted notifications.<br><br>If this setting is disabled, users will not receive any notifications, even if they have enabled push notifications, until they visit the UCP Notification settings to allow the specific notifications they wish to receive.',
	'WEBPUSH_DROPDOWN_SUBSCRIBE'	=> 'Show web push settings in the notification dropdown',
	'WEBPUSH_DROPDOWN_SUBSCRIBE_EXPLAIN'=> 'Show or hide the “Enable Web Push” toggle switch in the notification dropdown. This allows users to easily enable or disable push notifications from any page of the forum.',
	'WEBPUSH_INSECURE_SERVER_ERROR' => 'This board is not using a secure SSL/HTTPS protocol, which is required for enabling web push notifications. Alternatively, the server environment might be misconfigured. Ensure that the <em>HTTPS</em> and <em>HEADER_CLIENT_PROTO</em> server environment variables are correctly configured.',
]);
