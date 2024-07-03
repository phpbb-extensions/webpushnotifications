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
	'NOTIFICATION_METHOD_PHPBB_WPN_WEBPUSH'	=> 'Браузерные',
	'NOTIFY_WEBPUSH_NOTIFICATIONS'	=> 'Браузерные push—уведомления',
	'NOTIFY_WEBPUSH_DISABLE'		=> 'Отключить',
	'NOTIFY_WEBPUSH_ENABLE'			=> 'Включить',
	'NOTIFY_WEBPUSH_ENABLE_SLIDER'	=> 'Браузерные уведомления',
	'NOTIFY_WEBPUSH_ENABLE_EXPLAIN'	=> 'Включение браузерных push—уведомлений активирует их только на данном устройстве. Вы сможете отключить их в любое время через настройки браузера или с помощью кнопки «Отключить» выше, которая появится после включения браузерных push—уведомлений. Если не выбран ни один из типов уведомлений в категории «Браузерные» ниже, вы также не будете получать браузерные push—уведомления.',
	'NOTIFY_WEBPUSH_SUBSCRIBE'		=> 'Подписаться',
	'NOTIFY_WEBPUSH_UNSUBSCRIBE'	=> 'Отписаться',
	'NOTIFY_WEBPUSH_DROPDOWN_TITLE'	=> 'Посетите настройки уведомлений, чтобы установить предпочтительные типы браузерных уведомлений.',
	'NOTIFY_WEBPUSH_DENIED'			=> 'Вы запретили браузерные уведомления для даного сайта. Для того, чтобы подписаться, необходимо их разрешить в настройках браузера.',
	'NOTIFY_WEBPUSH_DISABLED'		=> 'Не поддерживается',
]);
