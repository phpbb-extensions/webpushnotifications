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
	'ACP_WEBPUSH_EXT_SETTINGS'			=> 'Браузерные уведомления',
	'ACP_WEBPUSH_REMOVE_WARNING'		=> 'Браузерные push—уведомления теперь встроены в phpBB',
	'ACP_WEBPUSH_REMOVE_NOTICE'			=> 'Расширение «phpBB Browser Push Notifications» больше не требуется и должно быть удалено.<br>Все системные и пользовательские настройки, связанные с данным расширением, будут перенесены в соответствующие настройки браузерных push—уведомлений конференции автоматически при удалении данного расширения.',
	'LOG_CONFIG_WEBPUSH'				=> '<strong>Изменены настройки браузерных push—уведомлений</strong>',
	'LOG_WEBPUSH_MESSAGE_FAIL'			=> '<strong>Не удалось отправить браузерное push—уведомление:</strong><br>» %s',
	'LOG_WEBPUSH_SUBSCRIPTION_REMOVED'	=> '<strong>Удалена подписка на браузерные push—уведомления:</strong><br>» %s',
	'LOG_WEBPUSH_ICON_DIR_FAIL'			=> '<strong>Не удалось перенести в папку изображений файл значка прогрессивного веб—приложения (PWA):</strong><br>» %1$s » %2$s',
	'LOG_WEBPUSH_ICON_DIR_SUCCESS'		=> '<strong>Папка изображений прогрессивного веб—приложения (PWA) успешно создана:</strong><br>» <samp>%s</samp>',
	'LOG_WEBPUSH_ICON_COPY_SUCCESS'		=> '<strong>Файлы значков прогрессивного веб—приложения (PWA) успешно скопированы в папку:</strong><br>» <samp>%s</samp>',
]);
