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
	'NOTIFICATION_METHOD_PHPBB_WPN_WEBPUSH'	=> 'Web Push',
	'NOTIFY_WEBPUSH_NOTIFICATIONS'	=> 'Web push notifications',
	'NOTIFY_WEBPUSH_DISABLE'		=> 'Disable push notifications',
	'NOTIFY_WEBPUSH_ENABLE'			=> 'Enable push notifications',
	'NOTIFY_WEBPUSH_ENABLE_SLIDER'	=> 'Enable push notifications',
	'NOTIFY_WEBPUSH_ENABLE_EXPLAIN'	=> 'Enabling push notifications will activate them on this device only. You can turn off notifications at any time through your browser settings or by clicking the button above. Additionally, if no web push notification types are selected below, you will not receive any web push notifications.',
	'NOTIFY_WEBPUSH_SUBSCRIBE'		=> 'Enable to subscribe',
	'NOTIFY_WEBPUSH_UNSUBSCRIBE'	=> 'Disable to unsubscribe',
	'NOTIFY_WEBPUSH_DROPDOWN_TITLE'	=> 'Visit notifications settings to set your preferred push notifications.',
	'NOTIFY_WEBPUSH_DENIED'			=> 'You have denied notifications from this site. To enable push notifications, allow notifications from this site in your browser settings.',
	'NOTIFY_WEBPUSH_DISABLED'		=> 'Push notifications not supported',
]);
