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
	'PHPBB_VERSION_MAX_ERROR'	=> 'This extension cannot be installed on this board. This is a phpBB 4 board, which already contains the features in this extension.',
	'PHPBB_VERSION_MIN_ERROR'	=> 'phpBB ' . \phpbb\webpushnotifications\ext::PHPBB_MIN_VERSION . ' or newer is required.',
	'PHP_VERSION_ERROR'			=> 'PHP ' . \phpbb\webpushnotifications\ext::PHP_MIN_VERSION . ' or newer is required.',
	'PHP_EXT_MISSING'			=> 'The “%s” extension for PHP must be installed on this server.',
]);
