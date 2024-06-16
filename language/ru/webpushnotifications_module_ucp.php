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
	'NOTIFICATION_METHOD_PHPBB_WPN_WEBPUSH'	=> 'Браузерные push—уведомления',
	'NOTIFY_WEBPUSH_ENABLE_SHORT'	=> 'Браузерные push—уведомления',
	'NOTIFY_WEBPUSH_ENABLE'			=> 'Включить браузерные push—уведомления',
	'NOTIFY_WEBPUSH_ENABLE_EXPLAIN'	=> 'Включение возможности получения браузерных push—уведомлений.<br>Данная возможность может быть отключена в любое время в настройках браузера, с помощью отписки от браузерных push—уведомлений в личном разделе или путём отключения данной функции ниже.',
	'NOTIFY_WEBPUSH_SUBSCRIBE'		=> 'Подписаться',
	'NOTIFY_WEBPUSH_UNSUBSCRIBE'	=> 'Отписаться',
	'NOTIFY_WEBPUSH_SUBSCRIBED'		=> 'Включено',
	'NOTIFY_WEBPUSH_DROPDOWN_TITLE'	=> 'Посетите настройки уведомлений, чтобы установить предпочтительные push-уведомления.',
	'NOTIFY_WEBPUSH_DENIED'			=> 'Вы запретили браузерные уведомления для даного сайта. Для того, чтобы подписаться, необходимо их разрешить в настройках браузера.',

]);
