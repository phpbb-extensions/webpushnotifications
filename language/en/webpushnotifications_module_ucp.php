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
	'NOTIFY_WEBPUSH_ENABLE_SHORT'	=> 'Enable Web Push notifications',
	'NOTIFY_WEBPUSH_ENABLE'			=> 'Enable receiving Web Push notifications',
	'NOTIFY_WEBPUSH_ENABLE_EXPLAIN'	=> 'Enable receiving browser-based push notifications.<br>The notifications can be turned off at any time in your browser settings, by unsubscribing, or by disabling the push notifications below.',
	'NOTIFY_WEBPUSH_SUBSCRIBE'		=> 'Subscribe',
	'NOTIFY_WEBPUSH_UNSUBSCRIBE'	=> 'Unsubscribe',
	'NOTIFY_WEBPUSH_SUBSCRIBED'		=> 'Subscribed',
]);
