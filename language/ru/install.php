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
	'PHPBB_VERSION_MAX_ERROR'	=> 'Данное расширение не может быть установлено на конференции. Вы используете версию phpBB 4, которая уже содержит соответствующие функции.',
	'PHPBB_VERSION_MIN_ERROR'	=> 'Необходима версия phpBB ' . \phpbb\webpushnotifications\ext::PHPBB_MIN_VERSION . ' или выше.',
	'PHP_VERSION_ERROR'			=> 'Необходима версия PHP ' . \phpbb\webpushnotifications\ext::PHP_MIN_VERSION . ' или выше.',
	'PHP_EXT_MISSING'			=> 'На этом сервере должно быть установлено расширение «%s» для PHP.',
]);
